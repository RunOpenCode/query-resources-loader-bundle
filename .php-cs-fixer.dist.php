<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create();
$finder = $finder
    ->in([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->notPath([
        '/\/var\//', 
    ]);

$config = new PhpCsFixer\Config();

$config->setRiskyAllowed(true);

return $config->setRules([
    '@PSR12'                     => true,
    'strict_param'               => true,
    'array_syntax'               => ['syntax' => 'short'],
    'native_function_invocation' => ['include' => ['@all']],
    'function_declaration'       => [
        'closure_fn_spacing'       => 'none',
        'closure_function_spacing' => 'none',
    ],
])->setFinder($finder);
