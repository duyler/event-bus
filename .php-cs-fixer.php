<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
;

return (new PhpCsFixer\Config())
    ->setUnsupportedPhpVersionAllowed(true)
    ->setRules([
        '@PER-CS' => true,
        'no_unused_imports' => true,
        'ordered_imports' => true,
        'type_declaration_spaces' => true,
        'array_syntax' => ['syntax' => 'short'],
        'global_namespace_import' => true,
    ])
    ->setFinder($finder);
