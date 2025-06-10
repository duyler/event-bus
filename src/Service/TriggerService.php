<?php

declare(strict_types=1);

namespace Duyler\EventBus\Service;

use Duyler\EventBus\Build\Trigger;
use Duyler\EventBus\Bus\Bus;
use Duyler\EventBus\Bus\CompleteAction;
use Duyler\EventBus\Exception\SubscribedActionNotDefinedException;
use Duyler\EventBus\Exception\TriggerAlreadyDefinedException;
use Duyler\EventBus\Exception\TriggerNotFoundException;
use Duyler\EventBus\Exception\TriggerOnNotDefinedActionException;
use Duyler\EventBus\Exception\TriggerOnSilentActionException;
use Duyler\EventBus\Storage\ActionStorage;

readonly class TriggerService
{
    public function __construct(
        private ActionStorage $actionStorage,
        private Bus $bus,
    ) {}

    public function addTrigger(Trigger $trigger): void
    {
        if (false === $this->actionStorage->isExists($trigger->actionId)) {
            throw new SubscribedActionNotDefinedException($trigger->subjectId);
        }

        if (false === $this->actionStorage->isExists($trigger->subjectId)) {
            throw new TriggerOnNotDefinedActionException($trigger);
        }

        $subject = $this->actionStorage->get($trigger->subjectId);

        if ($subject->triggerIsExists($trigger->actionId, $trigger->status)) {
            throw new TriggerAlreadyDefinedException($trigger);
        }

        if ($subject->isSilent()) {
            throw new TriggerOnSilentActionException($trigger->actionId, $trigger->subjectId);
        }

        $subject->addTrigger($trigger);
        $triggeredOn = $this->actionStorage->get($trigger->actionId);
        $triggeredOn->addTriggeredOn($trigger->subjectId);
    }

    public function triggerIsExists(Trigger $trigger): bool
    {
        return $this->actionStorage
            ->get($trigger->subjectId)
            ->triggerIsExists($trigger->actionId, $trigger->status);
    }

    public function resolveTriggers(CompleteAction $completeAction): void
    {
        $triggers = $completeAction->action->getTriggers($completeAction->result->status);

        foreach ($triggers as $actionId) {
            $action = $this->actionStorage->get($actionId);
            $this->bus->doAction($action);
        }
    }

    public function remove(Trigger $trigger): void
    {
        $action = $this->actionStorage->get($trigger->subjectId);

        if (false === $action->triggerIsExists($trigger->actionId, $trigger->status)) {
            throw new TriggerNotFoundException($trigger);
        }

        $action->removeTrigger($trigger->actionId, $trigger->status);
    }
}
