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

final class ConfigProvider
{
    /**
     * Retrieve default laminas-paginator configuration.
     *
     * @return array{paginators: array{aliases: array<string, class-string>, factories: array<class-string, class-string>}}
     *
     * @throws void
     *
     * @api
     */
    public function __invoke(): array
    {
        return [
            'paginators' => $this->getPaginatorConfig(),
        ];
    }

    /**
     * Retrieve configuration for laminas-paginator adapter plugin manager.
     *
     * @return array{aliases: array<string, class-string>, factories: array<class-string, class-string>}
     *
     * @throws void
     *
     * @api
     */
    public function getPaginatorConfig(): array
    {
        return [
            'aliases' => [
                'dbselect' => DbSelect::class,
                'dbSelect' => DbSelect::class,
                'DbSelect' => DbSelect::class,
                'dbtablegateway' => DbTableGateway::class,
                'dbTableGateway' => DbTableGateway::class,
                'DbTableGateway' => DbTableGateway::class,
            ],
            'factories' => [
                DbSelect::class => DbSelectFactory::class,
                DbTableGateway::class => DbTableGatewayFactory::class,
            ],
        ];
    }
}
