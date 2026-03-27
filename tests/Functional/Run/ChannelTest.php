<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Functional\Run;

use Duyler\EventBus\Build\Actor;
use Duyler\EventBus\BusBuilder;
use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Channel\Channel;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;

class ChannelTest extends TestCase
{
    #[Test]
    public function with_custom_channel_name(): void
    {
        $busBuilder = new BusBuilder(new BusConfig());
        $busBuilder->doActor(
            new Actor(
                id: "ListenChannel",
                handler: function () {
                    $message = Channel::open("custom")->recv();

                    $type = new stdClass();
                    $type->message = $message;
                    return $type;
                },
                type: stdClass::class,
                immutable: false,
            ),
        );

        $busBuilder->doActor(
            new Actor(
                id: "SendToChannel",
                handler: function (): void {
                    Channel::open("custom")->send("Payload text");
                },
            ),
        );

        $bus = $busBuilder->build();
        $bus->run();

        $this->assertTrue($bus->resultIsExists("ListenChannel"));
        $this->assertTrue($bus->resultIsExists("SendToChannel"));
        $this->assertEquals("Payload text", $bus->getResult("ListenChannel")->data->message);
    }

    #[Test]
    public function with_common_channel_name(): void
    {
        $busBuilder = new BusBuilder(new BusConfig());
        $busBuilder->doActor(
            new Actor(
                id: "ListenChannel",
                handler: function () {
                    $type = new stdClass();

                    $type->messageOne = Channel::open()->recv();

                    return $type;
                },
                type: stdClass::class,
                immutable: false,
            ),
        );

        $busBuilder->doActor(
            new Actor(
                id: "SendToChannel",
                handler: function (): void {
                    Channel::open()->send("Payload text");
                },
            ),
        );

        $bus = $busBuilder->build();
        $bus->run();

        $this->assertTrue($bus->resultIsExists("ListenChannel"));
        $this->assertTrue($bus->resultIsExists("SendToChannel"));
        $this->assertEquals("Payload text", $bus->getResult("ListenChannel")->data->messageOne);
    }

    #[Test]
    public function listen_channel_without_write(): void
    {
        $busBuilder = new BusBuilder(new BusConfig());
        $busBuilder->doActor(
            new Actor(
                id: "ListenChannel",
                handler: function () {
                    $messageOne = Channel::open("custom")->recv();

                    $type = new stdClass();
                    $type->messageOne = $messageOne;
                    return $type;
                },
                required: ["SendToChannel"],
                type: stdClass::class,
                immutable: false,
            ),
        );

        $busBuilder->addActor(
            new Actor(id: "SendToChannel", handler: function (): void {}),
        );

        $bus = $busBuilder->build();
        $bus->run();

        $this->assertTrue($bus->resultIsExists("ListenChannel"));
        $this->assertTrue($bus->resultIsExists("SendToChannel"));
        $this->assertEquals(
            null,
            $bus->getResult("ListenChannel")->data->messageOne,
        );
        $this->assertEquals(
            null,
            $bus->getResult("ListenChannel")->data->messageTwo,
        );
    }
}
