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

namespace Laminas\Paginator\Adapter\LaminasDb\Exception;

use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\Sql\Select;
use Laminas\Db\TableGateway\AbstractTableGateway;
use Laminas\Paginator\Adapter\LaminasDb\DbSelect;
use Laminas\Paginator\Adapter\LaminasDb\DbTableGateway;
use RuntimeException;

use function sprintf;

final class ServiceNotCreatedException extends RuntimeException implements ExceptionInterface
{
    /** @throws void */
    public static function forMissingDbSelectDependencies(): self
    {
        return new self(sprintf(
            '%s requires at least two options in the following order: a %s instance and a %s instance',
            DbSelect::class,
            Select::class,
            AdapterInterface::class,
        ));
    }

    /** @throws void */
    public static function forMissingDbTableGatewayDependencies(): self
    {
        return new self(sprintf(
            '%s requires at least one option, a %s instance',
            DbTableGateway::class,
            AbstractTableGateway::class,
        ));
    }
}
