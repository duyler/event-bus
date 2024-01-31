<?php

declare(strict_types=1);

namespace Duyler\EventBus;

use Duyler\EventBus\Dto\Result;
use Duyler\EventBus\Dto\Trigger;
use Throwable;

interface BusInterface
{
    /**
     * @throws Throwable
     */
    public function run(): BusInterface;

    public function getResult(string $actionId): Result;

    public function resultIsExists(string $actionId): bool;

    public function dispatchTrigger(Trigger $trigger): BusInterface;

    public function reset(): BusInterface;
}
