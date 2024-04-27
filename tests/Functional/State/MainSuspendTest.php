<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Functional\State;

use Duyler\EventBus\BusBuilder;
use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Contract\State\MainResumeStateHandlerInterface;
use Duyler\EventBus\Contract\State\MainSuspendStateHandlerInterface;
use Duyler\EventBus\Dto\Action;
use Duyler\EventBus\Dto\Context;
use Duyler\EventBus\State\Service\StateMainResumeService;
use Duyler\EventBus\State\Service\StateMainSuspendService;
use Duyler\EventBus\State\StateContext;
use Fiber;
use Override;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;

class MainSuspendTest extends TestCase
{
    #[Test]
    public function suspend_with_callback()
    {
        $busBuilder = new BusBuilder(new BusConfig());
        $busBuilder->addStateHandler(new MainSuspendStateHandler());
        $busBuilder->addStateHandler(new MainResumeStateHandler());
        $busBuilder->addStateContext(new Context(
            [
                MainSuspendStateHandler::class,
                MainResumeStateHandler::class,
            ],
        ));

        $busBuilder->doAction(
            new Action(
                id: 'TestSuspend1',
                handler: function () {
                    $callback = Fiber::suspend(fn() => 'Hello');
                    $result = $callback();
                    $data = new stdClass();
                    $data->hello = $result;

                    return $data;
                },
                contract: stdClass::class,
                externalAccess: true,
            ),
        );

        $busBuilder->doAction(
            new Action(
                id: 'TestSuspend2',
                handler: function () {
                    $callback = Fiber::suspend(fn() => 'Hello');
                    $result = $callback();
                    $data = new stdClass();
                    $data->hello = $result;

                    return $data;
                },
                required: ['TestSuspend1'],
                contract: stdClass::class,
                externalAccess: true,
            ),
        );

        $bus = $busBuilder->build();
        $bus->run();

        $this->assertTrue($bus->resultIsExists('TestSuspend1'));
        $this->assertEquals('Hello, World!', $bus->getResult('TestSuspend1')->data->hello);
        $this->assertTrue($bus->resultIsExists('TestSuspend2'));
        $this->assertEquals('Hello, World!', $bus->getResult('TestSuspend2')->data->hello);
    }
}

class MainSuspendStateHandler implements MainSuspendStateHandlerInterface
{
    #[Override]
    public function handle(StateMainSuspendService $stateService, StateContext $context): mixed
    {
        if ($stateService->getActionId() === 'TestSuspend1') {
            $stateService->getContainer();
        }

        /** @var callable $value */
        $value = $stateService->getValue();

        $result = $value();

        return fn() => $result . ', World!';
    }

    #[Override]
    public function isResumable(mixed $value): bool
    {
        return true;
    }
}

class MainResumeStateHandler implements MainResumeStateHandlerInterface
{
    #[Override]
    public function handle(StateMainResumeService $stateService, StateContext $context): mixed
    {
        $stateService->getActionId();
        if ($stateService->resultIsExists('TestSuspend2')) {
            $stateService->getResult('TestSuspend2');
        }
        return $stateService->getResumeValue();
    }
}
