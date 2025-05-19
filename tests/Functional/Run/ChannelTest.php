<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Functional\Run;

use Duyler\EventBus\Build\Action;
use Duyler\EventBus\BusBuilder;
use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Channel\Channel;
use Duyler\EventBus\Enum\Mode;
use Fiber;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;

class ChannelTest extends TestCase
{
    #[Test]
    public function with_custom_channel_name(): void
    {
        $busBuilder = new BusBuilder(new BusConfig());
        $busBuilder->doAction(
            new Action(
                id: 'ListenChannel',
                handler: function () {
                    $message = Channel::read('custom')
                        ->listen('test');

                    $type = new stdClass();
                    $type->message = $message;
                    return $type;
                },
                required: [
                    'SendToChannel',
                ],
                type: stdClass::class,
                immutable: false,
            ),
        );

        $busBuilder->addAction(
            new Action(
                id: 'SendToChannel',
                handler: function () {
                    Channel::write('custom')
                        ->setPayload('Payload text', 'test')
                        ->push();
                },
            ),
        );

        $bus = $busBuilder->build();
        $bus->run();

        $this->assertTrue($bus->resultIsExists('ListenChannel'));
        $this->assertTrue($bus->resultIsExists('SendToChannel'));
        $this->assertEquals('Payload text', $bus->getResult('ListenChannel')->data->message);
    }

    #[Test]
    public function with_common_channel_name(): void
    {
        $busBuilder = new BusBuilder(new BusConfig());
        $busBuilder->doAction(
            new Action(
                id: 'ListenChannel',
                handler: function () {

                    $type = new stdClass();

                    $type->messageOne = Channel::read()
                        ->get('test');

                    $type->messageTwo = Channel::read()
                        ->get('SendToChannel');

                    return $type;
                },
                required: [
                    'SendToChannel',
                ],
                type: stdClass::class,
                immutable: false,
            ),
        );

        $busBuilder->addAction(
            new Action(
                id: 'SendToChannel',
                handler: function () {
                    Channel::write()
                        ->setPayload('Payload text', 'test')
                        ->push();

                    Fiber::suspend('Common channel message');
                },
            ),
        );

        $bus = $busBuilder->build();
        $bus->run();

        $this->assertTrue($bus->resultIsExists('ListenChannel'));
        $this->assertTrue($bus->resultIsExists('SendToChannel'));
        $this->assertEquals('Payload text', $bus->getResult('ListenChannel')->data->messageOne);
        $this->assertEquals('Common channel message', $bus->getResult('ListenChannel')->data->messageTwo);
    }

    #[Test]
    public function write_without_payload(): void
    {
        $busBuilder = new BusBuilder(new BusConfig());
        $busBuilder->doAction(
            new Action(
                id: 'ListenChannel',
                handler: function () {
                    $message = Channel::read('custom')
                        ->listen('test');

                    $type = new stdClass();
                    $type->message = $message;
                    return $type;
                },
                required: [
                    'SendToChannel',
                ],
                type: stdClass::class,
                immutable: false,
            ),
        );

        $busBuilder->addAction(
            new Action(
                id: 'SendToChannel',
                handler: function () {
                    Channel::write('custom')
                        ->push();
                },
            ),
        );

        $bus = $busBuilder->build();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Message has no payload');

        $bus->run();
    }

    #[Test]
    public function clean_up_expire_message(): void
    {
        $busBuilder = new BusBuilder(new BusConfig(
            mode: Mode::Loop,
        ));

        $busBuilder->doAction(
            new Action(
                id: 'ListenChannel',
                handler: function () {

                    $type = new stdClass();

                    $type->messageOne = Channel::read()
                        ->get('test');

                    usleep(10000);

                    $type->messageTwo = Channel::read()
                        ->get('SendToChannel');

                    throw new RuntimeException('Stop bus');

                    return $type;
                },
                required: [
                    'SendToChannel',
                ],
                type: stdClass::class,
                immutable: false,
            ),
        );

        $busBuilder->addAction(
            new Action(
                id: 'SendToChannel',
                handler: function () {
                    Channel::write()
                        ->setPayload('Payload text', 'test')
                        ->setTtl('10 milliseconds')
                        ->push();

                    Fiber::suspend('Common channel message');
                },
            ),
        );

        $bus = $busBuilder->build();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Stop bus');

        $bus->run();
    }

    #[Test]
    public function listen_channel_without_write(): void
    {
        $busBuilder = new BusBuilder(new BusConfig());
        $busBuilder->doAction(
            new Action(
                id: 'ListenChannel',
                handler: function () {
                    $messageOne = Channel::read('custom')
                        ->listen('test');

                    $messageTwo = Channel::read('custom')
                        ->get('test');

                    $type = new stdClass();
                    $type->messageOne = $messageOne;
                    $type->messageTwo = $messageTwo;
                    return $type;
                },
                required: [
                    'SendToChannel',
                ],
                type: stdClass::class,
                immutable: false,
            ),
        );

        $busBuilder->addAction(
            new Action(
                id: 'SendToChannel',
                handler: function () {},
            ),
        );

        $bus = $busBuilder->build();
        $bus->run();

        $this->assertTrue($bus->resultIsExists('ListenChannel'));
        $this->assertTrue($bus->resultIsExists('SendToChannel'));
        $this->assertEquals(null, $bus->getResult('ListenChannel')->data->messageOne);
        $this->assertEquals(null, $bus->getResult('ListenChannel')->data->messageTwo);
    }
}
