<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Unit\Collection;

use Duyler\EventBus\Collection\SubscriptionCollection;
use Duyler\EventBus\Dto\Subscription;
use Duyler\EventBus\Enum\ResultStatus;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class SubscriptionCollectionTest extends TestCase
{
    private SubscriptionCollection $subscriptionCollection;

    #[Test]
    public function save_subscription(): void
    {
        $subscription = new Subscription(
            subjectId: 'test',
            actionId: 'test',
            status: ResultStatus::Success,
        );

        $this->subscriptionCollection->save($subscription);

        $this->assertTrue($this->subscriptionCollection->isExists($subscription));
        $this->assertSame(
            ['test.Success@test' => $subscription],
            $this->subscriptionCollection->getSubscriptions('test', ResultStatus::Success),
        );
    }

    public function setUp(): void
    {
        $this->subscriptionCollection = new SubscriptionCollection();
        parent::setUp();
    }
}
