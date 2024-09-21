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
use Duyler\EventBus\Storage\TriggerStorage;

readonly class TriggerService
{
    public function __construct(
        private TriggerStorage $triggerStorage,
        private ActionStorage $actionStorage,
        private Bus $bus,
    ) {}

    public function addTrigger(Trigger $trigger): void
    {
        if ($this->triggerStorage->isExists($trigger)) {
            throw new TriggerAlreadyDefinedException($trigger);
        }

        if (false === $this->actionStorage->isExists($trigger->actionId)) {
            throw new SubscribedActionNotDefinedException($trigger->subjectId);
        }

        if (false === $this->actionStorage->isExists($trigger->subjectId)) {
            throw new TriggerOnNotDefinedActionException($trigger);
        }

        $subject = $this->actionStorage->get($trigger->subjectId);

        if ($subject->silent) {
            throw new TriggerOnSilentActionException($trigger->actionId, $trigger->subjectId);
        }

        $this->triggerStorage->save($trigger);
    }

    public function triggerIsExists(Trigger $trigger): bool
    {
        return $this->triggerStorage->isExists($trigger);
    }

    public function resolveTriggers(CompleteAction $completeAction): void
    {
        $triggers = $this->triggerStorage->getTriggers(
            $completeAction->action->id,
            $completeAction->result->status,
        );

        foreach ($triggers as $trigger) {
            $action = $this->actionStorage->get($trigger->actionId);

            $this->bus->doAction($action);
        }
    }

    public function remove(Trigger $trigger): void
    {
        if (false === $this->triggerStorage->isExists($trigger)) {
            throw new TriggerNotFoundException($trigger);
        }

        $this->triggerStorage->remove($trigger);
    }
}
