<?php

namespace Eiron\ORM;

class QueryBuilder
{
    private $entity; /**< Contains the name of the entity you are currently working on with the querybuilder. */
    private $query; /**< Stores the query setted by all the calls to the querybuilder methods on this instance of it. */

    private $select = false;
    private $delete = false;
    private $count = false;

    private $from;
    private $where;
    private $andWhere = '';
    private $orWhere = '';
    private $innerJoin;
    private $leftJoin;
    private $rightJoin;
    private $on;
    private $orderBy;
    private $limit;

    /** \brief A basic way to select all the rows from a table.
    *
    * This method adds "SELECT * FROM $table" to the query. Use the following methods to filter the result, and execute the query.
    */
    public function selectFrom($entity)
    {
        $this->entity = $entity;
        $table = strtolower($entity);
        $this->select = true;
        $this->from = "SELECT * FROM $table";
        return $this;
    }

    /** \brief A basic way to delete some rows from a table.
    *
    * This method adds "DELETE FROM $table" to the query. Use the following methods to filter the result, and execute the query.
    */
    public function deleteFrom($entity)
    {
        $table = strtolower($entity);
        $this->delete = true;
        $this->from = "DELETE FROM $table";
        return $this;
    }

    /** \brief A basic way to count all the rows from a table.
    *
    * This method adds "SELECT COUNT(*) FROM $table" to the query. Use the following methods to filter the result, and execute the query.
    */
    public function countFrom($entity)
    {
        $this->entity = $entity;
        $table = strtolower($entity);
        $this->count = true;
        $this->from = "SELECT COUNT(*) FROM $table";
        return $this;
    }

    /** \brief A "where" function, with the condition as parameter.
    *
    * This method adds "WHERE $expr" to the query. You can specify more conditions using andWhere($expr) and orWhere($expr) methods.
    */
    public function where($expr)
    {
        $this->where = "WHERE $expr";
        return $this;
    }

    /** \brief Adds a second "where" using an AND operator, with the condition as parameter.
    *
    * This method adds "AND $expr" to the query. Use it to specify a second or more filter to your query. Alternatively, you can use the orWhere() method.
    */
    public function andWhere($expr)
    {
        $this->andWhere .= "AND $expr ";
        return $this;
    }

    /** \brief Adds a second "where" using an OR operator, with the condition as parameter.
    *
    * This method adds "OR $expr" to the query. Use it to specify a second or more filter to your query. Alternatively, you can use the andWhere() method.
    */
    public function orWhere($expr)
    {
        $this->orWhere .= "OR $expr ";
        return $this;
    }

    /** \brief Adds an inner join to your query between $this->entity, and the $entity parameter of this method.
    *
    * This method adds an inner join to the query. Use it to specify the joined entity as the parameter. Remember to use the on($expr) method.
    */
    public function innerJoin($entity)
    {
        $table = strtolower($entity);
        $this->innerJoin = "INNER JOIN $table";
        return $this;
    }

    /** \brief Adds a left join to your query between $this->entity, and the $entity parameter of this method.
    *
    * This method adds a left join to the query. Use it to specify the joined entity as the parameter. Remember to use the on($expr) method.
    */
    public function leftJoin($entity)
    {
        $table = strtolower($entity);
        $this->leftJoin = "LEFT JOIN $table";
        return $this;
    }

    /** \brief Adds a right join to your query between $this->entity, and the $entity parameter of this method.
    *
    * This method adds a right join to the query. Use it to specify the joined entity as the parameter. Remember to use the on($expr) method.
    */
    public function rightJoin($entity)
    {
        $table = strtolower($entity);
        $this->rightJoin = "RIGHT JOIN $table";
        return $this;
    }
    
    /** \brief Specify the fields used in a join.
    *
    * Use this method after a inner/left/right join to specify which fields should be compared.
    */
    public function on($expr)
    {
        $this->on = "ON $expr";
        return $this;
    }

