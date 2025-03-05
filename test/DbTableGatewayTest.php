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
use Laminas\Db\Adapter\Platform\Sql92;
use Laminas\Db\Sql\Exception\InvalidArgumentException;
use Laminas\Db\TableGateway\TableGateway;
use Laminas\Paginator\Adapter\Exception\MissingRowCountColumnException;
use Laminas\Paginator\Adapter\LaminasDb\DbSelect;
use Laminas\Paginator\Adapter\LaminasDb\DbTableGateway;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(DbTableGateway::class)]
final class DbTableGatewayTest extends TestCase
{
    protected MockObject & StatementInterface $mockStatement;

    /** @var DbTableGateway<int, mixed> */
    protected DbTableGateway $dbTableGateway;
    protected MockObject & TableGateway $mockTableGateway;

    /** @throws Exception */
    protected function setup(): void
    {
        $mockStatement = $this->createMock(StatementInterface::class);

        $mockDriver = $this->createMock(DriverInterface::class);
        $mockDriver
            ->expects(self::any())
            ->method('createStatement')
            ->willReturn($mockStatement);
        $mockDriver
            ->expects(self::any())
            ->method('formatParameterName')
            ->willReturnArgument(0);

        $mockAdapter = $this->getMockBuilder(Adapter::class)
            ->setConstructorArgs([$mockDriver, new Sql92()])
            ->onlyMethods([])
            ->getMock();

        $tableName = 'foobar';

        $mockTableGateway = $this->getMockBuilder(TableGateway::class)
            ->setConstructorArgs([$tableName, $mockAdapter])
            ->onlyMethods([])
            ->getMock();

        $this->mockStatement = $mockStatement;

        $this->mockTableGateway = $mockTableGateway;
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testGetItems(): void
    {
        $this->dbTableGateway = new DbTableGateway($this->mockTableGateway);

        $mockResult = $this->createMock(ResultInterface::class);
        $this->mockStatement
            ->expects(self::any())
            ->method('execute')
            ->willReturn($mockResult);

        $items = $this->dbTableGateway->getItems(2, 10);
        self::assertSame([], $items);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws MissingRowCountColumnException
     */
    public function testCount(): void
    {
        $this->dbTableGateway = new DbTableGateway($this->mockTableGateway);

        $mockResult = $this->createMock(ResultInterface::class);
        $mockResult
            ->expects(self::any())
            ->method('current')
            ->willReturn([DbSelect::ROW_COUNT_COLUMN_NAME => 10]);

        $this->mockStatement
            ->expects(self::any())
            ->method('execute')
            ->willReturn($mockResult);

        $count = $this->dbTableGateway->count();
        self::assertSame(10, $count);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testGetItemsWithWhereAndOrder(): void
    {
        $where = 'foo = bar';
        $order = 'foo';

        $this->dbTableGateway = new DbTableGateway($this->mockTableGateway, $where, $order);

        $mockResult = $this->createMock(ResultInterface::class);

        $this->mockStatement
            ->expects(self::any())
            ->method('execute')
            ->willReturn($mockResult);

        $items = $this->dbTableGateway->getItems(2, 10);
        self::assertSame([], $items);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testGetItemsWithWhereAndOrderAndGroup(): void
    {
        $where = 'foo = bar';
        $order = 'foo';
        $group = 'foo';

        $this->dbTableGateway = new DbTableGateway($this->mockTableGateway, $where, $order, $group);

        $mockResult = $this->createMock(ResultInterface::class);

        $this->mockStatement
            ->expects(self::once())
            ->method('setSql')
            ->with(
                'SELECT "foobar".* FROM "foobar" WHERE foo = bar GROUP BY "foo" ORDER BY "foo" ASC LIMIT limit OFFSET offset',
            );
        $this->mockStatement
            ->expects(self::any())
            ->method('execute')
            ->willReturn($mockResult);

        $items = $this->dbTableGateway->getItems(2, 10);
        self::assertSame([], $items);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testGetItemsWithWhereAndOrderAndGroupAndHaving(): void
    {
        $where  = 'foo = bar';
        $order  = 'foo';
        $group  = 'foo';
        $having = 'count(foo)>0';

        $this->dbTableGateway = new DbTableGateway(
            $this->mockTableGateway,
            $where,
            $order,
            $group,
            $having,
        );

        $mockResult = $this->createMock(ResultInterface::class);

        $this->mockStatement
            ->expects(self::once())
            ->method('setSql')
            ->with(
                'SELECT "foobar".* FROM "foobar" WHERE foo = bar GROUP BY "foo" HAVING count(foo)>0 ORDER BY "foo" ASC LIMIT limit OFFSET offset',
            );
        $this->mockStatement
            ->expects(self::any())
            ->method('execute')
            ->willReturn($mockResult);

        $items = $this->dbTableGateway->getItems(2, 10);
        self::assertSame([], $items);
    }
}
