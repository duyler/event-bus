<?php

declare(strict_types=1);

namespace Jine\EventBus;

use Jine\EventBus\Dto\Action;
use RuntimeException;

use function array_key_exists;

class ActionStorage
{
    private array $actions = [];

    public function save(Action $action): void
    {
        if (array_key_exists($action->serviceId . '.' . $action->name, $this->actions)) {
            throw new RuntimeException('Service ' . $action->serviceId . ' already registered');
        }
        $this->actions[$action->serviceId . '.' . $action->name] = $action;
    }

    public function get(string $actionFullName): Action
    {
        return $this->actions[$actionFullName];
    }

    public function getAll(): array
    {
        return $this->actions;
    }

    public function isExists(string $actionFullName): bool
    {
        return array_key_exists($actionFullName, $this->actions);
    }
}
