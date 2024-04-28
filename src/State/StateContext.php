<?php

declare(strict_types=1);

namespace Duyler\ActionBus\State;

class StateContext
{
    /** @var array<string, mixed> */
    private array $data = [];

    public function write(string $key, mixed $value): void
    {
        $this->data[$key] = $value;
    }

    public function read(string $key): mixed
    {
        return $this->data[$key] ?? null;
    }
}
