<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Test\Unit\Storage;

use Duyler\ActionBus\Bus\ActionContainer;
use Duyler\ActionBus\BusConfig;
use Duyler\ActionBus\Storage\ActionContainerStorage;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ActionContainerStorageTest extends TestCase
{
    private ActionContainerStorage $actionContainerStorage;

    #[Test]
    public function save_container(): void
    {
        $container = new ActionContainer(
            actionId: 'test',
            config: new BusConfig(),
        );

        $this->actionContainerStorage->save($container);

        $this->assertEquals($container, $this->actionContainerStorage->get($container->actionId));
    }

    protected function setUp(): void
    {
        $this->actionContainerStorage = new ActionContainerStorage();
    }
}
