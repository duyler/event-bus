<?php

declare(strict_types=1);

namespace Duyler\EventBus\State;

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

    public function remove(string $key): void
    {
        if (array_key_exists($key, $this->data)) {
            unset($key, $this->data);
        }
    }
}
