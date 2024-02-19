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

class SubscriptionCollection extends AbstractCollection
{
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
        $pattern = '/' . preg_quote($this->makeActionIdWithStatus($actionId, $status) . '@') . '/';

        return array_intersect_key(
            $this->data,
            array_flip(
                preg_grep($pattern, array_keys($this->data))
            )
        );
    }

    public function remove(string $actionId): void
    {
        $subscriptions = $this->byActionId[$actionId] ?? [];

        foreach ($subscriptions as $subscription) {
            unset($this->data[$this->makeSubscriptionId($subscription)]);
        }

        unset($this->byActionId[$actionId]);
    }
}
