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
use Psr\Container\ContainerInterface;

use function assert;
use function count;
use function is_a;

final class DbTableGatewayFactory
{
    /**
     * @param string                                                                                                                                                                                                                                                                                                                                                                                                 $requestedName
     * @param array{0: AbstractTableGateway, 1?: array<int|string, array<scalar>|PredicateInterface|string|null>|Closure(PredicateSet): void|PredicateInterface|string|Where|null, 2?: array<int|string, string>|string|null, 3?: array<int|string, string>|string|null, 4?: array<int|string, array<scalar>|PredicateInterface|string|null>|Closure(PredicateSet): void|Having|PredicateInterface|string|null}|null $options
     *
     * @return DbTableGateway<int, mixed>
     *
     * @throws Exception\ServiceNotCreatedException
     * @throws InvalidArgumentException
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        array | null $options = null,
    ): DbTableGateway {
        if ($options === null || count($options) < 1) {
            throw Exception\ServiceNotCreatedException::forMissingDbTableGatewayDependencies();
        }

        assert(is_a($requestedName, DbTableGateway::class, true));

        return new $requestedName(
            $options[0],
            $options[1] ?? null,
            $options[2] ?? null,
            $options[3] ?? null,
            $options[4] ?? null,
        );
    }
}
