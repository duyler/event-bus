<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Unit\Collection;

use Duyler\EventBus\Action\ActionContainer;
use Duyler\EventBus\Collection\ActionContainerCollection;
use Duyler\EventBus\BusConfig;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ActionContainerCollectionTest extends TestCase
{
    private ActionContainerCollection $actionContainerCollection;

    #[Test]
    public function save_container(): void
    {
        $container = new ActionContainer(
            actionId: 'test',
            config: new BusConfig(),
        );

        $this->actionContainerCollection->save($container);

        $this->assertEquals($container, $this->actionContainerCollection->get($container->actionId));
    }

    protected function setUp(): void
    {
        $this->actionContainerCollection = new ActionContainerCollection();
    }
}