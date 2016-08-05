# Introduction

For reporting libraries/modules within typical PHP application 
(if stored procedures or views are not used) usual 
practice is to hold database query within repository classes, or other class
services.

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

Naturally, query code should residue in separated files and included in
project with some kind of inclusion statement or service call.

[Table of contents](index.md) | [Proposed solution](proposed-solution.md)