<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Functional\Build;

use Duyler\EventBus\Build\Action;
use Duyler\EventBus\BusBuilder;
use Duyler\EventBus\BusConfig;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;

class AddSharedServiceTest extends TestCase
{
    #[Test]
    public function addSharedService_with_bind()
    {
        $busBuilder = new BusBuilder(new BusConfig());
        $busBuilder->addSharedService(
            new \Duyler\EventBus\Build\SharedService(
                class: TestSharedService::class,
                service: new TestSharedService('Test service'),
                bind: [
                    SharedInterface::class => TestSharedService::class,
                ],
            ),
        );

        $busBuilder->doAction(
            new Action(
                id: 'Test',
                handler: Handler::class,
                type: SharedInterface::class,
                immutable: false,
                externalAccess: true,
            ),
        );

        $bus = $busBuilder->build()->run();

        $this->assertTrue($bus->resultIsExists('Test'));
        $this->assertEquals('Test service', $bus->getResult('Test')->data->foo);
    }

    #[Test]
    public function addSharedService_with_invalid_class(): void
    {
        $busBuilder = new BusBuilder(new BusConfig());
        $busBuilder->addSharedService(
            new \Duyler\EventBus\Build\SharedService(
                class: stdClass::class,
                service: new TestSharedService('Test service'),
                bind: [
                    SharedInterface::class => TestSharedService::class,
                ],
            ),
        );

        $this->expectException(InvalidArgumentException::class);

        $busBuilder->doAction(
            new Action(
                id: 'Test',
                handler: Handler::class,
                type: SharedInterface::class,
                externalAccess: true,
            ),
        );

        $busBuilder->build();
    }
}

readonly class TestSharedService implements SharedInterface
{
    public function __construct(public string $foo) {}
}

interface SharedInterface {}

readonly class Handler
{
    public function __construct(
        public SharedInterface $shared,
    ) {}

    public function __invoke(): SharedInterface
    {
        return $this->shared;
    }
}
