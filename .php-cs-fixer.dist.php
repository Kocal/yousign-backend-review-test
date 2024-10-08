<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude('var')
;

return (new PhpCsFixer\Config())
    ->setParallelConfig(PhpCsFixer\Runner\Parallel\ParallelConfigFactory::detect())
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
        'trailing_comma_in_multiline' => ['elements' => ['arrays', 'match', 'parameters']],
    ])
    ->setRiskyAllowed(true)
    ->setFinder($finder)
;
