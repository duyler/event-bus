<?php

declare(strict_types=1);

namespace Duyler\EventBus\Service;

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
                    throw new InvalidArgumentException(
                        'Required action ' . $subject . ' not registered in the bus'
                    );
                }
            }

            $this->actionCollection->save($action);
        }
    }

    public function doAction(Action $action): void
    {
        if ($this->actionCollection->isExists($action->id) === false) {
            $this->addAction($action);
        }

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
}
