<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Unit\Storage;

use Duyler\EventBus\Build\Trigger;
use Duyler\EventBus\Enum\ResultStatus;
use Duyler\EventBus\Formatter\IdFormatter;
use Duyler\EventBus\Storage\TriggerStorage;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class TriggerStorageTest extends TestCase
{
    private TriggerStorage $triggerStorage;

    #[Test]
    public function save_trigger(): void
    {
        $trigger = new Trigger(
            subjectId: 'test',
            actionId: 'test',
            status: ResultStatus::Success,
        );

        $this->triggerStorage->save($trigger);

        $this->assertTrue($this->triggerStorage->isExists($trigger));
        $this->assertSame(
            ['test' . IdFormatter::DELIMITER . 'Success@test' => $trigger],
            $this->triggerStorage->getTriggers('test', ResultStatus::Success),
        );
    }

    public function setUp(): void
    {
        $this->triggerStorage = new TriggerStorage();
        parent::setUp();
    }
}
