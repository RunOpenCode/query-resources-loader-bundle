<?php
/*
 * This file is part of the QueryResourcesLoaderBundle, an RunOpenCode project.
 *
 * (c) 2017 RunOpenCode.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RunOpenCode\Bundle\QueryResourcesLoader\Tests\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use RunOpenCode\Bundle\QueryResourcesLoader\Exception\Exception;

class ExtensionTest extends AbstractExtensionTestCase
{


    /**
     * {@inheritdoc}
     */
    protected function getContainerExtensions()
    {
        return new Exception();
    }
}
