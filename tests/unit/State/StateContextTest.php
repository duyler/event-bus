<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\unit\State;

use Duyler\EventBus\State\StateContext;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class StateContextTest extends TestCase
{
    private StateContext $stateContext;

    #[Test]
    public function addResumeValue_with_string(): void
    {
        $this->stateContext->addResumeValue('actionId', 'value');
        $this->assertSame('value', $this->stateContext->getResumeValue('actionId'));
    }

    protected function setUp(): void
    {
        $this->stateContext = new StateContext();
    }
}
