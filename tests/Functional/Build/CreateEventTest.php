<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Functional\Build;

use DateTimeInterface;
use Duyler\EventBus\Build\Event;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;

class CreateEventTest extends TestCase
{
    #[Test]
    public function create_with_immutable_type_class()
    {
        $this->expectException(InvalidArgumentException::class);

        new Event(
            id: 'test',
            type: stdClass::class,
            immutable: true,
        );
    }

    #[Test]
    public function create_with_immutable_type_interface()
    {
        $this->expectExceptionMessage('Type of ' . DatetimeInterface::class . ' it should not be an interface');

        new Event(
            id: 'test',
            type: DatetimeInterface::class,
            immutable: true,
        );
    }
}
