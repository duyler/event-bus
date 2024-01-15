<?php

declare(strict_types=1);

namespace Duyler\EventBus\State;

class StateSuspendContext
{
    private array $resumeValues = [];

    public function addResumeValue(string $actionId, mixed $value): void
    {
        $this->resumeValues[$actionId] = $value;
    }

    public function getResumeValue(string $actionId): mixed
    {
        $value = $this->resumeValues[$actionId];
        unset($this->resumeValues[$actionId]);

        return $value;
    }
}
