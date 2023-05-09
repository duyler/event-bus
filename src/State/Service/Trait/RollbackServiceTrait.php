<?php

declare(strict_types=1);

namespace Duyler\EventBus\State\Service\Trait;

use Duyler\EventBus\Control;

/**
 * @property Control $control
 */
trait RollbackServiceTrait
{
    public function rollback(): void
    {
        $this->control->rollback();
    }
}
