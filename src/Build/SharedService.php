<?php

declare(strict_types=1);

namespace Duyler\EventBus\Build;

final readonly class SharedService
{
    public function __construct(
        public string $class,
        public ?object $service = null,

        /** @var array<string, string> */
        public array $bind = [],

        /** @var array<string, string> */
        public array $providers = [],
    ) {}
}
