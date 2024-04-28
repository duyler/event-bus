<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Service;

use Duyler\ActionBus\Bus\Bus;
use Duyler\ActionBus\Bus\TriggerRelation;
use Duyler\ActionBus\Collection\ActionCollection;
use Duyler\ActionBus\Collection\TriggerRelationCollection;
use Duyler\ActionBus\Dto\Trigger;
use Duyler\ActionBus\Exception\ContractForDataNotReceivedException;
use Duyler\ActionBus\Exception\DataForContractNotReceivedException;
use Duyler\ActionBus\Exception\DataMustBeCompatibleWithContractException;
use Duyler\ActionBus\Exception\TriggerHandlersNotFoundException;

class TriggerService
{
    public function __construct(
        private TriggerRelationCollection $triggerRelationCollection,
        private ActionCollection $actionCollection,
        private Bus $bus,
    ) {}

    public function dispatch(Trigger $trigger): void
    {
        if (null !== $trigger->data) {
            if (null === $trigger->contract) {
                throw new ContractForDataNotReceivedException($trigger->id);
            }

            if (false === $trigger->data instanceof $trigger->contract) {
                throw new DataMustBeCompatibleWithContractException($trigger->id, $trigger->contract);
            }
        } else {
            if (null !== $trigger->contract) {
                throw new DataForContractNotReceivedException($trigger->id, $trigger->contract);
            }
        }

        $actions = $this->actionCollection->getByTrigger($trigger->id);

        if (count($actions) === 0) {
            throw new TriggerHandlersNotFoundException($trigger->id);
        }

        foreach ($actions as $action) {
            $this->triggerRelationCollection->save(new TriggerRelation($action, $trigger));
            $this->bus->doAction($action);
        }
    }
}
