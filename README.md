Query Resources Loader Bundle
=============================

[![Packagist](https://img.shields.io/packagist/v/RunOpenCode/query-resources-loader-bundle.svg)](https://packagist.org/packages/runopencode/query-resources-loader-bundle)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/RunOpenCode/query-resources-loader-bundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/RunOpenCode/query-resources-loader-bundle/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/RunOpenCode/query-resources-loader-bundle/badges/build.png?b=master)](https://scrutinizer-ci.com/g/RunOpenCode/query-resources-loader-bundle/build-status/master)
[![Code Coverage](https://scrutinizer-ci.com/g/RunOpenCode/query-resources-loader-bundle/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/RunOpenCode/query-resources-loader-bundle/?branch=master)
[![Build Status](https://travis-ci.org/RunOpenCode/query-resources-loader-bundle.svg?branch=master)](https://travis-ci.org/RunOpenCode/query-resources-loader-bundle)

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/0c149e3f-689a-422f-998a-9eec47d580ee/big.png)](https://insight.sensiolabs.com/projects/0c149e3f-689a-422f-998a-9eec47d580ee)

The purpose of query resources loader is to help you manage and organize
your big, long, database queries, especially in application that deals
with reporting.

Read the documentation [here](docs/index.md).

# Quick example

Typical reporting repository that has a query within repository can be
like as in example below:

    class MyReportingRepository 
    {
        protected $db;
    
        public function getInvocingReportData($year)
        {
            $sql = 'SELECT 
                
                field_1.T as f1,
                field_2.T as f2,
                
                ...
                
                field_57.X as f57,
                
                ...
                
                field_n.N as fn
                
                FROM 
                
                table_name T
                
                INNER JOIN table_name_2 T2 ON (T.id = T2.t1_id)
                
                INNER JOIN table_name_3 T3 ON (T2.id = T3.t2_id)
                
                ....
                
                [More joins]
                
                WHERE
                
                T.year = :year
                
                [A lot of where statements and so on...]                                           
            ';
            
            $this->db->execute($sql, array( 
                'year' => $year 
            ));            
        }
    }

With this bundle, you can store your queries in `Resources/query` directory
as typical `.sql` file (or any other extension that your query language uses) 
and load that query using `roc.query_loader` service, thus, decreasing amount
of code in your repository classes, or, even better, you can inject service
into your repository:

    class MyReportingRepository 
    {
        protected $manager;
        
        protected $db;
    
        public function __construct(\RunOpenCode\Bundle\QueryResourcesLoader\Contract\ManagerInterface $manager, $db)
        {            
            $this->manager = $manager;
            $this->db = $db;
        }
        
        public function getInvocingReportData($year)
        {
            $sql = $this->manager->get('@BundleName/name_of_file_with_sql_query.sql');
            return $this->db->execute($sql, array(
                'year' => $year 
            ));
        }
    }

## Cherry on top

`\RunOpenCode\Bundle\QueryResourcesLoader\Contract\ManagerInterface` defines
`execute($name, array $args = array(), $executor = 'default')` method as well,
so, if you are using Doctrine, you can use this *method out-of-the-box*. 
Otherwise, you may provide your own executor (see full documentation 
regarding this topic). Here is our improved repository:

    class MyReportingRepository 
    {
        protected $manager;
    
        public function __construct(\RunOpenCode\Bundle\QueryResourcesLoader\Contract\ManagerInterface $manager)
        {            
            $this->manager = $manager;           
        }
        
        public function getInvocingReportData($year)
        {
            $sql = $this->manager->execute('@BundleName/name_of_file_with_sql_query.sql', array(
                'year' => $year
            ));
        }
    }

## Building complex queries 

Sometimes, you will need a possibility to build up your queries depending
on your application logic. For that purpose, query loader knows Twig and
all your query resources are pre-parsed with Twig, allowing you to dynamically
build your queries.

Example, file `@MyBundle/query.twig.sql`:

    SELECT * FROM my_table T
    
    WHERE T.field_1 = :some_parameter
    
    {% if some_other_parameter is defined %}
    
        AND T.field_2 = :some_other_parameter 
        
    {% endif %}
    
    ;
    

For other details about this bundle, as well as for tips on how to use it,
read the documentation [here](docs/index.md).    
    
    
 
 


