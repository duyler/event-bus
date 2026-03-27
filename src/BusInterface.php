<?php

declare(strict_types=1);

namespace Duyler\EventBus;

use Duyler\EventBus\Dto\Event;
use Duyler\EventBus\Dto\Log;
use Duyler\EventBus\Dto\Result;
use Throwable;
use UnitEnum;

interface BusInterface
{
    /**
     * @throws Throwable
     */
    public function run(): BusInterface;

    public function getResult(string|UnitEnum $actorId, string $scope): Result;

    public function resultIsExists(string|UnitEnum $actorId, string $scope): bool;

    public function dispatchEvent(Event $event, string $scope): BusInterface;

    public function reset(): BusInterface;

    public function getLog(): Log;
}
