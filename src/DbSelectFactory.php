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
use Laminas\Db\ResultSet\ResultSetInterface;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Sql;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

use function assert;
use function count;
use function is_a;

final class DbSelectFactory implements FactoryInterface
{
    /**
     * @param string                                                                              $requestedName
     * @param array{0: Select, 1: Adapter|Sql, 2?:ResultSetInterface<mixed>, 3?:Select|null}|null $options
     *
     * @return DbSelect<int, mixed>
     *
     * @throws Exception\ServiceNotCreatedException
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function __invoke(ContainerInterface $container, $requestedName, array | null $options = null): DbSelect
    {
        if ($options === null || count($options) < 2) {
            throw Exception\ServiceNotCreatedException::forMissingDbSelectDependencies();
        }

        assert(is_a($requestedName, DbSelect::class, true));

        return new $requestedName(
            $options[0],
            $options[1],
            $options[2] ?? null,
            $options[3] ?? null,
        );
    }
}
