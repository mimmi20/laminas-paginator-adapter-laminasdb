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

namespace Laminas\Paginator\Adapter\LaminasDb;

use Laminas\Db\Adapter\Adapter;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\ResultSet\ResultSetInterface;
use Laminas\Db\Sql\Exception\InvalidArgumentException;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Sql;
use Laminas\Paginator\Adapter\AdapterInterface;
use Laminas\Paginator\Adapter\Exception\MissingRowCountColumnException;

use function array_key_exists;
use function assert;
use function is_array;
use function iterator_to_array;
use function mb_strtolower;

/**
 * @phpcs:disable SlevomatCodingStandard.Classes.RequireAbstractOrFinal.ClassNeitherAbstractNorFinal
 * @template-covariant TKey of int
 * @template-covariant TValue
 * @implements AdapterInterface<TKey, TValue>
 */
class DbSelect implements AdapterInterface
{
    /** @api */
    public const ROW_COUNT_COLUMN_NAME = 'C';

    private readonly Sql $sql;

    /** @var ResultSetInterface<mixed> */
    private readonly ResultSetInterface $resultSetPrototype;

    /**
     * Total item count
     *
     * @var int<0, max>|null
     */
    private int | null $rowCount = null;

    /**
     * @param Select                         $select             The select query
     * @param Adapter|Sql                    $adapterOrSqlObject DB adapter or Sql object
     * @param ResultSetInterface<mixed>|null $resultSetPrototype
     *
     * @throws void
     */
    public function __construct(
        private readonly Select $select,
        Adapter | Sql $adapterOrSqlObject,
        ResultSetInterface | null $resultSetPrototype = null,
        /**
         * Database count query
         */
        private readonly Select | null $countSelect = null,
    ) {
        if ($adapterOrSqlObject instanceof Adapter) {
            $adapterOrSqlObject = new Sql($adapterOrSqlObject);
        }

        $this->sql                = $adapterOrSqlObject;
        $this->resultSetPrototype = $resultSetPrototype ?: new ResultSet();
    }

    /**
     * Returns an array of items for a page.
     *
     * @param int $offset           Page offset
     * @param int $itemCountPerPage Number of items per page
     *
     * @return array<int, mixed>
     *
     * @throws InvalidArgumentException
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function getItems($offset, $itemCountPerPage): array
    {
        $select = clone $this->select;
        $select->offset($offset);
        $select->limit($itemCountPerPage);

        $statement = $this->sql->prepareStatementForSqlObject($select);
        $result    = $statement->execute();

        $resultSet = clone $this->resultSetPrototype;
        $resultSet->initialize($result);

        return iterator_to_array($resultSet);
    }

    /**
     * Returns the total number of rows in the result set.
     *
     * @return int<0, max>
     *
     * @throws MissingRowCountColumnException
     * @throws InvalidArgumentException
     */
    public function count(): int
    {
        if ($this->rowCount !== null) {
            return $this->rowCount;
        }

        $select    = $this->getSelectCount();
        $statement = $this->sql->prepareStatementForSqlObject($select);
        $result    = $statement->execute();
        $row       = $result->current();
        assert(is_array($row));

        $this->rowCount = $this->locateRowCount($row);

        return $this->rowCount;
    }

    /**
     * @internal
     *
     * @see https://github.com/laminas/laminas-paginator/issues/3 Reference for creating an internal cache ID
     *
     * @return array{select: string, count_select: string}
     *
     * @throws InvalidArgumentException
     *
     * @api
     * @todo The next major version should rework the entire caching of a paginator.
     */
    public function getArrayCopy(): array
    {
        return [
            'select' => $this->sql->buildSqlString($this->select),
            'count_select' => $this->sql->buildSqlString(
                $this->getSelectCount(),
            ),
        ];
    }

    /**
     * Returns select query for count
     *
     * @throws InvalidArgumentException
     */
    private function getSelectCount(): Select
    {
        if ($this->countSelect) {
            return $this->countSelect;
        }

        $select = clone $this->select;
        $select->reset(Select::LIMIT);
        $select->reset(Select::OFFSET);
        $select->reset(Select::ORDER);

        $countSelect = new Select();

        $countSelect->columns([self::ROW_COUNT_COLUMN_NAME => new Expression('COUNT(1)')]);
        $countSelect->from(['original_select' => $select]);

        return $countSelect;
    }

    /**
     * @param array<string, int> $row
     *
     * @return int<0, max>
     *
     * @throws MissingRowCountColumnException
     */
    private function locateRowCount(array $row): int
    {
        if (array_key_exists(self::ROW_COUNT_COLUMN_NAME, $row)) {
            return (int) $row[self::ROW_COUNT_COLUMN_NAME];
        }

        $lowerCaseColumnName = mb_strtolower(self::ROW_COUNT_COLUMN_NAME);

        if (array_key_exists($lowerCaseColumnName, $row)) {
            return (int) $row[$lowerCaseColumnName];
        }

        throw MissingRowCountColumnException::forColumn(self::ROW_COUNT_COLUMN_NAME);
    }
}
