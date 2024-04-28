<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Test\Functional\Build;

use Duyler\ActionBus\BusBuilder;
use Duyler\ActionBus\BusConfig;
use Duyler\ActionBus\Dto\Action;
use Duyler\ActionBus\Dto\Subscription;
use Duyler\ActionBus\Exception\SubscribedActionNotDefinedException;
use Duyler\ActionBus\Exception\SubscriptionAlreadyDefinedException;
use Duyler\ActionBus\Exception\SubscriptionOnNotDefinedActionException;
use Duyler\ActionBus\Exception\SubscriptionOnSilentActionException;
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
            ),
        );

        $this->expectException(SubscriptionAlreadyDefinedException::class);

        $builder->addSubscription(
            new Subscription(
                subjectId: 'Action',
                actionId: 'Subscriber',
            ),
        );
    }

    #[Test]
    public function AddSubscription_on_silent_action()
    {
        $builder = new BusBuilder(new BusConfig());

        $builder->addAction(
            new Action(
                id: 'Action',
                handler: fn() => null,
                silent: true,
            ),
        );

        $builder->addAction(
            new Action(
                id: 'Subscriber',
                handler: fn() => null,
            ),
        );

        $this->expectException(SubscriptionOnSilentActionException::class);

        $builder->addSubscription(
            new Subscription(
                subjectId: 'Action',
                actionId: 'Subscriber',
            ),
        );

        $builder->build();
    }

    #[Test]
    public function AddSubscription_on_not_defined_subject_action()
    {
        $builder = new BusBuilder(new BusConfig());

        $builder->addAction(
            new Action(
                id: 'Subscriber',
                handler: fn() => null,
            ),
        );

        $this->expectException(SubscriptionOnNotDefinedActionException::class);

        $builder->addSubscription(
            new Subscription(
                subjectId: 'Action',
                actionId: 'Subscriber',
            ),
        );

        $builder->build();
    }

    #[Test]
    public function AddSubscription_with_undefined_target_action()
    {
        $builder = new BusBuilder(new BusConfig());

        $builder->addAction(
            new Action(
                id: 'Action',
                handler: fn() => null,
            ),
        );

        $this->expectException(SubscribedActionNotDefinedException::class);

        $builder->addSubscription(
            new Subscription(
                subjectId: 'Action',
                actionId: 'Subscriber',
            ),
        );

        $builder->build();
    }
}
