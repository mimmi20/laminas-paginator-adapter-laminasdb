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

final class Module
{
    /**
     * Retrieve configuration for laminas-paginator adapter plugin manager for laminas-mvc context.
     *
     * @return array{paginators: array{aliases: array<string, class-string>, factories: array<class-string, class-string>}}
     *
     * @throws void
     *
     * @api
     */
    public function getConfig(): array
    {
        return (new ConfigProvider())();
    }
}
