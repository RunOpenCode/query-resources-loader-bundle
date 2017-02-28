<?php
/*
 * This file is part of the QueryResourcesLoaderBundle, an RunOpenCode project.
 *
 * (c) 2017 RunOpenCode.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RunOpenCode\Bundle\QueryResourcesLoader\Twig;

use Symfony\Bridge\Doctrine\RegistryInterface;

class DoctrineOrmExtension extends \Twig_Extension
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
            new \Twig_SimpleFunction('table_name', \Closure::bind(function($entity) {
                return $this->getTableName($entity);
            }, $this)),
            new \Twig_SimpleFunction('column_name', \Closure::bind(function($field, $entity) {
                return $this->getColumnName($field, $entity);
            }, $this))
        );
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('table_name', \Closure::bind(function($entity) {
                return $this->getTableName($entity);
            }, $this)),
            new \Twig_SimpleFilter('column_name', \Closure::bind(function($field, $entity) {
                return $this->getColumnName($field, $entity);
            }, $this))
        );
    }

    private function getTableName($entity)
    {
        if (is_object($entity)) {
            $entity = get_class($entity);
        }

        return $this->doctrine->getManagerForClass($entity)->getClassMetadata($entity)->getTableName();
    }

    private function getColumnName($field, $entity)
    {
        if (is_object($entity)) {
            $entity = get_class($entity);
        }

        return $this->doctrine->getManagerForClass($entity)->getClassMetadata($entity)->getColumnName($field);
    }
}
