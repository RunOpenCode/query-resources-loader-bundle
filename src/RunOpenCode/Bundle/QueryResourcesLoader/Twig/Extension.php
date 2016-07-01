<?php
/*
 * This file is part of the QueryResourcesLoaderBundle, an RunOpenCode project.
 *
 * (c) 2016 RunOpenCode
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RunOpenCode\Bundle\QueryResourcesLoader\Twig;

use Symfony\Bridge\Doctrine\RegistryInterface;

class Extension extends \Twig_Extension
{
    /**
     * @var RegistryInterface
     */
    private $doctrine;

    public function __construct(RegistryInterface $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'run_open_code_query_resources_loader';
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            new \Twig_Function('table_name', \Closure::bind(function($entity) {
                return $this->getTableName($entity);
            }, $this))
        );
    }

    public function getFilters()
    {
        return array(
            new \Twig_Filter('table_name', \Closure::bind(function($entity) {
                return $this->getTableName($entity);
            }, $this))
        );
    }

    private function getTableName($entity)
    {
        if (is_object($entity)) {
            $entity = get_class($entity);
        }

        return $this->doctrine->getEntityManagerForClass($entity)->getClassMetadata($entity)->getTableName();
    }
}
