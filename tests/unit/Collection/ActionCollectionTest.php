<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\unit\Collection;

use Duyler\EventBus\Collection\ActionCollection;
use Duyler\EventBus\Dto\Action;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ActionCollectionTest extends TestCase
{
    private ActionCollection $actionCollection;

    #[Test]
    public function save_action(): void
    {
        $action = new Action(
            id: 'test',
            handler: 'test',
            contract: 'test',
        );
        $this->actionCollection->save($action);

        $this->assertSame($action, $this->actionCollection->get($action->id));
    }

    protected function setUp(): void
    {
        $this->actionCollection = new ActionCollection();
    }
}
