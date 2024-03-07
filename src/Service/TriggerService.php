<?php

declare(strict_types=1);

namespace Duyler\EventBus\Service;

use Duyler\EventBus\Bus\Bus;
use Duyler\EventBus\Bus\TriggerRelation;
use Duyler\EventBus\Collection\ActionCollection;
use Duyler\EventBus\Collection\TriggerRelationCollection;
use Duyler\EventBus\Dto\Trigger;
use Duyler\EventBus\Exception\ContractForDataNotReceivedException;
use Duyler\EventBus\Exception\DataMustBeCompatibleWithContractException;
use Duyler\EventBus\Exception\DataForContractNotReceivedException;

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
                throw new ContractForDataNotReceivedException($trigger->id);
            }

            if ($trigger->data instanceof $trigger->contract === false) {
                throw new DataMustBeCompatibleWithContractException($trigger->id, $trigger->contract);
            }
        } else {
            if ($trigger->contract !== null) {
                throw new DataForContractNotReceivedException($trigger->id, $trigger->contract);
            }
        }

        $actions = $this->actionCollection->getByTrigger($trigger->id);

        foreach ($actions as $action) {
            $this->triggerRelationCollection->save(new TriggerRelation($action, $trigger));
            $this->bus->doAction($action);
        }
    }
}
