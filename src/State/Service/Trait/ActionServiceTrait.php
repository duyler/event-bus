<?php

declare(strict_types=1);

namespace Duyler\EventBus\State\Service\Trait;

use Duyler\EventBus\Control;
use Duyler\EventBus\Dto\Action;
use Duyler\EventBus\Dto\Subscription;

/**
 * @property Control $control
 */
trait ActionServiceTrait
{
    public function addAction(Action $action): void
    {
        if ($this->control->actionIsExists($action->id) === false) {
            $this->control->addAction($action);
        }
    }

    public function doAction(Action $action): void
    {
        $this->control->doAction($action);
    }

    public function doExistsAction(string $actionId): void
    {
        $this->control->doExistsAction($actionId);
    }

    public function actionIsExists(string $actionId): bool
    {
        return $this->control->actionIsExists($actionId);
    }
}
