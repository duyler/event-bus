<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Unit;

use Duyler\EventBus\Dto\Result;
use Duyler\EventBus\Enum\ResultStatus;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;

class ResultTest extends TestCase
{
    #[Test]
    public function success_without_data_sets_status_success_and_data_null()
    {
        $result = Result::success();
        $this->assertEquals(ResultStatus::Success, $result->status);
        $this->assertNull($result->data);
    }

    #[Test]
    public function success_with_data_sets_status_success_and_data_object()
    {
        $data = new stdClass();
        $result = Result::success($data);
        $this->assertEquals(ResultStatus::Success, $result->status);
        $this->assertSame($data, $result->data);
    }

    #[Test]
    public function fail_sets_status_fail_and_data_null()
    {
        $result = Result::fail();
        $this->assertEquals(ResultStatus::Fail, $result->status);
        $this->assertNull($result->data);
    }
} 