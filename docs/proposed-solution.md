# Proposed solution

Solution is inspired with Symfony's way of loading templates into controller
and rendering response.

Symfony proposes convention where template code is separated (as per MVC)
from application logic and held in `Resources/views` directory. Required 
rendering logic can be coded in templates by using very powerful script 
language Twig. Rendering of templates is executed via dedicated service,
while templates are identified via bundle resource locator syntax 
(e.g. `@BundleName::template.html.twig`).

Inspired with that convention, this bundle proposes similar approach (if 
not the same approach):

- Your queries are held in separate files in your bundles, in `Resources/query`
directories. 
- Queries are files where only query code should exist.
- Complex queries with some kind of query building logic can use Twig as 
pre-processing script language.
- You can use a dedicated service `roc.query_loader` to load query from 
its location by using bundle locator syntax (you can use that service to 
execute query immediately as well), or even better, you can inject service
into your repository classes.

## Example

In image below, a real-world example of directory structure of project
reporting bundle which uses this bundle is presented:

![Project structure with query files](img/file_structure.jpg "Real world example of this bundle usage")

In that matter, in order to execute query stored within one of those files,
following code can be used for data source/repository class:

    // file: ReportingBundle/Repository/ReportingDataSource.php
    
    namespace MyApp\ReportingBundle\Repository;
    
    use RunOpenCode\Bundle\QueryResourcesLoader\Contract\ManagerInterface;
    
    class ReportingDataSource
    {
        /**
         * @var ManagerInterface
         */
        protected $queryLoader;
    
        public function __construct(ManagerInterface $queryLoader)
        {
            $this->queryLoader = $queryLoader;
        }
    
        /**
         * @param array $filters
         *
         * @return \Iterator
         */
        public function getLedgerData(array $filters = array())
        {
            return $this->queryLoader->execute('@MyAppReporting/common.ledger.sql', array(
                'year' => !empty($filters['year']) ? $filters['year'] : date('Y')
            ));
        }
    }
    
and, of course, you should register your data source class within service
container:

    // file: ReportingBundle/Resources/config/services.yml
    
    services:
    
        my_app.reporting.common_reports.data_source:
            class: MyApp\ReportingBundle\Repository\ReportingDataSource
            arguments: [ "@roc.query_loader" ]
            
Note how your code gets cleaner by just omitting query statements from 
your PHP code.
            
            
[<< Introduction](introduction.md) | [Table of contents](index.md) | [Twig support >>](twig-support.md)
