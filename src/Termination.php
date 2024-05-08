<?php

declare(strict_types=1);

namespace Duyler\ActionBus;

use Duyler\DependencyInjection\ContainerInterface;
use Duyler\ActionBus\Bus\Log;
use Duyler\ActionBus\Dto\Log as LogDto;

class Termination
{
    private Log $log;
    private ?LogDto $logDto = null;

    public function __construct(
        private ContainerInterface $container,
    ) {
        $this->log = $this->container->get(Log::class);
    }

    public function run(): void
    {
        $this->logDto = $this->log->getLog();
        $this->container->finalize();
    }

    public function getLog(): LogDto
    {
        if (null === $this->logDto) {
            $this->logDto = $this->log->getLog();
        }

        return $this->logDto;
    }
}
