<?php

declare(strict_types=1);

namespace Duyler\EventBus\Collection;

use Duyler\EventBus\Dto\Subscription;
use Duyler\EventBus\Enum\ResultStatus;

use function array_key_exists;
use function preg_quote;
use function array_intersect_key;
use function array_flip;
use function preg_grep;
use function array_keys;

class SubscriptionCollection
{
    /**
     * @var array<string, Subscription>
     */
    private array $data = [];

    /** @var array<string, array<array-key, Subscription>>  */
    private array $byActionId = [];

    public function save(Subscription $subscription): void
    {
        $id = $this->makeSubscriptionId($subscription);

        $this->data[$id] = $subscription;
        $this->byActionId[$subscription->actionId][] = $subscription;
    }

    public function isExists(Subscription $subscription): bool
    {
        $id = $this->makeSubscriptionId($subscription);

        return array_key_exists($id, $this->data);
    }

    /**
     * @return Subscription[]
     */
    public function getSubscriptions(string $actionId, ResultStatus $status): array
    {
        $pattern = '/^' . preg_quote($this->makeActionIdWithStatus($actionId, $status) . '@') . '/';

        return array_intersect_key(
            $this->data,
            array_flip(
                preg_grep($pattern, array_keys($this->data))
            )
        );
    }

    public function removeByActionId(string $actionId): void
    {
        $subscriptions = $this->byActionId[$actionId] ?? [];

        foreach ($subscriptions as $subscription) {
            unset($this->data[$this->makeSubscriptionId($subscription)]);
        }

        unset($this->byActionId[$actionId]);
    }

    public function remove(Subscription $subscription): void
    {
        $id = $this->makeSubscriptionId($subscription);
        unset($this->data[$id]);
        unset($this->byActionId[$subscription->actionId]);
    }

    private function makeActionIdWithStatus(string $actionId, ResultStatus $status): string
    {
        return $actionId . '.' . $status->value;
    }

    private function makeSubscriptionId(Subscription $subscription): string
    {
        return $subscription->subjectId . '.' . $subscription->status->value . '@' . $subscription->actionId;
    }

    public function getAll(): array
    {
        return $this->data;
    }
}
