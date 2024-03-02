<?php

declare(strict_types=1);

namespace Duyler\EventBus\State;

class StateSuspendContext
{
    /** @var array<string, mixed[]> */
    private array $resumeValues = [];

    public function addResumeValue(string $actionId, mixed $value): void
    {
        $this->resumeValues[$actionId][] = $value;
    }

    public function getResumeValue(string $actionId): mixed
    {
        return array_shift($this->resumeValues[$actionId]);
    }
}
