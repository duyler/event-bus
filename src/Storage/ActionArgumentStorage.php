<?php

declare(strict_types=1);

namespace Duyler\EventBus\Storage;

use Duyler\DI\Attribute\Finalize;

#[Finalize(method: 'reset')]
class ActionArgumentStorage
{
    /** @var array<string, object> */
    private array $data = [];

    public function set(string $actionId, object $argument): void
    {
        $this->data[$actionId] = $argument;
    }

    public function get(string $actionId): object
    {
        return $this->data[$actionId];
    }

    public function isExists(string $actionId): bool
    {
        return isset($this->data[$actionId]);
    }

    public function reset(): void
    {
        $this->data = [];
    }
}
