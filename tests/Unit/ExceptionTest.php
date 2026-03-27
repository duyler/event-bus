<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Unit;

use Duyler\EventBus\Exception\ActorNotAllowExternalAccessException;
use Duyler\EventBus\Exception\ActorNotDefinedException;
use Duyler\EventBus\Exception\ContractForDataNotReceivedException;
use Duyler\EventBus\Exception\DataForContractNotReceivedException;
use Duyler\EventBus\Exception\DispatchedEventNotDefinedException;
use Duyler\EventBus\Exception\EventNotDefinedException;
use Duyler\EventBus\Exception\NotAllowedSealedActorException;
use Duyler\EventBus\Exception\ResultNotExistsException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ExceptionTest extends TestCase
{
    #[Test]
    public function actor_not_allow_external_access_exception_message(): never
    {
        $this->expectException(ActorNotAllowExternalAccessException::class);
        $this->expectExceptionMessage('Actor Test does not allow external access');
        throw new ActorNotAllowExternalAccessException('Test');
    }

    #[Test]
    public function actor_not_defined_exception_message(): never
    {
        $this->expectException(ActorNotDefinedException::class);
        $this->expectExceptionMessage('Required actor TestActor not defined in the bus');
        throw new ActorNotDefinedException('TestActor');
    }

    #[Test]
    public function contract_for_data_not_received_exception_message(): never
    {
        $this->expectException(ContractForDataNotReceivedException::class);
        $this->expectExceptionMessage('TestActor with data, but contract for data is not received');
        throw new ContractForDataNotReceivedException('TestActor');
    }

    #[Test]
    public function data_for_contract_not_received_exception_message(): never
    {
        $this->expectException(DataForContractNotReceivedException::class);
        $this->expectExceptionMessage('TestActor set as contract SomeClass, but data for contract is not received');
        throw new DataForContractNotReceivedException('TestActor', 'SomeClass');
    }

    #[Test]
    public function dispatched_event_not_defined_exception_message(): never
    {
        $this->expectException(DispatchedEventNotDefinedException::class);
        $this->expectExceptionMessage('Dispatched event TestEvent not defined in the bus');
        throw new DispatchedEventNotDefinedException('TestEvent');
    }

    #[Test]
    public function event_not_defined_exception_message(): never
    {
        $this->expectException(EventNotDefinedException::class);
        $this->expectExceptionMessage('Listen event TestEvent for actor TestActor not defined in the bus');
        throw new EventNotDefinedException('TestEvent', 'TestActor');
    }

    #[Test]
    public function result_not_exists_exception_message(): never
    {
        $this->expectException(ResultNotExistsException::class);
        $this->expectExceptionMessage('Actor or event result for TestActor does not exist');
        throw new ResultNotExistsException('TestActor');
    }

    #[Test]
    public function not_allowed_sealed_actor_exception_message(): never
    {
        $this->expectException(NotAllowedSealedActorException::class);
        $this->expectExceptionMessage('Actor A cannot be sealed to B');
        throw new NotAllowedSealedActorException('A', 'B');
    }
}
