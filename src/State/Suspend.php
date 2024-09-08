<?php

declare(strict_types=1);

namespace Duyler\EventBus\State;

use UnitEnum;

class Suspend
{
    private mixed $resumeValue = null;

    public function __construct(
        public readonly string|UnitEnum $actionId,
        public readonly mixed $value,
    ) {}

    public function setResumeValue(mixed $value): void
    {
        $this->resumeValue = $value;
    }

    public function getResumeValue(): mixed
    {
        return $this->resumeValue;
    }

    public function resumeValueIsExists(): bool
    {
        return null !== $this->resumeValue;
    }
}
