<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Functional\Result;

use Duyler\EventBus\BusBuilder;
use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Dto\Action;
use Duyler\EventBus\Dto\Result;
use Duyler\EventBus\Enum\ResultStatus;
use Duyler\EventBus\Exception\ActionReturnValueExistsException;
use Duyler\EventBus\Exception\ActionReturnValueMustBeTypeObjectException;
use Duyler\EventBus\Exception\DataForContractNotReceivedException;
use Duyler\EventBus\Exception\DataMustBeCompatibleWithContractException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;

class ActionResultTest extends TestCase
{
    #[Test]
    public function return_object_without_contract()
    {
        $busBuilder = new BusBuilder(new BusConfig());
        $busBuilder->doAction(
            new Action(
                id: 'TestAction',
                handler: fn() => new stdClass(),
            ),
        );

        $bus = $busBuilder->build();

        $this->expectException(ActionReturnValueExistsException::class);

        $bus->run();
    }

    #[Test]
    public function return_with_not_exists_result_data()
    {
        $builder = new BusBuilder(new BusConfig());
        $builder->doAction(
            new Action(
                id: 'Test',
                handler: fn() => new Result(ResultStatus::Success),
                contract: stdClass::class,
                externalAccess: true,
            ),
        );

        $bus = $builder->build();

        $this->expectException(DataForContractNotReceivedException::class);

        $bus->run();

        $this->assertFalse($bus->resultIsExists('Test'));
    }

    #[Test]
    public function return_non_object_without_contract()
    {
        $busBuilder = new BusBuilder(new BusConfig());
        $busBuilder->doAction(
            new Action(
                id: 'TestAction',
                handler: fn() => 123,
            ),
        );

        $bus = $busBuilder->build();

        $this->expectException(ActionReturnValueMustBeTypeObjectException::class);

        $bus->run();
    }

    #[Test]
    public function return_invalid_contract()
    {
        $busBuilder = new BusBuilder(new BusConfig());
        $busBuilder->doAction(
            new Action(
                id: 'TestAction',
                handler: fn() => new class () {},
                contract: stdClass::class,
            ),
        );

        $bus = $busBuilder->build();

        $this->expectException(DataMustBeCompatibleWithContractException::class);

        $bus->run();
    }
}
