<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Test\Unit\Collection;

use Duyler\ActionBus\Bus\ActionContainer;
use Duyler\ActionBus\BusConfig;
use Duyler\ActionBus\Collection\ActionContainerCollection;
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
