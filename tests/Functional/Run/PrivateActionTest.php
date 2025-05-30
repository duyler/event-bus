<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Functional\Run;

use Duyler\EventBus\Build\Action;
use Duyler\EventBus\BusBuilder;
use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Exception\CannotRequirePrivateActionException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class PrivateActionTest extends TestCase
{
    #[Test]
    public function run_require_private_action()
    {
        $builder = new BusBuilder(new BusConfig());
        $builder->addAction(
            new Action(
                id: 'PrivateAction',
                handler: function (): void {},
                externalAccess: true,
                private: true,
            ),
        );

        $builder->doAction(
            new Action(
                id: 'TestAction',
                handler: function (): void {},
                required: ['PrivateAction'],
                externalAccess: true,
            ),
        );

        $builder->addAction(
            new Action(
                id: 'PrivateWithSealedAction',
                handler: function (): void {},
                externalAccess: true,
                private: true,
                sealed: ['TestAction'],
            ),
        );

        $builder->doAction(
            new Action(
                id: 'TestWithSealedAction',
                handler: function (): void {},
                required: ['PrivateAction', 'PrivateWithSealedAction'],
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

        $this->expectException(CannotRequirePrivateActionException::class);

        $builder->build();
    }
}
