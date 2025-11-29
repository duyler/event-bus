<?php

declare(strict_types=1);

namespace Duyler\EventBus\Bus;

use Duyler\EventBus\Contract\ErrorHandlerInterface;
use Duyler\EventBus\Dto\Log;
use Override;
use Psr\Log\LoggerInterface;
use Throwable;

final class ErrorHandler implements ErrorHandlerInterface
{
    public function __construct(
        private LoggerInterface $logger,
    ) {}

    #[Override]
    public function handle(Throwable $exception, ?Log $log = null): void
    {
        $errorData = $log?->toArray() ?? [];
        $errorData['message'] = $exception->getMessage();
        $errorData['file'] = $exception->getFile();
        $errorData['line'] = $exception->getLine();
        $errorData['trace'] = $exception->getTraceAsString();

        $this->logger->error($exception->getMessage(), $errorData);

        throw $exception;
    }
}
