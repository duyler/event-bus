<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Unit\Enum;

use Duyler\EventBus\Enum\SubscriptionType;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class SubscriptionTypeTest extends TestCase
{
    #[Test]
    public function has_none_case(): void
    {
        $this->assertInstanceOf(SubscriptionType::class, SubscriptionType::None);
    }

    #[Test]
    public function has_one_case(): void
    {
        $this->assertInstanceOf(SubscriptionType::class, SubscriptionType::One);
    }

    #[Test]
    public function has_any_case(): void
    {
        $this->assertInstanceOf(SubscriptionType::class, SubscriptionType::Any);
    }

    #[Test]
    public function has_all_case(): void
    {
        $this->assertInstanceOf(SubscriptionType::class, SubscriptionType::All);
    }

    #[Test]
    public function has_exactly_four_cases(): void
    {
        $cases = SubscriptionType::cases();
        $this->assertCount(4, $cases);
    }

    #[Test]
    public function cases_are_unique(): void
    {
        $cases = SubscriptionType::cases();
        $uniqueCases = array_unique($cases, SORT_REGULAR);
        $this->assertCount(count($cases), $uniqueCases);
    }
}
