<?php

declare(strict_types=1);

namespace Duyler\EventBus\Collection;

use Duyler\EventBus\Dto\Subscription;
use Duyler\EventBus\Enum\ResultStatus;
use RuntimeException;

use function array_flip;
use function array_intersect_key;
use function array_key_exists;
use function array_keys;
use function preg_grep;
use function preg_quote;

class SubscriptionCollection extends AbstractCollection
{
    public function save(Subscription $subscription): void
    {
        $id = $this->makeSubscriptionId($subscription);

        if (array_key_exists($id, $this->data)) {
            throw new RuntimeException(
                'Subscription ' . $id . ' already registered for ' . $subscription->actionId
            );
        }

        $this->data[$id] = $subscription;
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
}
