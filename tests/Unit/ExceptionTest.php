<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Unit;

use Duyler\EventBus\Exception\ActionNotAllowExternalAccessException;
use Duyler\EventBus\Exception\ActionNotDefinedException;
use Duyler\EventBus\Exception\ContractForDataNotReceivedException;
use Duyler\EventBus\Exception\DataForContractNotReceivedException;
use Duyler\EventBus\Exception\DispatchedEventNotDefinedException;
use Duyler\EventBus\Exception\EventNotDefinedException;
use Duyler\EventBus\Exception\NotAllowedSealedActionException;
use Duyler\EventBus\Exception\ResultNotExistsException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ExceptionTest extends TestCase
{
    #[Test]
    public function action_not_allow_external_access_exception_message(): never
    {
        $this->expectException(ActionNotAllowExternalAccessException::class);
        $this->expectExceptionMessage('Action Test does not allow external access');
        throw new ActionNotAllowExternalAccessException('Test');
    }

    #[Test]
    public function action_not_defined_exception_message(): never
    {
        $this->expectException(ActionNotDefinedException::class);
        $this->expectExceptionMessage('Required action TestAction not defined in the bus');
        throw new ActionNotDefinedException('TestAction');
    }

    #[Test]
    public function contract_for_data_not_received_exception_message(): never
    {
        $this->expectException(ContractForDataNotReceivedException::class);
        $this->expectExceptionMessage('TestAction with data, but contract for data is not received');
        throw new ContractForDataNotReceivedException('TestAction');
    }

    #[Test]
    public function data_for_contract_not_received_exception_message(): never
    {
        $this->expectException(DataForContractNotReceivedException::class);
        $this->expectExceptionMessage('TestAction set as contract SomeClass, but data for contract is not received');
        throw new DataForContractNotReceivedException('TestAction', 'SomeClass');
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
        $this->expectExceptionMessage('Listen event TestEvent for action TestAction not defined in the bus');
        throw new EventNotDefinedException('TestEvent', 'TestAction');
    }

    #[Test]
    public function result_not_exists_exception_message(): never
    {
        $this->expectException(ResultNotExistsException::class);
        $this->expectExceptionMessage('Action or event result for TestAction does not exist');
        throw new ResultNotExistsException('TestAction');
    }

    #[Test]
    public function not_allowed_sealed_action_exception_message(): never
    {
        $this->expectException(NotAllowedSealedActionException::class);
        $this->expectExceptionMessage('Action A cannot be sealed to B');
        throw new NotAllowedSealedActionException('A', 'B');
    }
}
