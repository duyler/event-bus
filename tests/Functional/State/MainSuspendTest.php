<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Functional\State;

use Duyler\EventBus\BusBuilder;
use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Contract\State\MainResumeStateHandlerInterface;
use Duyler\EventBus\Contract\State\MainSuspendStateHandlerInterface;
use Duyler\EventBus\Dto\Action;
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
        $busBuilder->doAction(
            new Action(
                id: 'TestSuspend',
                handler: function () {
                    $callback = Fiber::suspend(fn() => 'Hello');
                    $result = $callback();
                    $data = new stdClass();
                    $data->hello = $result;
                    return $data;
                },
                contract: stdClass::class,
                externalAccess: true,
            )
        );

        $bus = $busBuilder->build();
        $bus->run();

        $this->assertTrue($bus->resultIsExists('TestSuspend'));
        $this->assertEquals('Hello, World!', $bus->getResult('TestSuspend')->data->hello);
    }
}

class MainSuspendStateHandler implements MainSuspendStateHandlerInterface
{
    #[Override]
    public function handle(StateMainSuspendService $stateService, StateContext $context): mixed
    {
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
        return $stateService->getResumeValue();
    }
}
