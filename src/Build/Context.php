<?php

declare(strict_types=1);

namespace Duyler\EventBus\Build;

final readonly class Context
{
    public function __construct(
        /** @var array<array-key, string> */
        public array $scope,
    ) {}
}
