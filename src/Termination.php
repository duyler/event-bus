<?php

declare(strict_types=1);

namespace Duyler\EventBus;

use Duyler\DI\ContainerInterface;
use Duyler\EventBus\Bus\State;
use Duyler\EventBus\Dto\Log as LogDto;

class Termination
{
    private State $state;
    private ?LogDto $logDto = null;

    public function __construct(
        private ContainerInterface $container,
    ) {
        /** @var State */
        $this->state = $this->container->get(State::class);
    }

    public function run(): void
    {
        $this->logDto = $this->state->getLog();
        $this->container->finalize();
    }

    public function getLog(): LogDto
    {
        if (null === $this->logDto) {
            $this->logDto = $this->state->getLog();
        }

        return $this->logDto;
    }
}
