<?php

declare(strict_types=1);

namespace Duyler\EventBus\Action;

use Duyler\EventBus\Contract\ActionSubstitutionInterface;
use Override;

class ActionSubstitution implements ActionSubstitutionInterface
{
    /** @var array<string, array<string, object>> */
    private array $requiredResultSubstitutions = [];

    /** @var array<string, string> */
    private array $handlerSubstitutions = [];

    #[Override]
    public function addResultSubstitutions(string $actionId, array $substitutions): void
    {
        $this->requiredResultSubstitutions[$actionId] = $substitutions;
    }

    #[Override]
    public function addHandlerSubstitution(string $actionId, string $handlerSubstitution): void
    {
        $this->handlerSubstitutions[$actionId] = $handlerSubstitution;
    }

    #[Override]
    public function isSubstituteHandler(string $actionId): bool
    {
        return array_key_exists($actionId, $this->handlerSubstitutions);
    }

    #[Override]
    public function getSubstituteHandler(string $actionId): string
    {
        return $this->handlerSubstitutions[$actionId];
    }

    #[Override]
    public function isSubstituteResult(string $actionId): bool
    {
        return array_key_exists($actionId, $this->requiredResultSubstitutions);
    }

    #[Override]
    public function getSubstituteResult(string $actionId): array
    {
        return $this->requiredResultSubstitutions[$actionId];
    }
}
