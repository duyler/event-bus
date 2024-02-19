<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Functional\Build;

use Duyler\EventBus\BusBuilder;
use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Dto\Subscription;
use Duyler\EventBus\Exception\SubscriptionAlreadyDefinedException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class AddSubscriptionTest extends TestCase
{
    #[Test]
    public function addSubscription_with_redefine()
    {
        $builder = new BusBuilder(new BusConfig());

        $builder->addSubscription(
            new Subscription(
                subjectId: 'Action',
                actionId: 'Subscriber',
            )
        );

        $this->expectException(SubscriptionAlreadyDefinedException::class);

        $builder->addSubscription(
            new Subscription(
                subjectId: 'Action',
                actionId: 'Subscriber',
            )
        );
    }
}
