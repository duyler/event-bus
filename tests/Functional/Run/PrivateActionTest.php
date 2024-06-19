<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Test\Functional\Run;

use Duyler\ActionBus\Build\Action;
use Duyler\ActionBus\BusBuilder;
use Duyler\ActionBus\BusConfig;
use Duyler\ActionBus\Exception\CannotRequirePrivateActionException;
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
                handler: function () {},
                externalAccess: true,
                private: true,
            ),
        );

        $builder->doAction(
            new Action(
                id: 'TestAction',
                handler: function () {},
                required: ['PrivateAction'],
                externalAccess: true,
            ),
        );

        $builder->addAction(
            new Action(
                id: 'PrivateWithSealedAction',
                handler: function () {},
                externalAccess: true,
                private: true,
                sealed: ['TestAction'],
            ),
        );

        $builder->doAction(
            new Action(
                id: 'TestWithSealedAction',
                handler: function () {},
                required: ['PrivateAction', 'PrivateWithSealedAction'],
                externalAccess: true,
            ),
        );

        $builder->doAction(
            new Action(
                id: 'SomeAction',
                handler: function () {},
                externalAccess: true,
            ),
        );

        $this->expectException(CannotRequirePrivateActionException::class);

        $builder->build();
    }
}
