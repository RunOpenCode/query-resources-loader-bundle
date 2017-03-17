<?php
/*
 * This file is part of the QueryResourcesLoaderBundle, an RunOpenCode project.
 *
 * (c) 2017 RunOpenCode.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RunOpenCode\Bundle\QueryResourcesLoader\Tests\Manager;

use PHPUnit\Framework\TestCase;
use RunOpenCode\Bundle\QueryResourcesLoader\Twig\DoctrineOrmExtension;
use Symfony\Bridge\Doctrine\RegistryInterface;

class DoctrineOrmExtensionTest extends TestCase
{
    /**
     * @test
     */
    public function itHasName()
    {
        $registry = $this
            ->getMockBuilder(RegistryInterface::class)
            ->getMock();

        $extension = new DoctrineOrmExtension($registry);

        $this->assertEquals('run_open_code_query_resources_loader', $extension->getName());
    }
}
