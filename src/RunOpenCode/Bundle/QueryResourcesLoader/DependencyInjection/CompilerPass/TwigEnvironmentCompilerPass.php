<?php
/*
 * This file is part of the QueryResourcesLoaderBundle, an RunOpenCode project.
 *
 * (c) 2017 RunOpenCode.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RunOpenCode\Bundle\QueryResourcesLoader\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class TwigEnvironmentCompilerPass
 *
 * Builds Twig environment, registers its extensions.
 *
 * @package RunOpenCode\Bundle\QueryResourcesLoader\DependencyInjection\CompilerPass
 */
class TwigEnvironmentCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (false === $container->hasDefinition('runopencode.query_resources_loader.twig')) {
            return;
        }

        $definition = $container->getDefinition('runopencode.query_resources_loader.twig');

        // Extensions must always be registered before everything else.
        // For instance, global variable definitions must be registered
        // afterward. If not, the globals from the extensions will never
        // be registered.
        $calls = $definition->getMethodCalls();

        $definition->setMethodCalls(array());

        foreach ($container->findTaggedServiceIds('runopencode.query_resources_loader.twig.extension') as $id => $attributes) {
            $definition->addMethodCall('addExtension', array(new Reference($id)));
        }

        $definition->setMethodCalls(array_merge($definition->getMethodCalls(), $calls));
    }
}
