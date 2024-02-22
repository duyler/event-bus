<?php

declare(strict_types=1);

namespace Duyler\EventBus\Service;

use Duyler\EventBus\Bus\Bus;
use Duyler\EventBus\Bus\TriggerRelation;
use Duyler\EventBus\Bus\Validator;
use Duyler\EventBus\Collection\ActionCollection;
use Duyler\EventBus\Collection\TriggerRelationCollection;
use Duyler\EventBus\Dto\Trigger;

class TriggerService
{
    public function __construct(
        private TriggerRelationCollection $triggerRelationCollection,
        private ActionCollection $actionCollection,
        private Validator $validator,
        private Bus $bus,
    ) {}

    public function dispatch(Trigger $trigger): void
    {
        $this->validator->validateTrigger($trigger);

        $actions = $this->actionCollection->getByTrigger($trigger->id);

        foreach ($actions as $action) {
            $this->triggerRelationCollection->save(new TriggerRelation($action, $trigger));
            $this->bus->doAction($action);
        }
    }
}
