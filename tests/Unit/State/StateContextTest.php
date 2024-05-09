<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Test\Unit\State;

use Duyler\ActionBus\State\StateSuspendContext;
use Duyler\ActionBus\State\Suspend;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class StateContextTest extends TestCase
{
    private StateSuspendContext $stateContext;

    #[Test]
    public function addResumeValue_with_string(): void
    {
        $this->stateContext->addSuspend('actionId', new Suspend('actionId', 'value'));
        $this->assertSame('value', $this->stateContext->getSuspend('actionId')->value);
    }

    protected function setUp(): void
    {
        $this->stateContext = new StateSuspendContext();
    }
}
