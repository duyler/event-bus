<?php

declare(strict_types=1);

namespace Jine\EventBus\Test\unit;

use Jine\EventBus\ActionStorage;
use Jine\EventBus\Bus;
use Jine\EventBus\BusValidator;
use Jine\EventBus\ResultStorage;
use Jine\EventBus\ServiceStorage;
use Jine\EventBus\SubscribeStorage;
use Jine\EventBus\Dispatcher;
use Jine\EventBus\Dto\Service;
use Jine\EventBus\TaskManager;
use PHPUnit\Framework\TestCase;

class BusTest extends TestCase
{
    private Dispatcher $dispatcher;
    private ServiceStorage $serviceStorage;
    private SubscribeStorage $subscribeStorage;
    private ActionStorage $actionStorage;
    private BusValidator $busValidator;
    private TaskManager $taskManager;
    private ResultStorage $resultStorage;

    public function setUp(): void
    {
        $this->actionStorage = $this->createMock(ActionStorage::class);
        $this->serviceStorage = $this->createMock(ServiceStorage::class);
        $this->subscribeStorage = $this->createMock(SubscribeStorage::class);
        $this->dispatcher = $this->createMock(Dispatcher::class);
        $this->busValidator = $this->createMock(BusValidator::class);
        $this->taskManager = $this->createMock(TaskManager::class);
        $this->resultStorage = $this->createMock(ResultStorage::class);
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

    public function testActionIsExists()
    {
        $this->actionStorage->method('isExists')->willReturn(true);

        $bus = $this->createBus();

        $this->assertTrue($bus->actionIsExists('Service.Action'));
    }

    public function testRegisterSharedDefinitions()
    {
        $this->taskManager->method('registerSharedDefinitions');

        $bus = $this->createBus();

        $this->assertInstanceOf( Bus::class, $bus->registerSharedDefinitions([]));
    }

    public function testGetResult()
    {
        $this->resultStorage->method('isExists')->willReturn(false);

        $bus = $this->createBus();

        $this->assertNull($bus->getResult('Service.Action'));
    }

    private function createBus(): Bus
    {
        return new Bus(
            $this->dispatcher,
            $this->serviceStorage,
            $this->subscribeStorage,
            $this->actionStorage,
            $this->busValidator,
            $this->taskManager,
            $this->resultStorage
        );
    }
}
