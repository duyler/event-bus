<?php

declare(strict_types=1);

namespace Duyler\EventBus\Action;

class ActionSubstitution
{
    private array $requiredResultSubstitutions = [];
    private array $handlerSubstitutions = [];

    public function addResultSubstitutions(string $actionId, array $substitutions): void
    {
        $this->requiredResultSubstitutions[$actionId] = $substitutions;
    }

    public function addHandlerSubstitution(string $actionId, string $handlerSubstitution): void
    {
        $this->handlerSubstitutions[$actionId] = $handlerSubstitution;
    }

    public function isSubstituteHandler(string $actionId): bool
    {
        return array_key_exists($actionId, $this->handlerSubstitutions);
    }

    public function getSubstituteHandler(string $actionId): string
    {
        return $this->handlerSubstitutions[$actionId];
    }

    public function isSubstituteResult(string $actionId): bool
    {
        return array_key_exists($actionId, $this->requiredResultSubstitutions);
    }

    public function getSubstituteResult(string $actionId): array
    {
        return $this->requiredResultSubstitutions[$actionId];
    }
}
