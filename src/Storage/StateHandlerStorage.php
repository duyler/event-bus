<?php

declare(strict_types=1);

namespace Duyler\EventBus\Storage;

use Duyler\EventBus\Contract\State\StateAfterHandlerInterface;
use Duyler\EventBus\Contract\State\StateBeforeHandlerInterface;
use Duyler\EventBus\Contract\State\StateFinalHandlerInterface;

class StateHandlerStorage extends AbstractStorage
{
    public function save(StateAfterHandlerInterface|StateBeforeHandlerInterface|StateFinalHandlerInterface $stateHandler)
    {
        $this->data[$stateHandler::TYPE_KEY][] = $stateHandler;
    }

    public function get(string $key): array
    {
        return $this->data[$key] ?? [];
    }
}
