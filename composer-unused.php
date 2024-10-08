<?php

declare(strict_types=1);

use ComposerUnused\ComposerUnused\Configuration\Configuration;
use ComposerUnused\ComposerUnused\Configuration\NamedFilter;

return static function (Configuration $config): Configuration {
    $config->addNamedFilter(NamedFilter::fromString('symfony/doctrine-bridge'));
    $config->addNamedFilter(NamedFilter::fromString('symfony/twig-bundle'));
    $config->addNamedFilter(NamedFilter::fromString('doctrine/doctrine-bundle'));
    return $config;
};
