<?php

declare(strict_types=1);

namespace Duyler\EventBus\State\Service\Trait;

use Duyler\EventBus\Control;

/**
 * @property Control $control
 */
trait LogService
{
    public function getFirstAction(): string
    {
        return $this->control->getFirstAction();
    }

    public function getLastAction(): string
    {
        return $this->control->getLastAction();
    }
}
