<?php

declare(strict_types=1);

namespace Duyler\EventBus\Dto;

readonly class Context
{
    public function __construct(public array $scope) {}
}
