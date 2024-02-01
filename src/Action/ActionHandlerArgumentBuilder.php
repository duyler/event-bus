<?php

declare(strict_types=1);

namespace Duyler\EventBus\Action;

use Duyler\EventBus\Action\Exception\InvalidArgumentFactoryException;
use Duyler\EventBus\Bus\CompleteAction;
use Duyler\EventBus\Collection\ActionCollection;
use Duyler\EventBus\Collection\CompleteActionCollection;
use Duyler\EventBus\Collection\TriggerRelationCollection;
use Duyler\EventBus\Dto\Action;
use Duyler\EventBus\Enum\ResultStatus;
use LogicException;

class ActionHandlerArgumentBuilder
{
    public function __construct(
        private CompleteActionCollection $completeActionCollection,
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
            $trigger = $this->triggerRelationCollection->shift($action->id)->trigger;
            if ($trigger->data !== null) {
                $results[$trigger->contract] = $trigger->data;
            }
        }

        $completeActions = $this->completeActionCollection->getAllByArray($action->required->getArrayCopy());

        foreach ($completeActions as $completeAction) {
            $results = $this->prepareRequiredResults($completeAction) + $results;
        }

        if ($this->actionSubstitution->isSubstituteResult($action->id)) {
            $results = $this->actionSubstitution->getSubstituteResult($action->id) + $results;
        }

        foreach ($results as $interface => $definition) {
            $container->bind([$interface => $definition::class]);
            $container->set($definition);
        }

        if ($action->argumentFactory === null) {
            foreach ($results as $interface => $definition) {
                if ($definition instanceof $action->argument) {
                    return $definition;
                }
            }
            throw new LogicException(
                'Argument factory is not set to unresolved argument: ' . $action->argument . ' for ' . $action->id
            );
        }

        $factory = $container->get($action->argumentFactory);

        if (false === is_callable($factory)) {
            throw new InvalidArgumentFactoryException($action->argument);
        }

        return $factory();
    }

    private function prepareRequiredResults(CompleteAction $completeAction): array
    {
        $results = [];

        if (ResultStatus::Fail === $completeAction->result->status) {
            $actionsWithContract = $this->actionCollection->getByContract($completeAction->action->contract);

            foreach ($actionsWithContract as $actionWithContract) {
                if ($this->completeActionCollection->isExists($actionWithContract->id)) {
                    $replaceTaskEvent = $this->completeActionCollection->get($actionWithContract->id);
                    if (ResultStatus::Success === $replaceTaskEvent->result->status) {
                        $interface = array_search($replaceTaskEvent->result->data::class, $actionWithContract->bind)
                            ?: $replaceTaskEvent->result->data::class;
                        $results[$interface] = $replaceTaskEvent->result->data;

                        return $results;
                    }
                }
            }
        }

        $interface = array_search($completeAction->result->data::class, $completeAction->action->bind)
            ?: $completeAction->result->data::class;
        $results[$interface] = $completeAction->result->data;

        return $results;
    }
}
