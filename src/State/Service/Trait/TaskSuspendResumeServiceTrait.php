<?php

declare(strict_types=1);

namespace Duyler\ActionBus\State\Service\Trait;

use Duyler\ActionBus\State\Suspend;
use UnitEnum;

/**
 * @property Suspend $suspend
 */
trait TaskSuspendResumeServiceTrait
{
    public function getValue(): mixed
    {
        return $this->suspend->value;
    }

    public function getActionId(): string|UnitEnum
    {
        return $this->suspend->actionId;
    }

    public function setResumeValue(mixed $value): void
    {
        $this->suspend->setResumeValue($value);
    }

    public function resumeValueIsExists(): bool
    {
        return $this->suspend->resumeValueIsExists();
    }
}
