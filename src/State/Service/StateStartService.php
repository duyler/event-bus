<?php

declare(strict_types=1);

namespace Duyler\EventBus\State\Service;

use Duyler\EventBus\Control;
use Duyler\EventBus\State\Service\Trait\ActionServiceTrait;
use Duyler\EventBus\State\Service\Trait\SubscriptionServiceTrait;
use Duyler\EventBus\State\StateServiceInterface;

class StateStartService implements StateServiceInterface
{
    use ActionServiceTrait;
    use SubscriptionServiceTrait;

    public function __construct(
        private readonly Control $control,
    ) {
    }
}
