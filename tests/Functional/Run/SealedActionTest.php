<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Functional\Run;

use Duyler\EventBus\Build\Action;
use Duyler\EventBus\Build\Type;
use Duyler\EventBus\BusBuilder;
use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Enum\ResultStatus;
use Duyler\EventBus\Exception\ActionNotDefinedException;
use Duyler\EventBus\Exception\NotAllowedSealedActionException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;

class SealedActionTest extends TestCase
{
    #[Test]
    public function run_require_accept_action(): void
    {
        $builder = new BusBuilder(new BusConfig());
        $builder->addAction(
            new Action(
                id: 'SealedAction',
                handler: function (): void {},
                externalAccess: true,
                sealed: ['AcceptAction'],
            ),
        );

        $builder->doAction(
            new Action(
                id: 'AcceptAction',
                handler: function (): void {},
                required: ['SealedAction'],
                externalAccess: true,
            ),
        );

        $bus = $builder->build();
        $bus->run();

        $result = $bus->getResult('SealedAction');
        $this->assertEquals(ResultStatus::Success, $result->status);
    }

    #[Test]
    public function run_require_not_accept_action(): void
    {
        $builder = new BusBuilder(new BusConfig());
        $builder->addAction(
            new Action(
                id: 'SealedAction',
                handler: function (): void {},
                externalAccess: true,
                sealed: ['SomeAction'],
            ),
        );

        $builder->doAction(
            new Action(
                id: 'NotAcceptAction',
                handler: function (): void {},
                required: ['SealedAction'],
                externalAccess: true,
            ),
        );

        $builder->doAction(
            new Action(
                id: 'SomeAction',
                handler: function (): void {},
                externalAccess: true,
            ),
        );

        $this->expectException(NotAllowedSealedActionException::class);

        $builder->build();
    }

    #[Test]
    public function run_depends_on_not_allowed_action(): void
    {
        $builder = new BusBuilder(new BusConfig(
            allowSkipUnresolvedActions: true,
        ));

        $builder->doAction(
            new Action(
                id: 'SealedAction',
                handler: fn(): stdClass => new stdClass(),
                type: stdClass::class,
                immutable: false,
                externalAccess: true,
                sealed: ['SomeAction'],
            ),
        );

        $builder->doAction(
            new Action(
                id: 'SomeAction',
                handler: function (): void {},
                externalAccess: true,
            ),
        );

        $builder->doAction(
            new Action(
                id: 'NotAllowedAction',
                handler: function (): void {},
                dependsOn: [Type::of(stdClass::class)],
                argument: stdClass::class,
                externalAccess: true,
            ),
        );

        $bus = $builder->build();
        $bus->run();

        $result = $bus->getResult('SealedAction');
        $this->assertEquals(ResultStatus::Success, $result->status);
        $this->assertFalse($bus->resultIsExists('NotAllowedAction'));
    }

    #[Test]
    public function run_sealed_with_not_defined_action(): void
    {
        $builder = new BusBuilder(new BusConfig(
            allowSkipUnresolvedActions: true,
        ));

        $builder->doAction(
            new Action(
                id: 'SealedAction',
                handler: fn(): stdClass => new stdClass(),
                type: stdClass::class,
                immutable: false,
                externalAccess: true,
                sealed: ['SomeAction'],
            ),
        );

        $builder->doAction(
            new Action(
                id: 'NotAllowedAction',
                handler: function (): void {},
                dependsOn: [Type::of(stdClass::class)],
                argument: stdClass::class,
                externalAccess: true,
            ),
        );

        $this->expectException(ActionNotDefinedException::class);

        $builder->build();
    }

    #[Test]
    public function run_depends_on_allowed_action(): void
    {
        $builder = new BusBuilder(new BusConfig());
        $builder->doAction(
            new Action(
                id: 'SealedAction',
                handler: fn(): stdClass => new stdClass(),
                type: stdClass::class,
                immutable: false,
                externalAccess: true,
                sealed: ['AllowedAction'],
            ),
        );

        $builder->doAction(
            new Action(
                id: 'AllowedAction',
                handler: function (): void {},
                dependsOn: [Type::of(stdClass::class)],
                argument: stdClass::class,
                externalAccess: true,
            ),
        );

        $bus = $builder->build();
        $bus->run();

        $result = $bus->getResult('SealedAction');
        $this->assertEquals(ResultStatus::Success, $result->status);
        $this->assertTrue($bus->resultIsExists('AllowedAction'));
    }
}
