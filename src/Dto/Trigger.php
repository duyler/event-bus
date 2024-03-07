<?php

declare(strict_types=1);

namespace Duyler\EventBus\Dto;

readonly class Trigger
{
    public function __construct(
        public string $id,
        public object|null $data = null,
        public string|null $contract = null,
    ) {}
}
