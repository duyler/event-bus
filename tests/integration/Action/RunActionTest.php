<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\integration\Action;

use Duyler\DependencyInjection\ContainerBuilder;
use Duyler\DependencyInjection\ContainerInterface;
use Duyler\EventBus\Action\ActionRunner;
use Duyler\EventBus\Contract\StateActionInterface;
use Duyler\EventBus\Dto\Action;
use Duyler\EventBus\Dto\Result;
use Duyler\EventBus\Enum\ResultStatus;
use Duyler\EventBus\State\StateAction;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class RunActionTest extends TestCase
{
    private ContainerInterface $container;

    #[Test]
    public function runAction_with_say_hello(): void
    {
        $action = new Action(
            id: 'action.id',
            handler: fn(): Result => new Result(status: ResultStatus::Success, data: new class () {
                public function sayHello(): string
                {
                    return 'hello';
                }
            }),
        );

        $actionRunner = $this->createActionRunner();

        $result = $actionRunner->runAction($action);

        $this->assertEquals('hello', $result->data->sayHello());
    }

    private function createActionRunner(): ActionRunner
    {
        $this->container = ContainerBuilder::build();
        $this->container->bind([StateActionInterface::class => StateAction::class]);
        return $this->container->make(ActionRunner::class);
    }
}
