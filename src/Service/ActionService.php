<?php

declare(strict_types=1);

namespace Duyler\EventBus\Service;

use Duyler\EventBus\Action\ActionRequiredIterator;
use Duyler\EventBus\Bus;
use Duyler\EventBus\Collection\ActionCollection;
use Duyler\EventBus\Dto\Action;
use InvalidArgumentException;

readonly class ActionService
{
    public function __construct(
        private ActionCollection $actionCollection,
        private Bus              $bus,
    ) {
    }

    public function addAction(Action $action): void
    {
        if ($this->actionCollection->isExists($action->id) === false) {
            foreach ($action->required as $subject) {
                if ($this->actionCollection->isExists($subject) === false) {
                    $this->throwNotRegistered($subject);
                }
            }

            $this->actionCollection->save($action);
        }
    }

    public function doAction(Action $action): void
    {
        $this->addAction($action);

        $this->bus->doAction($action);
    }

    public function doExistsAction(string $actionId): void
    {
        $action = $this->actionCollection->get($actionId);

        $this->doAction($action);
    }

    public function actionIsExists(string $actionId): bool
    {
        return $this->actionCollection->isExists($actionId);
    }
    
    /** @param  Action[] $actions */
    public function collect(iterable $actions): void
    {
        foreach ($actions as $action) {

            $requiredIterator = new ActionRequiredIterator($action->required, $actions);

            foreach ($requiredIterator as $subject) {
                if (array_key_exists($subject, $actions) === false) {
                    $this->throwNotRegistered($subject);
                }
            }

            if ($this->actionCollection->isExists($action->id) === false) {
                $this->actionCollection->save($action);
            }
        }
    }

    private function throwNotRegistered(string $subject): never
    {
        throw new InvalidArgumentException(
            'Required action ' . $subject . ' not registered in the bus'
        );
    }
}
