Query resources loader
======================

In any reporting application, a long query statements (per example, an
SQL statements) are used to fetch/load data from database. There
are several places where those statements can residue, database view, 
stored procedure, or, as usual, within an application code.

# Table of content

- Introduction
- Proposed solution

# Introduction

Typical example would look like in pseudocode given below:

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


Having in mind example given above, here is a short list of identified 
issues with these kind of approach:

- Method size can easily go over 30 lines of code, which is against good
coding practice.
- Consequently, class size can easily go over few hundred lines of code, 
also against good coding practice.
- Using RAD tools with some kind of SQL builder which have syntax checker,
autocomplete and testing and executing playground is impossible to use while
building an query statement.
- Mixing of query statements with application code seams wrong, almost like
mixing HTML and PHP together. 
- You can not just send your code to DB expert to help you optimise/write
some complex query if he is not familiar with PHP and Symfony, he will not
be able to work on query without your assistance. 
 
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

In image below, a real-world example of directory structure of project
reporting bundle which uses this bundle is presented:

![Project structure with query files](img/file_structure.jpg "Real world example of this bundle usage")








