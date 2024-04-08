<?php

declare(strict_types=1);

namespace Duyler\EventBus\Dto;

readonly class ActionResultSubstitution
{
    public function __construct(
        public string $actionId,
        public string $requiredContract,
        public object $substitution,
    ) {}
}
