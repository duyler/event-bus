<?php

declare(strict_types=1);

namespace Duyler\EventBus\Storage;

use Duyler\EventBus\Dto\Subscription;
use Duyler\EventBus\Enum\ResultStatus;

abstract class AbstractStorage
{
    protected array $data = [];

    public function getAll(): array
    {
        return $this->data;
    }

    protected function makeActionIdWithStatus(string $actionId, ResultStatus $status): string
    {
        return $actionId . '.' . $status->value;
    }

    protected function makeSubscriptionId(Subscription $subscription): string
    {
        return $subscription->subjectId . '.' . $subscription->status->value . '@' . $subscription->actionId;
    }

}
