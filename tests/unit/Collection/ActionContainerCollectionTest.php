<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\unit\Collection;

use Duyler\EventBus\Action\ActionContainer;
use Duyler\EventBus\Collection\ActionContainerCollection;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ActionContainerCollectionTest extends TestCase
{
    private ActionContainerCollection $actionContainerCollection;

    #[Test]
    public function should_save_container(): void
    {
        $container = new ActionContainer(
            actionId: 'test',
            containerCacheDir: '',
        );

        $this->actionContainerCollection->save($container);

        $this->assertEquals($container, $this->actionContainerCollection->get($container->actionId));
    }

    protected function setUp(): void
    {
        $this->actionContainerCollection = new ActionContainerCollection();
    }
}
