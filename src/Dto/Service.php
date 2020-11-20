<?php 

declare(strict_types=1);

namespace Jine\EventBus\Dto;

use Jine\EventBus\ActionStorage;

class Service
{
    private ActionStorage $actionStorage;

    public string $id;

    public string $handler = '';

    public function __construct(ActionStorage $actionStorage)
    {
        $this->actionStorage = $actionStorage;
    }

    public function action($actionName): Action
    {
        $action = new Action($actionName, $this->id);
        $action->handler ??= $this->handler;
        $this->actionStorage->save($action);
        return $action;
    }

    public function handler(string $handler): self
    {
        $this->handler = $handler;
        return $this;
    }
}
