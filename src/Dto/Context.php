<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Dto;

readonly class Context
{
    public function __construct(
        /** @var array<array-key, string> */
        public array $scope,
    ) {}
}
