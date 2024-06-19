<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Test\Functional\Run;

use Duyler\ActionBus\Build\Action;
use Duyler\ActionBus\BusBuilder;
use Duyler\ActionBus\BusConfig;
use Duyler\ActionBus\Dto\Result;
use Duyler\ActionBus\Enum\ResultStatus;
use Duyler\ActionBus\Exception\UnableToContinueWithFailActionException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;

class AlternateActionTest extends TestCase
{
    #[Test]
    public function run_with_alternate_action()
    {
        $busBuilder = new BusBuilder(new BusConfig());
        $busBuilder->doAction(
            new Action(
                id: 'Test',
                handler: function () {},
                required: ['RequiredAction'],
                argument: stdClass::class,
                externalAccess: true,
            ),
        );

        $busBuilder->addAction(
            new Action(
                id: 'RequiredAction',
                handler: fn() => new Result(ResultStatus::Fail, null),
                required: [],
                contract: stdClass::class,
                externalAccess: true,
                alternates: [
                    'AlternateRequiredAction',
                ],
            ),
        );

        $busBuilder->addAction(
            new Action(
                id: 'AlternateRequiredAction',
                handler: fn() => new stdClass(),
                contract: stdClass::class,
                externalAccess: true,
            ),
        );

        $bus = $busBuilder->build()->run();

        $this->assertTrue($bus->resultIsExists('Test'));
        $this->assertTrue($bus->resultIsExists('RequiredAction'));
        $this->assertTrue($bus->resultIsExists('AlternateRequiredAction'));
    }

    #[Test]
    public function run_with_alternate_action_with_not_allowed_skip()
    {
        $busBuilder = new BusBuilder(new BusConfig(allowSkipUnresolvedActions: false));
        $busBuilder->doAction(
            new Action(
                id: 'Test',
                handler: function () {},
                required: ['RequiredAction'],
                externalAccess: true,
            ),
        );

        $busBuilder->addAction(
            new Action(
                id: 'RequiredAction',
                handler: fn() => new Result(ResultStatus::Fail, null),
                required: [],
                contract: stdClass::class,
                externalAccess: true,
                alternates: [
                    'AlternateRequiredAction',
                ],
            ),
        );

        $busBuilder->addAction(
            new Action(
                id: 'AlternateRequiredAction',
                handler: fn() => new Result(ResultStatus::Fail, new stdClass()),
                contract: stdClass::class,
                externalAccess: true,
            ),
        );

        $this->expectException(UnableToContinueWithFailActionException::class);

        $bus = $busBuilder->build()->run();

        $this->assertFalse($bus->resultIsExists('Test'));
        $this->assertTrue($bus->resultIsExists('RequiredAction'));
        $this->assertTrue($bus->resultIsExists('AlternateRequiredAction'));
    }

    #[Test]
    public function run_with_alternate_action_with_allowed_skip()
    {
        $busBuilder = new BusBuilder(new BusConfig(allowSkipUnresolvedActions: true));
        $busBuilder->doAction(
            new Action(
                id: 'Test',
                handler: function () {},
                required: ['RequiredAction'],
                externalAccess: true,
            ),
        );

        $busBuilder->addAction(
            new Action(
                id: 'RequiredAction',
                handler: fn() => new Result(ResultStatus::Fail, null),
                required: [],
                contract: stdClass::class,
                externalAccess: true,
                alternates: [
                    'AlternateRequiredAction',
                ],
            ),
        );

        $busBuilder->addAction(
            new Action(
                id: 'AlternateRequiredAction',
                handler: fn() => new Result(ResultStatus::Fail, new stdClass()),
                contract: stdClass::class,
                externalAccess: true,
                retries: 3,
            ),
        );

        $bus = $busBuilder->build()->run();

        $this->assertFalse($bus->resultIsExists('Test'));
        $this->assertTrue($bus->resultIsExists('RequiredAction'));
        $this->assertTrue($bus->resultIsExists('AlternateRequiredAction'));
    }

    #[Test]
    public function run_with_retrying_action()
    {
        $busBuilder = new BusBuilder(new BusConfig());
        $busBuilder->doAction(
            new Action(
                id: 'Test',
                handler: function () {},
                required: ['RequiredAction'],
                externalAccess: true,
            ),
        );

        $busBuilder->addAction(
            new Action(
                id: 'RequiredAction',
                handler: fn() => new Result(ResultStatus::Fail, new stdClass()),
                contract: stdClass::class,
                externalAccess: true,
                alternates: [
                    'AlternateRequiredAction',
                ],
                retries: 2,
            ),
        );

        $busBuilder->addAction(
            new Action(
                id: 'AlternateRequiredAction',
                handler: fn() => new stdClass(),
                contract: stdClass::class,
                externalAccess: true,
            ),
        );

        $bus = $busBuilder->build()->run();

        $this->assertTrue($bus->resultIsExists('Test'));
        $this->assertTrue($bus->resultIsExists('RequiredAction'));
        $this->assertTrue($bus->resultIsExists('AlternateRequiredAction'));
    }
}
