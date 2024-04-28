<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Test\Functional\Build;

use Duyler\ActionBus\BusBuilder;
use Duyler\ActionBus\BusConfig;
use Duyler\ActionBus\Dto\Action;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class AddSharedServiceTest extends TestCase
{
    #[Test]
    public function addSharedService_with_bind()
    {
        $busBuilder = new BusBuilder(new BusConfig());
        $busBuilder->addSharedService(
            new SharedService('Test service'),
            [
                SharedInterface::class => SharedService::class,
            ],
        );

        $busBuilder->doAction(
            new Action(
                id: 'Test',
                handler: Handler::class,
                contract: SharedInterface::class,
                externalAccess: true,
            ),
        );

        $bus = $busBuilder->build()->run();

        $this->assertTrue($bus->resultIsExists('Test'));
        $this->assertEquals('Test service', $bus->getResult('Test')->data->foo);
    }
}

readonly class SharedService implements SharedInterface
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
