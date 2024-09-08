<?php

declare(strict_types=1);

namespace Duyler\EventBus;

use Duyler\EventBus\Dto\Log;
use Duyler\EventBus\Dto\Result;
use Duyler\EventBus\Dto\Event;
use Throwable;
use UnitEnum;

interface BusInterface
{
    /**
     * @throws Throwable
     */
    public function run(): BusInterface;

    public function getResult(string|UnitEnum $actionId): Result;

    public function resultIsExists(string|UnitEnum $actionId): bool;

    public function dispatchEvent(Event $event): BusInterface;

    public function reset(): BusInterface;

    public function getLog(): Log;
}
