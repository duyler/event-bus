<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Unit\Action;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Duyler\EventBus\Action\ActionContainerProvider;
use Duyler\EventBus\Build\Action;
use Duyler\EventBus\Build\SharedService;
use Duyler\EventBus\Bus\ActionContainer;
use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Storage\ActionContainerStorage;
use InvalidArgumentException;
use stdClass;

class ActionContainerProviderTest extends TestCase
{
    private BusConfig $config;
    private ActionContainerStorage $containerStorage;
    private ActionContainerProvider $provider;

    protected function setUp(): void
    {
        $this->config = new BusConfig();

        $this->containerStorage = $this->createMock(ActionContainerStorage::class);
        $this->provider = new ActionContainerProvider($this->config, $this->containerStorage);
    }

    #[Test]
    public function get_with_returns_action_container(): void
    {
        $action = new Action('id', function () {});
        $container = new ActionContainer('id', $this->config);

        $this->containerStorage->expects($this->once())
            ->method('isExists')
            ->with($action->id)
            ->willReturn(true);

        $this->containerStorage->expects($this->once())
            ->method('get')
            ->with($action->id)
            ->willReturn($container);

        $this->assertSame($container, $this->provider->get($action));
    }

    #[Test]
    public function AddSharedService_with_throws_exception_when_service_is_not_instance_of_class(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Service must be an instance of SomeClass');

        $sharedService = new SharedService('SomeClass', new stdClass(), []);

        $this->provider->addSharedService($sharedService);
    }
}
