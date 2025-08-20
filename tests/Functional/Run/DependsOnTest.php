<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Functional\Run;

use ArrayIterator;
use Duyler\EventBus\Action\Context\ActionContext;
use Duyler\EventBus\Build\Action;
use Duyler\EventBus\Build\Type;
use Duyler\EventBus\BusBuilder;
use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Contract\State\MainAfterStateHandlerInterface;
use Duyler\EventBus\Contract\State\MainBeginStateHandlerInterface;
use Duyler\EventBus\State\Service\StateMainAfterService;
use Duyler\EventBus\State\Service\StateMainBeginService;
use Duyler\EventBus\State\StateContext;
use Override;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;

class DependsOnTest extends TestCase
{
    #[Test]
    public function should_return_depends_type(): void
    {
        $builder = new BusBuilder(new BusConfig());
        $builder->doAction(new Action(
            id: 'TestReturn',
            handler: fn() => new stdClass(),
            type: stdClass::class,
            immutable: false,
        ));

        $builder->doAction(new Action(
            id: 'TestReturnDepend',
            handler: fn(ActionContext $context) => $context->argument(),
            dependsOn: [Type::of(stdClass::class)],
            argument: stdClass::class,
            type: stdClass::class,
            immutable: false,
        ));

        $bus = $builder->build();
        $bus->run();

        $this->assertInstanceOf(stdClass::class, $bus->getResult('TestReturnDepend')->data);
    }

    #[Test]
    public function should_removed_recursively(): void
    {
        $builder = new BusBuilder(new BusConfig());
        $builder->addStateHandler(
            new class implements MainBeginStateHandlerInterface {
                #[Override]
                public function handle(StateMainBeginService $stateService, StateContext $context): void
                {
                    $stateService->doAction(new Action(
                        id: 'TestReturn',
                        handler: fn() => new stdClass(),
                        type: stdClass::class,
                        immutable: false,
                    ));

                    $stateService->doAction(new Action(
                        id: 'TestReturnDepend',
                        handler: fn(ActionContext $context) => $context->argument(),
                        dependsOn: [Type::of(stdClass::class)],
                        argument: stdClass::class,
                        type: stdClass::class,
                        immutable: false,
                    ));
                }
            },
        );

        $builder->addStateHandler(
            new class implements MainAfterStateHandlerInterface {
                #[Override]
                public function handle(StateMainAfterService $stateService, StateContext $context): void
                {
                    $stateService->removeAction('TestReturn');
                }

                #[Override]
                public function observed(StateContext $context): array
                {
                    return ['TestReturnDepend'];
                }
            },
        );

        $bus = $builder->build();
        $bus->run();

        $this->assertFalse($bus->resultIsExists('TestReturn'));
        $this->assertFalse($bus->resultIsExists('TestReturnDepend'));
    }

    #[Test]
    public function should_not_removed_recursively(): void
    {
        $builder = new BusBuilder(new BusConfig());
        $builder->addStateHandler(
            new class implements MainBeginStateHandlerInterface {
                #[Override]
                public function handle(StateMainBeginService $stateService, StateContext $context): void
                {
                    $stateService->doAction(new Action(
                        id: 'TestReturnDepend',
                        handler: fn(ActionContext $context) => $context->argument(),
                        dependsOn: [Type::of(stdClass::class)],
                        argument: stdClass::class,
                        type: stdClass::class,
                        immutable: false,
                    ));

                    $stateService->doAction(new Action(
                        id: 'TestReturn',
                        handler: fn() => new stdClass(),
                        type: stdClass::class,
                        immutable: false,
                    ));

                    $stateService->doAction(new Action(
                        id: 'TestReturnEqual',
                        handler: fn() => new stdClass(),
                        type: stdClass::class,
                        immutable: false,
                    ));

                    $stateService->doAction(new Action(
                        id: 'TestDepend',
                        handler: function (ActionContext $context): void {},
                        dependsOn: [Type::of(stdClass::class)],
                        argument: stdClass::class,
                    ));
                }
            },
        );

        $builder->addStateHandler(
            new class implements MainAfterStateHandlerInterface {
                #[Override]
                public function handle(StateMainAfterService $stateService, StateContext $context): void
                {
                    $stateService->removeAction('TestReturn');
                }

                #[Override]
                public function observed(StateContext $context): array
                {
                    return ['TestDepend'];
                }
            },
        );

        $bus = $builder->build();
        $bus->run();

        $this->assertFalse($bus->resultIsExists('TestReturn'));
        $this->assertTrue($bus->resultIsExists('TestDepend'));
        $this->assertTrue($bus->resultIsExists('TestReturnEqual'));
        $this->assertTrue($bus->resultIsExists('TestReturnDepend'));
    }

    #[Test]
    public function should_not_removed_recursively_with_type_collection(): void
    {
        $builder = new BusBuilder(new BusConfig());
        $builder->addStateHandler(
            new class implements MainBeginStateHandlerInterface {
                #[Override]
                public function handle(StateMainBeginService $stateService, StateContext $context): void
                {
                    $stateService->doAction(new Action(
                        id: 'TestReturnDepend',
                        handler: fn(ActionContext $context) => new ArrayIterator([$context->argument()]),
                        dependsOn: [Type::collectionOf(stdClass::class)],
                        argument: ArrayIterator::class,
                        type: stdClass::class,
                        typeCollection: ArrayIterator::class,
                        immutable: false,
                    ));

                    $stateService->doAction(new Action(
                        id: 'TestReturn',
                        handler: fn() => new ArrayIterator([new stdClass()]),
                        type: stdClass::class,
                        typeCollection: ArrayIterator::class,
                        immutable: false,
                    ));

                    $stateService->doAction(new Action(
                        id: 'TestReturnEqual',
                        handler: fn() => new ArrayIterator([new stdClass()]),
                        type: stdClass::class,
                        typeCollection: ArrayIterator::class,
                        immutable: false,
                    ));

                    $stateService->doAction(new Action(
                        id: 'TestDepend',
                        handler: function (ActionContext $context): void {},
                        dependsOn: [Type::collectionOf(stdClass::class)],
                        argument: ArrayIterator::class,
                    ));
                }
            },
        );

        $builder->addStateHandler(
            new class implements MainAfterStateHandlerInterface {
                #[Override]
                public function handle(StateMainAfterService $stateService, StateContext $context): void
                {
                    $stateService->removeAction('TestReturn');
                }

                #[Override]
                public function observed(StateContext $context): array
                {
                    return ['TestDepend'];
                }
            },
        );

        $bus = $builder->build();
        $bus->run();

        $this->assertFalse($bus->resultIsExists('TestReturn'));
        $this->assertTrue($bus->resultIsExists('TestDepend'));
        $this->assertTrue($bus->resultIsExists('TestReturnEqual'));
        $this->assertTrue($bus->resultIsExists('TestReturnDepend'));
    }
}
