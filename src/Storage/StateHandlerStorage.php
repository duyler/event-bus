<?php

declare(strict_types=1);

namespace Duyler\EventBus\Storage;

use Duyler\EventBus\Contract\State\StateAfterHandlerInterface;
use Duyler\EventBus\Contract\State\StateBeforeHandlerInterface;
use Duyler\EventBus\Contract\State\StateFinalHandlerInterface;
use Duyler\EventBus\Contract\State\StateStartHandlerInterface;

class StateHandlerStorage extends AbstractStorage
{
    public function save(
        StateStartHandlerInterface
        |StateAfterHandlerInterface
        |StateBeforeHandlerInterface
        |StateFinalHandlerInterface $stateHandler): void {
        $this->data[$stateHandler::TYPE_KEY][] = $stateHandler;
    }

    public function get(string $key): array
    {
        return $this->data[$key] ?? [];
    }
}
