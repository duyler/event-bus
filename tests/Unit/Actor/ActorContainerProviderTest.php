<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Unit\Actor;

use Duyler\EventBus\Actor\ActorContainerProvider;
use Duyler\EventBus\Actor\ActorEventDispatcher;
use Duyler\EventBus\Build\Actor;
use Duyler\EventBus\Build\SharedService;
use Duyler\EventBus\Bus\ActorContainer;
use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Storage\ActorContainerStorage;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;

class ActorContainerProviderTest extends TestCase
{
    private BusConfig $config;
    private ActorContainerStorage $containerStorage;
    private ActorContainerProvider $provider;
    private ActorEventDispatcher $actorEventDispatcher;

    protected function setUp(): void
    {
        $this->config = new BusConfig();

        $this->containerStorage = $this->createMock(ActorContainerStorage::class);
        $this->actorEventDispatcher = $this->createStub(ActorEventDispatcher::class);
        $this->provider = new ActorContainerProvider(
            $this->config,
            $this->containerStorage,
            $this->actorEventDispatcher,
        );
    }

    #[Test]
    public function get_with_returns_actor_container(): void
    {
        $actor = new Actor('id', function (): void {});
        $container = new ActorContainer('id', $this->config);

        $this->containerStorage->expects($this->once())
            ->method('isExists')
            ->with($actor->id)
            ->willReturn(true);

        $this->containerStorage->expects($this->once())
            ->method('get')
            ->with($actor->id)
            ->willReturn($container);

        $this->assertSame($container, $this->provider->get(\Duyler\EventBus\Bus\Actor::fromExternal($actor)));
    }

    #[Test]
    #[AllowMockObjectsWithoutExpectations]
    public function AddSharedService_with_throws_exception_when_service_is_not_instance_of_class(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Service must be an instance of SomeClass');

        $sharedService = new SharedService('SomeClass', new stdClass(), []);

        $this->provider->addSharedService($sharedService);
    }
}
