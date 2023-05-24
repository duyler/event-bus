<?php

declare(strict_types=1);

namespace Duyler\EventBus;

use Duyler\EventBus\Collection\ActionCollection;
use Duyler\EventBus\Dto\Action;
use InvalidArgumentException;

readonly class ActionRegister
{
    public function __construct(private ActionCollection $collection)
    {
    }

    public function add(Action $action): void
    {
        foreach ($action->required as $subject) {
            if ($this->collection->isExists($subject) === false) {
                throw new InvalidArgumentException(
                    'Required action ' . $subject . ' not registered in the bus'
                );
            }
        }

        $this->collection->save($action);
    }
}