    /** \brief Orders the rows returned by your query.
    *
    * This method adds an ORDER BY to the query. You can define which column and how (ASC/DESC) the results must be sorted. If you omit the second parameter, it will be ASC.
    */
    public function orderBy($column, $asc = true)
    {
        $asc = ($asc === true) ? "ASC" : "DESC";
        if (is_null($this->orderBy)) {
            $this->orderBy = "ORDER BY $column $asc";
        } else {
            $this->orderBy .= ", $column $asc";
        }
        return $this;
    }

    /** \brief Limits the number of rows returned.
    *
    * This method adds a limit to the query. You can use only the first parameter, or both. If you use only the first, only the $limit first rows will be returned. If you use both, rows from $limit to $offset will be returned.
    */
    public function limit($limit, $offset = null)
    {
        $this->limit = "LIMIT $limit";
        if (!is_null($offset)) $this->limit .= " OFFSET $offset";
        return $this;
    }
    
    /** \brief Generates the query.
    *
    * This method generates the query according to the methods you called on this instance of the query and stores it in $this->query. Remember to use the execQuery() method.
    */
    public function generateQuery()
    {
        if ($this->select || $this->delete || $this->count) {
            $this->query = $this->from;
            if ($this->innerJoin) $this->query .= "\n$this->innerJoin";
            elseif ($this->leftJoin) $this->query .= "\n$this->leftJoin";
            elseif ($this->rightJoin) $this->query .= "\n$this->rightJoin";
            if ($this->on) $this->query .= "\n$this->on";
            if ($this->where) $this->query .= "\n$this->where";
            if ($this->andWhere) $this->query .= "\n$this->andWhere";
            if ($this->orWhere) $this->query .= "\n$this->orWhere";
            if ($this->orderBy) $this->query .= "\n$this->orderBy";
            if ($this->limit) $this->query .= "\n$this->limit";
        }
        return $this;
    }
    
    /** \brief Sets a MySQL query.
    *
    * Use this method to set a custom MySQL query, if you don't want or can't use the querybuilder methods.
    */
    public function setQuery($query)
    {
        $this->query = $query;
        $this->select = false;
        $this->delete = false;
        $this->count = false;
        return $this;
    }

    /** \brief Returns the query.
    *
    * Use this method to get the query if you need it for anything.
    */
    public function getQuery($query)
    {
        return $this->query;
    }
    
    /** \brief Executes the query.
    *
    * This method is to be called in last. It executes the query created using the querybuilder or the setQuery() method and returns its result if possible.
    */
    public function execQuery()
    {
        $errorsLogs = fopen(__DIR__ . '/../../logs/ORM/errors.log', 'a');
        $queriesLogs = fopen(__DIR__ . '/../../logs/ORM/access.log', 'a');
        $date = date('Y-m-d H:i:s');
        try {
            $db = Database::getInstance();
            $response = $db->getDb()->query($this->query);
        } catch (\PDOException $e) {
            fwrite($errorsLogs, "$date : " . $e->__toString() . "\n");
            fclose($errorsLogs);
            return $e;
        }
        
        fwrite($queriesLogs, "$date : $this->query \n");
        fclose($queriesLogs);

        if ($this->select) {
            $rows = array();
            while ($row = $response->fetch()) {
                $construct = 'ORM\Model\Entity\\' . $this->entity;
                $object = new $construct();
                $columns = [];
                foreach($row as $column => $value) {
                    if (!is_int($column)) {
                        $setter = 'set'.ucfirst($column); 
                        if (method_exists($object, $setter) && !in_array($column, $columns)) {
                            $object->$setter($value);
                            array_push($columns, $column);
                        }
                    }
                }
                $rows[] = $object;
            }
            $response->closeCursor();
            return $rows;
        } elseif($this->delete) {
            return true;
        } elseif($this->count) {
            return $response->fetchColumn();
        } else {
            return $response;
        }
    }
}