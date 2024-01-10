<?php

declare(strict_types=1);

namespace Duyler\EventBus\Action;

use Duyler\EventBus\Action\Exception\InvalidArgumentFactoryException;
use Duyler\EventBus\Bus\Event;
use Duyler\EventBus\Collection\ActionCollection;
use Duyler\EventBus\Collection\EventCollection;
use Duyler\EventBus\Collection\TriggerRelationCollection;
use Duyler\EventBus\Dto\Action;
use Duyler\EventBus\Enum\ResultStatus;

class ActionHandlerArgumentBuilder
{
    public function __construct(
        private EventCollection $eventCollection,
        private ActionSubstitution $actionSubstitution,
        private ActionCollection $actionCollection,
        private TriggerRelationCollection $triggerRelationCollection,
    ) {}

    /**
     * @todo be refactored
     */
    public function build(Action $action, ActionContainer $container): mixed
    {
        if (empty($action->argument)) {
            return null;
        }

        $results = [];

        if ($this->triggerRelationCollection->has($action->id)) {
            $trigger = $this->triggerRelationCollection->get($action->id)->trigger;
            if ($trigger->data !== null) {
                $results[$trigger->contract] = $trigger->data;
            }
        }

        $completeTasks = $this->eventCollection->getAllByArray($action->required->getArrayCopy());

        foreach ($completeTasks as $task) {
            $results = $this->prepareRequiredResults($task) + $results;
        }

        if ($this->actionSubstitution->isSubstituteResult($action->id)) {
            $results = $this->actionSubstitution->getSubstituteResult($action->id) + $results;
        }

        foreach ($results as $interface => $definition) {
            if ($definition instanceof $action->argument) {
                return $definition;
            }
            $container->bind([$interface => $definition::class]);
            $container->set($definition);
        }

        $factory = $container->get($action->argument);

        if (false === is_callable($factory)) {
            throw new InvalidArgumentFactoryException($action->argument);
        }

        return $factory();
    }

    private function prepareRequiredResults(Event $requiredTaskEvent): array
    {
        $results = [];

        if (ResultStatus::Fail === $requiredTaskEvent->result->status) {
            $actionsWithContract = $this->actionCollection->getByContract($requiredTaskEvent->action->contract);

            foreach ($actionsWithContract as $actionWithContract) {
                if ($this->eventCollection->isExists($actionWithContract->id)) {
                    $replaceTaskEvent = $this->eventCollection->get($actionWithContract->id);
                    if (ResultStatus::Success === $replaceTaskEvent->result->status) {
                        $interface = array_search($replaceTaskEvent->result->data::class, $actionWithContract->bind)
                            ?: $replaceTaskEvent->result->data::class;
                        $results[$interface] = $replaceTaskEvent->result->data;

                        return $results;
                    }
                }
            }
        }

        $interface = array_search($requiredTaskEvent->result->data::class, $requiredTaskEvent->action->bind)
            ?: $requiredTaskEvent->result->data::class;
        $results[$interface] = $requiredTaskEvent->result->data;

        return $results;
    }
}
