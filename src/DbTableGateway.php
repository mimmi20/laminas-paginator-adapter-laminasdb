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

use Closure;
use Laminas\Db\Sql\Exception\InvalidArgumentException;
use Laminas\Db\Sql\Having;
use Laminas\Db\Sql\Predicate\PredicateInterface;
use Laminas\Db\Sql\Predicate\PredicateSet;
use Laminas\Db\Sql\Where;
use Laminas\Db\TableGateway\AbstractTableGateway;

/**
 * @template-covariant TKey of int
 * @template-covariant TValue
 * @extends DbSelect<TKey, TValue>
 */
final class DbTableGateway extends DbSelect
{
    /**
     * @param array<int|string, array<scalar>|PredicateInterface|string|null>|Closure(PredicateSet): void|PredicateInterface|string|Where|null  $where
     * @param array<int|string, string>|string|null                                                                                             $order
     * @param array<int|string, string>|string|null                                                                                             $group
     * @param array<int|string, array<scalar>|PredicateInterface|string|null>|Closure(PredicateSet): void|Having|PredicateInterface|string|null $having
     *
     * @throws InvalidArgumentException
     */
    public function __construct(
        AbstractTableGateway $tableGateway,
        Where | Closure | PredicateInterface | string | array | null $where = null,
        string | array | null $order = null,
        string | array | null $group = null,
        Having | Closure | PredicateInterface | string | array | null $having = null,
    ) {
        $sql    = $tableGateway->getSql();
        $select = $sql->select();

        if ($where !== null) {
            $select->where($where);
        }

        if ($order !== null) {
            $select->order($order);
        }

        if ($group !== null) {
            $select->group($group);
        }

        if ($having !== null) {
            $select->having($having);
        }

        $resultSetPrototype = $tableGateway->getResultSetPrototype();

        parent::__construct($select, $sql, $resultSetPrototype);
    }
}
