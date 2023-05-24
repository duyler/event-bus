<?php

declare(strict_types=1);

namespace Duyler\EventBus\State\Service\Trait;

use Duyler\EventBus\Control;

/**
 * @property Control $control
 */
trait RollbackService
{
    public function rollbackWithoutException(int $step = 0): void
    {
        $this->control->rollbackWithoutException($step);
    }
}
