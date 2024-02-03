<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\integration\Action;

use Duyler\DependencyInjection\Container;
use Duyler\DependencyInjection\ContainerInterface;
use Duyler\EventBus\Action\ActionRunnerProvider;
use Duyler\EventBus\Contract\StateActionInterface;
use Duyler\EventBus\Dto\Action;
use Duyler\EventBus\Dto\Result;
use Duyler\EventBus\Enum\ResultStatus;
use Duyler\EventBus\State\StateAction;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;

class RunActionTest extends TestCase
{
    private ContainerInterface $container;

    #[Test]
    public function runAction_with_say_hello(): void
    {
        $action = new Action(
            id: 'action.id',
            handler: fn(): Result => new Result(status: ResultStatus::Success, data: new class () extends stdClass {
                public function sayHello(): string
                {
                    return 'hello';
                }
            }),
            contract: stdClass::class,
        );

        $actionRunner = $this->createActionRunner();

        $result = $actionRunner->getRunner($action);

        $this->assertEquals('hello', $result->data->sayHello());
    }

    private function createActionRunner(): ActionRunnerProvider
    {
        $this->container = new Container();
        $this->container->bind([StateActionInterface::class => StateAction::class]);
        return $this->container->get(ActionRunnerProvider::class);
    }
}
