<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Functional\Result;

use Duyler\EventBus\Build\Actor;
use Duyler\EventBus\BusBuilder;
use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Dto\Result;
use Duyler\EventBus\Exception\ActorReturnValueExistsException;
use Duyler\EventBus\Exception\ActorReturnValueMustBeTypeObjectException;
use Duyler\EventBus\Exception\DataForContractNotReceivedException;
use Duyler\EventBus\Exception\DataMustBeCompatibleWithContractException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;

class ActorResultTest extends TestCase
{
    #[Test]
    public function return_object_without_contract()
    {
        $busBuilder = new BusBuilder(new BusConfig());
        $busBuilder->doActor(
            new Actor(
                id: 'TestActor',
                handler: fn() => new stdClass(),
            ),
        );

        $bus = $busBuilder->build();

        $this->expectException(ActorReturnValueExistsException::class);

        $bus->run();
    }

    #[Test]
    public function return_with_not_exists_result_data()
    {
        $builder = new BusBuilder(new BusConfig());
        $builder->doActor(
            new Actor(
                id: 'Test',
                handler: fn() => Result::success(),
                type: stdClass::class,
                immutable: false,
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
        $busBuilder->doActor(
            new Actor(
                id: 'TestActor',
                handler: fn() => 123,
            ),
        );

        $bus = $busBuilder->build();

        $this->expectException(ActorReturnValueMustBeTypeObjectException::class);

        $bus->run();
    }

    #[Test]
    public function return_invalid_contract()
    {
        $busBuilder = new BusBuilder(new BusConfig());
        $busBuilder->doActor(
            new Actor(
                id: 'TestActor',
                handler: fn() => new class {},
                type: stdClass::class,
                immutable: false,
            ),
        );

        $bus = $busBuilder->build();

        $this->expectException(DataMustBeCompatibleWithContractException::class);

        $bus->run();
    }
}
