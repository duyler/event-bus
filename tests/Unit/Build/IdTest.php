<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Unit\Build;

use Duyler\EventBus\Build\Id;
use Duyler\EventBus\Enum\ResultStatus;
use Duyler\EventBus\Formatter\IdFormatter;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class IdTest extends TestCase
{
    #[Test]
    public function from_creates_id_with_subject_and_status(): void
    {
        $id = Id::from('ActionOne', ResultStatus::Success);

        $this->assertSame('ActionOne' . IdFormatter::DELIMITER . 'Success', $id->from);
    }

    #[Test]
    public function success_creates_id_with_success_status(): void
    {
        $id = Id::success('ActionOne');

        $this->assertSame('ActionOne' . IdFormatter::DELIMITER . 'Success', $id->from);
    }

    #[Test]
    public function fail_creates_id_with_fail_status(): void
    {
        $id = Id::fail('ActionOne');

        $this->assertSame('ActionOne' . IdFormatter::DELIMITER . 'Fail', $id->from);
    }

    #[Test]
    public function get_subject_extracts_subject_from_composite_id(): void
    {
        $id = Id::from('ActionOne', ResultStatus::Success);

        $this->assertSame('ActionOne', $id->getSubject());
    }

    #[Test]
    public function get_subject_returns_full_from_when_no_delimiter(): void
    {
        $id = new Id('SimpleId');

        $this->assertSame('SimpleId', $id->getSubject());
    }

    #[Test]
    public function get_status_extracts_result_status_from_composite_id(): void
    {
        $id = Id::from('ActionOne', ResultStatus::Success);

        $this->assertSame(ResultStatus::Success, $id->getStatus());
    }

    #[Test]
    public function get_status_returns_null_when_no_delimiter(): void
    {
        $id = new Id('SimpleId');

        $this->assertNull($id->getStatus());
    }

    #[Test]
    public function get_status_returns_fail_status(): void
    {
        $id = Id::fail('ActionOne');

        $this->assertSame(ResultStatus::Fail, $id->getStatus());
    }

    #[Test]
    public function to_string_returns_from_value(): void
    {
        $id = Id::from('ActionOne', ResultStatus::Success);

        $this->assertSame('ActionOne' . IdFormatter::DELIMITER . 'Success', (string) $id);
    }

    #[Test]
    public function from_accepts_enum_subject(): void
    {
        $id = Id::from(ResultStatus::Success, ResultStatus::Fail);

        $this->assertStringContainsString('Fail', $id->from);
    }

    #[Test]
    public function get_subject_and_status_work_with_enum_subject(): void
    {
        $id = Id::from(ResultStatus::Success, ResultStatus::Fail);

        $this->assertStringContainsString('Success', $id->getSubject());
        $this->assertSame(ResultStatus::Fail, $id->getStatus());
    }

    #[Test]
    public function get_subject_and_status_work_with_enum_action_in_success(): void
    {
        $id = Id::success(ResultStatus::Fail);

        $this->assertStringContainsString('Fail', $id->getSubject());
        $this->assertSame(ResultStatus::Success, $id->getStatus());
    }

    #[Test]
    public function get_subject_and_status_work_with_enum_action_in_fail(): void
    {
        $id = Id::fail(ResultStatus::Success);

        $this->assertStringContainsString('Success', $id->getSubject());
        $this->assertSame(ResultStatus::Fail, $id->getStatus());
    }

    #[Test]
    public function success_accepts_enum_action_id(): void
    {
        $id = Id::success(ResultStatus::Fail);

        $this->assertStringContainsString('Success', $id->from);
    }

    #[Test]
    public function fail_accepts_enum_action_id(): void
    {
        $id = Id::fail(ResultStatus::Success);

        $this->assertStringContainsString('Fail', $id->from);
    }
}
