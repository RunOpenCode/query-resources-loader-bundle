<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->in([
        __DIR__ . '/src',
        __DIR__ . '/tests'
    ]);

$config = new PhpCsFixer\Config();

return $config->setRules([
    '@PSR12' => true,
    'strict_param' => true,
    'array_syntax' => ['syntax' => 'short'],
])->setFinder($finder);
