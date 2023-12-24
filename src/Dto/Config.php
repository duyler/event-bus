<?php

declare(strict_types=1);

namespace Duyler\EventBus\Dto;

use Duyler\DependencyInjection\Definition;

readonly class Config
{
    public function __construct(
        /** @var array<string, string> */
        public array $bind = [],
        /** @var array<string, string> */
        public array $providers = [],
        /** @var Definition[] */
        public array $definitions = [],
    ) {}
}
