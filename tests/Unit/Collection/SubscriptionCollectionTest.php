<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Test\Unit\Collection;

use Duyler\ActionBus\Collection\SubscriptionCollection;
use Duyler\ActionBus\Dto\Subscription;
use Duyler\ActionBus\Enum\ResultStatus;
use Duyler\ActionBus\Formatter\ActionIdFormatter;
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
            ['test' . ActionIdFormatter::DELIMITER . 'Success@test' => $subscription],
            $this->subscriptionCollection->getSubscriptions('test', ResultStatus::Success),
        );
    }

    public function setUp(): void
    {
        $this->subscriptionCollection = new SubscriptionCollection();
        parent::setUp();
    }
}
