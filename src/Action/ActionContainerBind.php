<?php

declare(strict_types=1);

namespace Duyler\EventBus\Action;

use Duyler\EventBus\Dto\Action;
use Duyler\EventBus\Dto\Result;

class ActionContainerBind
{
    private array $bind = [];

    public function add(Action $action, Result $result): void
    {
        if ($result->data !== null) {
            $this->bind[$action->id] = [
                $action->contract => $result->data::class,
            ];
        }
    }

    public function get(string $actionId): array
    {
        return $this->bind[$actionId] ?? [];
    }
}
