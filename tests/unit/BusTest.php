<?php

declare(strict_types=1);

namespace Jine\EventBus\Test\unit;

use Jine\EventBus\ActionStorage;
use Jine\EventBus\Bus;
use Jine\EventBus\BusValidator;
use Jine\EventBus\ConfigProvider;
use Jine\EventBus\ServiceStorage;
use Jine\EventBus\SubscribeStorage;
use Jine\EventBus\Dispatcher;
use Jine\EventBus\Dto\Service;
use PHPUnit\Framework\TestCase;

class BusTest extends TestCase
{
    private Dispatcher $dispatcher;
    private ConfigProvider $configProvider;
    private ServiceStorage $serviceStorage;
    private SubscribeStorage $subscribeStorage;
    private ActionStorage $actionStorage;
    private BusValidator $busValidator;

    public function setUp(): void
    {
        $this->actionStorage = $this->createMock(ActionStorage::class);
        $this->serviceStorage = $this->createMock(ServiceStorage::class);
        $this->configProvider = $this->createMock(ConfigProvider::class);
        $this->subscribeStorage = $this->createMock(SubscribeStorage::class);
        $this->dispatcher = $this->createMock(Dispatcher::class);
        $this->busValidator = $this->createMock(BusValidator::class);
        parent::setUp();
    }

    public function testRegisterService()
    {
        $this->serviceStorage->method('Save');

        $bus = $this->createBus();

        $this->assertInstanceOf( Service::class, $bus->registerService('OneService'));
    }

    public function testSubscribe()
    {
        $this->subscribeStorage->method('save');

        $bus = $this->createBus();

        $this->assertInstanceOf( Bus::class, $bus->subscribe('OneService.Done', 'TwoService.Show'));
    }

    public function testSetCachePath()
    {
        $this->configProvider->method('setCachePath');

        $bus = $this->createBus();

        $this->assertInstanceOf( Bus::class, $bus->setCachePath('path\to\cache'));
    }

    public function testActionIsExists()
    {
        $this->actionStorage->method('isExists')->willReturn(true);

        $bus = $this->createBus();

        $this->assertTrue($bus->actionIsExists('Service.Action'));
    }

    private function createBus(): Bus
    {
        return new Bus(
            $this->dispatcher,
            $this->configProvider,
            $this->serviceStorage,
            $this->subscribeStorage,
            $this->actionStorage,
            $this->busValidator,
        );
    }
}
