<?php

declare(strict_types=1);

namespace Duyler\EventBus\Service;

use Duyler\EventBus\Collection\TaskCollection;
use Duyler\EventBus\Dto\Result;

readonly class ResultService
{
    public function __construct(private TaskCollection $taskCollection)
    {
    }

    public function getResult(string $actionId): Result
    {
        return $this->taskCollection->getResult($actionId);
    }

    public function resultIsExists(string $actionId): bool
    {
        return $this->taskCollection->isExists($actionId);
    }
}
