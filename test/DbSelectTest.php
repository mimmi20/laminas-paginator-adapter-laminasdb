<?php

/**
 * This file is part of the mimmi20/laminas-paginator-adapter-laminasdb package.
 *
 * Copyright (c) 2020-2025 Laminas Project a Series of LF Projects, LLC. (https://getlaminas.org/)
 * Copyright (c) 2025, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace LaminasTest\Paginator\Adapter\LaminasDb;

use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Adapter\Driver\DriverInterface;
use Laminas\Db\Adapter\Driver\ResultInterface;
use Laminas\Db\Adapter\Driver\StatementInterface;
use Laminas\Db\Adapter\Platform\PlatformInterface;
use Laminas\Db\Sql\Exception\InvalidArgumentException;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Sql;
use Laminas\Paginator\Adapter\Exception\MissingRowCountColumnException;
use Laminas\Paginator\Adapter\LaminasDb\DbSelect;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

use function array_keys;
use function mb_strtolower;

#[CoversClass(DbSelect::class)]
final class DbSelectTest extends TestCase
{
    protected MockObject & Select $mockSelect;
    protected MockObject & Select $mockSelectCount;
    protected MockObject & StatementInterface $mockStatement;
    protected MockObject & ResultInterface $mockResult;
    protected MockObject & Sql $mockSql;

    /** @var DbSelect<int, mixed> */
    protected DbSelect $dbSelect;

    /** @throws Exception */
    protected function setUp(): void
    {
        $this->mockResult = $this->createMock(ResultInterface::class);

        $this->mockStatement = $this->createMock(StatementInterface::class);
        $this->mockStatement
            ->expects(self::any())
            ->method('execute')
            ->willReturn($this->mockResult);

        $mockDriver = $this->createMock(DriverInterface::class);
        $mockDriver
            ->expects(self::any())
            ->method('createStatement')
            ->willReturn($this->mockStatement);

        $mockPlatform = $this->createMock(PlatformInterface::class);
        $mockPlatform
            ->expects(self::any())
            ->method('getName')
            ->willReturn('platform');

        $mockAdapter = $this->getMockBuilder(Adapter::class)
            ->setConstructorArgs([$mockDriver, $mockPlatform])
            ->onlyMethods([])
            ->getMock();

        $this->mockSql = $this->getMockBuilder(Sql::class)
            ->onlyMethods(['prepareStatementForSqlObject'])
            ->setConstructorArgs([$mockAdapter])
            ->getMock();
        $this->mockSql
            ->expects(self::any())
            ->method('prepareStatementForSqlObject')
            ->with(self::isInstanceOf(Select::class))
            ->willReturn($this->mockStatement);

        $this->mockSelect      = $this->createMock(Select::class);
        $this->mockSelectCount = $this->createMock(Select::class);

        $this->dbSelect = new DbSelect($this->mockSelect, $this->mockSql);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testGetItems(): void
    {
        $this->mockSelect
            ->expects(self::once())
            ->method('limit')
            ->with(10);

        $this->mockSelect
            ->expects(self::once())
            ->method('offset')
            ->with(2);

        $items = $this->dbSelect->getItems(2, 10);
        self::assertSame([], $items);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws MissingRowCountColumnException
     */
    public function testCount(): void
    {
        $this->mockResult
            ->expects(self::once())
            ->method('current')
            ->willReturn([DbSelect::ROW_COUNT_COLUMN_NAME => 5]);

        $this->mockSelect
            ->expects(self::exactly(3))
            // called for columns, limit, offset, order
            ->method('reset');

        $count = $this->dbSelect->count();
        self::assertSame(5, $count);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws MissingRowCountColumnException
     */
    public function testCountQueryWithLowerColumnNameShouldReturnValidResult(): void
    {
        $this->dbSelect = new DbSelect($this->mockSelect, $this->mockSql);
        $this->mockResult
            ->expects(self::once())
            ->method('current')
            ->willReturn([mb_strtolower(DbSelect::ROW_COUNT_COLUMN_NAME) => 7]);

        $count = $this->dbSelect->count();
        self::assertSame(7, $count);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws MissingRowCountColumnException
     */
    public function testCountQueryWithMissingColumnNameShouldRaiseException(): void
    {
        $this->dbSelect = new DbSelect($this->mockSelect, $this->mockSql);
        $this->mockResult
            ->expects(self::once())
            ->method('current')
            ->willReturn([]);

        $this->expectException(MissingRowCountColumnException::class);
        $this->dbSelect->count();
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws MissingRowCountColumnException
     */
    public function testCustomCount(): void
    {
        $this->dbSelect = new DbSelect($this->mockSelect, $this->mockSql, null, $this->mockSelectCount);
        $this->mockResult
            ->expects(self::once())
            ->method('current')
            ->willReturn([DbSelect::ROW_COUNT_COLUMN_NAME => 7]);

        $count = $this->dbSelect->count();
        self::assertSame(7, $count);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    #[Group('6812')]
    #[Group('6817')]
    public function testReturnValueIsArray(): void
    {
        self::assertIsArray($this->dbSelect->getItems(0, 10));
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testGetArrayCopyShouldContainSelectItems(): void
    {
        $this->dbSelect = new DbSelect($this->mockSelect, $this->mockSql, null, $this->mockSelectCount);
        self::assertSame(
            [
                'select',
                'count_select',
            ],
            array_keys($this->dbSelect->getArrayCopy()),
        );
    }
}
