<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Unit\Storage;

use Duyler\EventBus\Build\Subscription;
use Duyler\EventBus\Enum\ResultStatus;
use Duyler\EventBus\Formatter\IdFormatter;
use Duyler\EventBus\Storage\SubscriptionStorage;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class SubscriptionStorageTest extends TestCase
{
    private SubscriptionStorage $subscriptionStorage;

    #[Test]
    public function save_subscription(): void
    {
        $subscription = new Subscription(
            subjectId: 'test',
            actionId: 'test',
            status: ResultStatus::Success,
        );

        $this->subscriptionStorage->save($subscription);

        $this->assertTrue($this->subscriptionStorage->isExists($subscription));
        $this->assertSame(
            ['test' . IdFormatter::DELIMITER . 'Success@test' => $subscription],
            $this->subscriptionStorage->getSubscriptions('test', ResultStatus::Success),
        );
    }

    public function setUp(): void
    {
        $this->subscriptionStorage = new SubscriptionStorage();
        parent::setUp();
    }
}
