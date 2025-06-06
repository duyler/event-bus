<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Functional\Build;

use Duyler\EventBus\Build\Trigger;
use Duyler\EventBus\Build\Action;
use Duyler\EventBus\BusBuilder;
use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Exception\SubscribedActionNotDefinedException;
use Duyler\EventBus\Exception\TriggerAlreadyDefinedException;
use Duyler\EventBus\Exception\TriggerOnNotDefinedActionException;
use Duyler\EventBus\Exception\TriggerOnSilentActionException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class AddTriggerTest extends TestCase
{
    #[Test]
    public function addTrigger_with_redefine()
    {
        $builder = new BusBuilder(new BusConfig());

        $builder->addTrigger(
            new Trigger(
                subjectId: 'Action',
                actionId: 'Subscriber',
            ),
        );

        $this->expectException(TriggerAlreadyDefinedException::class);

        $builder->addTrigger(
            new Trigger(
                subjectId: 'Action',
                actionId: 'Subscriber',
            ),
        );
    }

    #[Test]
    public function AddTrigger_on_silent_action()
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

        $this->expectException(TriggerOnSilentActionException::class);

        $builder->addTrigger(
            new Trigger(
                subjectId: 'Action',
                actionId: 'Subscriber',
            ),
        );

        $builder->build();
    }

    #[Test]
    public function AddTrigger_on_not_defined_subject_action()
    {
        $builder = new BusBuilder(new BusConfig());

        $builder->addAction(
            new Action(
                id: 'Subscriber',
                handler: fn() => null,
            ),
        );

        $this->expectException(TriggerOnNotDefinedActionException::class);

        $builder->addTrigger(
            new Trigger(
                subjectId: 'Action',
                actionId: 'Subscriber',
            ),
        );

        $builder->build();
    }

    #[Test]
    public function AddTrigger_with_undefined_target_action()
    {
        $builder = new BusBuilder(new BusConfig());

        $builder->addAction(
            new Action(
                id: 'Action',
                handler: fn() => null,
            ),
        );

        $this->expectException(SubscribedActionNotDefinedException::class);

        $builder->addTrigger(
            new Trigger(
                subjectId: 'Action',
                actionId: 'Subscriber',
            ),
        );

        $builder->build();
    }
}
