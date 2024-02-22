<?php

declare(strict_types=1);

namespace Duyler\EventBus\Service;

use Duyler\EventBus\Bus\Bus;
use Duyler\EventBus\Bus\TriggerRelation;
use Duyler\EventBus\Collection\ActionCollection;
use Duyler\EventBus\Collection\TriggerRelationCollection;
use Duyler\EventBus\Dto\Trigger;
use InvalidArgumentException;

class TriggerService
{
    public function __construct(
        private TriggerRelationCollection $triggerRelationCollection,
        private ActionCollection $actionCollection,
        private Bus $bus,
    ) {}

    public function dispatch(Trigger $trigger): void
    {
        if ($trigger->data !== null) {
            if ($trigger->contract === null) {
                throw new InvalidArgumentException('Trigger contract will be received');
            }

            if ($trigger->data instanceof $trigger->contract === false) {
                throw new InvalidArgumentException('Trigger data will be compatible with ' . $trigger->contract);
            }
        } else {
            if ($trigger->contract !== null) {
                throw new InvalidArgumentException('Trigger data will be received for ' . $trigger->contract);
            }
        }

        $actions = $this->actionCollection->getByTrigger($trigger->id);

        foreach ($actions as $action) {
            $this->triggerRelationCollection->save(new TriggerRelation($action, $trigger));
            $this->bus->doAction($action);
        }
    }
}
