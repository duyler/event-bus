<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Unit\Storage;

use Duyler\EventBus\Bus\ActorContainer;
use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Storage\ActorContainerStorage;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ActorContainerStorageTest extends TestCase
{
    private ActorContainerStorage $actorContainerStorage;

    #[Test]
    public function save_container(): void
    {
        $container = new ActorContainer(
            actorId: 'test',
            config: new BusConfig(),
        );

        $this->actorContainerStorage->save($container);

        $this->assertEquals($container, $this->actorContainerStorage->get($container->actorId));
    }

    protected function setUp(): void
    {
        $this->actorContainerStorage = new ActorContainerStorage();
    }
}
