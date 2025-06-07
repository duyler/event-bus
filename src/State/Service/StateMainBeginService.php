<?php

declare(strict_types=1);

namespace Duyler\EventBus\State\Service;

use Duyler\EventBus\Bus\Action;
use Duyler\EventBus\Formatter\IdFormatter;
use Duyler\EventBus\Service\ActionService;
use Duyler\EventBus\Service\EventService;
use Duyler\EventBus\Service\TriggerService;
use Duyler\EventBus\State\Service\Trait\ActionServiceTrait;
use Duyler\EventBus\State\Service\Trait\EventServiceTrait;
use Duyler\EventBus\State\Service\Trait\TriggerServiceTrait;
use UnitEnum;

class StateMainBeginService
{
    use ActionServiceTrait;
    use TriggerServiceTrait;
    use EventServiceTrait;

    public function __construct(
        private readonly ActionService $actionService,
        private readonly TriggerService $triggerService,
        private readonly EventService $eventService,
    ) {}

    public function getById(string|UnitEnum $actionId): Action
    {
        return $this->actionService->getById(IdFormatter::toString($actionId));
    }
}
