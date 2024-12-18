<?php

declare(strict_types=1);

namespace Duyler\EventBus\Bus;

use Duyler\EventBus\Build\Action;

final class ActionRequiredMap
{
    /** @var array<string, Action[]> */
    private array $map = [];

    public function create(Action $action): void
    {
        foreach ($action->required as $required) {
            $this->map[$required][] = $action;
        }
    }

    /** @return Action[] */
    public function get(string $actionId): array
    {
        return $this->map[$actionId] ?? [];
    }

    public function remove(string $actionId): void
    {
        unset($this->map[$actionId]);
    }
}
