<?php

declare(strict_types=1);

namespace Konveyer\EventBus\Storage;

use RuntimeException;
use Konveyer\EventBus\Action;

use function array_key_exists;

class ActionStorage
{
    private array $actions = [];

    public function save(Action $action): void
    {
        if (array_key_exists($action->service . '.' . $action->name, $this->actions)) {
            throw new RuntimeException('Service ' . $action->service . ' already registered');
        }
        $this->actions[$action->service . '.' . $action->name] = $action;
    }

    public function get(string $actionFullName): Action
    {
        if ($this->isExists($actionFullName) === false) {
            throw new RuntimeException('Service ' . $actionFullName . 'not found in the storage');
        }
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
