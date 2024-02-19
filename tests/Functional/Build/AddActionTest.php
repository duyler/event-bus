<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Functional\Build;

use Duyler\EventBus\BusBuilder;
use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Dto\Action;
use Duyler\EventBus\Exception\ActionAlreadyDefinedException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class AddActionTest extends TestCase
{
    #[Test]
    public function addAction_with_redefine()
    {
        $builder = new BusBuilder(new BusConfig());

        $builder->addAction(
            new Action(
                id: 'Test',
                handler: function () {},
            )
        );

        $this->expectException(ActionAlreadyDefinedException::class);

        $builder->addAction(
            new Action(
                id: 'Test',
                handler: function () {},
            )
        );
    }

    #[Test]
    public function doAction_with_redefine()
    {
        $builder = new BusBuilder(new BusConfig());

        $builder->doAction(
            new Action(
                id: 'Test',
                handler: function () {},
                externalAccess: true,
            )
        );

        $this->expectException(ActionAlreadyDefinedException::class);

        $builder->doAction(
            new Action(
                id: 'Test',
                handler: function () {},
                externalAccess: true,
            )
        );
    }
}
