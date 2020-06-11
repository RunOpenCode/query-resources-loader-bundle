# Proposed solution

Solution is inspired by Symfony's way of loading templates into a controller
and rendering response.

Symfony proposes convention where template code is separated (as per MVC)
from application logic and held in `templates` (or `Resources/views`) directory.
Required rendering logic can be coded in templates by using very powerful templating 
language Twig. Rendering of templates is executed via dedicated service,
while templates are identified via path or bundle resource locator syntax 
(e.g. `@BundleName::template.html.twig`).

Inspired with that convention, this bundle proposes similar approach (if 
not the same approach):

- Your queries are held in separate files in your bundles, in `query` or 
`Resources/query` directories (or any other per your desire). 
- Queries are files where only query code should exist.
- Complex queries with some kind of query building logic can use Twig as 
pre-processing script language.
- You can use a dedicated service `runopencode.query_loader` to load query from 
its location by using service locator (which is bad practice) or inject the instance
of `RunOpenCode\Bundle\QueryResourcesLoader\Contract\ManagerInterface` into your class 
and let dependency injection do its magic (recommended).

## Example

In image below, a real-world example of directory structure of project
reporting bundle which uses this bundle is presented:

![Project structure with query files](img/file_structure.jpg "Real world example of this bundle usage")

In that matter, in order to execute the query stored within one of those files,
following code can be used for data source/repository class:

    // file: ReportingBundle/Repository/ReportingDataSource.php
    
    namespace MyApp\ReportingBundle\Repository;
    
    use RunOpenCode\Bundle\QueryResourcesLoader\Contract\ManagerInterface;
    
    final class ReportingDataSource
    {
        private ManagerInterface $queryLoader;
    
        public function __construct(ManagerInterface $queryLoader)
        {
            $this->queryLoader = $queryLoader;
        }
    
        public function getLedgerData(array $filters = []): iterable
        {
            return $this->queryLoader->execute('@MyAppReporting/common.ledger.sql', array(
                'year' => !empty($filters['year']) ? $filters['year'] : date('Y')
            ));
        }
    }
    
If you do not use autowiring, you should register your data source class within service
container:

    // file: ReportingBundle/Resources/config/services.yml
    
    services:
    
        MyApp\ReportingBundle\Repository\ReportingDataSource:
            arguments: [ "@RunOpenCode\Bundle\QueryResourcesLoader\Contract\ManagerInterface" ]
            
Note how your code gets cleaner by just omitting query statements from 
your PHP code.
            
            
[<< Introduction](introduction.md) | [Table of contents](index.md) | [Twig support >>](twig-support.md)
