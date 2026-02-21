<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Unit\Exception;

use Duyler\EventBus\Enum\SubscriptionType;
use Duyler\EventBus\Exception\InvalidSubscriptionCombinationException;
use Exception;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class InvalidSubscriptionCombinationExceptionTest extends TestCase
{
    #[Test]
    public function exception_message_contains_subscription_types(): void
    {
        $types = [SubscriptionType::One, SubscriptionType::Any];

        $exception = new InvalidSubscriptionCombinationException($types);

        $this->assertStringContainsString('One', $exception->getMessage());
        $this->assertStringContainsString('Any', $exception->getMessage());
    }

    #[Test]
    public function exception_message_indicates_multiple_types(): void
    {
        $types = [SubscriptionType::One, SubscriptionType::All];

        $exception = new InvalidSubscriptionCombinationException($types);

        $this->assertStringContainsString('multiple subscription types', $exception->getMessage());
    }

    #[Test]
    public function exception_extends_exception(): void
    {
        $exception = new InvalidSubscriptionCombinationException([SubscriptionType::One]);

        $this->assertInstanceOf(Exception::class, $exception);
    }

    #[Test]
    public function exception_with_all_types(): void
    {
        $types = [
            SubscriptionType::One,
            SubscriptionType::Any,
            SubscriptionType::All,
        ];

        $exception = new InvalidSubscriptionCombinationException($types);

        $this->assertStringContainsString('One', $exception->getMessage());
        $this->assertStringContainsString('Any', $exception->getMessage());
        $this->assertStringContainsString('All', $exception->getMessage());
    }
}
