<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Unit\Storage;

use Duyler\EventBus\Build\Action;
use Duyler\EventBus\Storage\ActionStorage;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ActionStorageTest extends TestCase
{
    private ActionStorage $actionStorage;

    #[Test]
    public function save_action(): void
    {
        $action = new Action(
            id: 'test',
            handler: 'test',
            type: 'test',
            immutable: false,
        );
        $this->actionStorage->save($action);

        $this->assertSame($action, $this->actionStorage->get($action->id));
    }

    protected function setUp(): void
    {
        $this->actionStorage = new ActionStorage();
    }
}
