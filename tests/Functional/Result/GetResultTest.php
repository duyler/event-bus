<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Functional\Result;

use Duyler\EventBus\Build\Action;
use Duyler\EventBus\BusBuilder;
use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Enum\ResultStatus;
use Duyler\EventBus\Exception\ActionNotAllowExternalAccessException;
use Duyler\EventBus\Exception\ResultNotExistsException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;

class GetResultTest extends TestCase
{
    #[Test]
    public function getResult_without_contract()
    {
        $builder = new BusBuilder(new BusConfig());
        $builder->doAction(
            new Action(
                id: 'Test',
                handler: function () {},
                externalAccess: true,
            ),
        );

        $bus = $builder->build();
        $bus->run();

        $result = $bus->getResult('Test');

        $this->assertEquals(null, $result->data);
        $this->assertEquals(ResultStatus::Success, $result->status);
    }

    #[Test]
    public function getResult_with_contract()
    {
        $builder = new BusBuilder(new BusConfig());
        $builder->doAction(
            new Action(
                id: 'Test',
                handler: fn() => new stdClass(),
                contract: stdClass::class,
                externalAccess: true,
            ),
        );

        $bus = $builder->build();
        $bus->run();

        $result = $bus->getResult('Test');

        $this->assertInstanceOf(stdClass::class, $result->data);
        $this->assertEquals(ResultStatus::Success, $result->status);
    }

    #[Test]
    public function getResult_with_external_access_exception()
    {
        $builder = new BusBuilder(new BusConfig());
        $builder->doAction(
            new Action(
                id: 'Test',
                handler: function () {},
                externalAccess: false,
            ),
        );

        $bus = $builder->build();
        $bus->run();

        $this->expectException(ActionNotAllowExternalAccessException::class);
        $this->expectExceptionMessage('Action Test does not allow external access');

        $bus->getResult('Test');
    }

    #[Test]
    public function getResult_with_not_exists_result()
    {
        $builder = new BusBuilder(new BusConfig());
        $builder->doAction(new Action(id: 'Test', handler: function () {}));

        $bus = $builder->build();
        $bus->run();

        $this->expectException(ResultNotExistsException::class);
        $this->expectExceptionMessage('Action or event result for Test_Not_Found does not exist');

        $bus->getResult('Test_Not_Found');
    }
}
