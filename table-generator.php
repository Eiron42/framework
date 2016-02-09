<?php

require __DIR__ . '/vendor/autoload.php';

use Eiron\ORM\Database;

// TABLE CREATION

echo "You are trying to create a new table. \n\n";
echo "Table name (lowercase, letters and digits only): ";
$name = trim(str_replace(' ', '', strtolower(fgets(STDIN))));

$db = Database::getInstance();

if ($db->doesTableExists($name) !== false) exit("\nThis table already exists.");

echo "An ID column has been created.\n";

$columns = array();
$columnTypes = ['varchar', 'text', 'int', 'float', 'bool', 'date', 'datetime'];

do {
    echo "\nEnter a column name (or press Enter to stop adding columns): ";
    $columnName = trim(str_replace(' ', '', strtolower(fgets(STDIN))));
    if (!empty($columnName) && empty($columns[$columnName])) {
        do {
            echo "Enter a column type (varchar, text, int, float, bool, date or datetime)): ";
            $columnType = trim(str_replace(' ', '', strtolower(fgets(STDIN))));
        } while (!in_array($columnType, $columnTypes));

        if ($columnType === 'varchar') {
            do {
                echo "Enter a lentgh: ";
                $columnLength = trim(str_replace(' ', '', fgets(STDIN)));
                if (is_numeric($columnLength)) $columnLength = intval($columnLength);
            } while (!is_int($columnLength));
            $columns[$columnName] = "$columnType($columnLength)";
        } else {
            $columns[$columnName] = $columnType;
        }
    } elseif (!empty($columnName) && !empty($columns[$columnName])) {
        echo "This name is not available.";
    }
} while (!empty($columnName));

do {
    echo "\nDo you confirm ? (Y/n) ";
    $confirm = trim(str_replace(' ', '', fgets(STDIN)));
} while($confirm !== 'Y' && $confirm !== 'n');

if ($confirm === 'Y') {
    $error = $db->createTable($name, $columns);
    if ($error === true) echo "\nTable successfully created.\n";
    else echo $error;
} else {
    exit("Table creation aborted.");
}

$className = ucfirst($name);
$repoName = $className . 'Repository';
$class = fopen("./model/Entity/$className.php", 'x');

$properties = '';
$methods = '';
$insertColumns = '(';
$insertValues = '(';
$updateValues = '';
$columnsNames = ['id'];
foreach ($columns as $key => $value) {
    $properties .= "\n    private $$key;";
    $columnName = ucfirst($key);
    $methods .= 
"\n\n    public function get$columnName()
    {
        return \$this->$key;
    }

    public function set$columnName(\$value)
    {
        \$this->$key = \$value;
        return \$this;
    }";
    $insertColumns .= "$key, ";
    $insertValues .= "'\$this->$key', "; 
    $updateValues .= "$key = '\$this->$key', ";
    array_push($columnsNames, $key);
}
$insertColumns = rtrim($insertColumns, ", ") . ")";
$insertValues = rtrim($insertValues, ", ") . ")";
$updateValues = rtrim($updateValues, ", ");
$columnsNames = '[\'' . implode('\', \'', $columnsNames) . '\']';

$classContent =
"<?php

namespace ORM\Model\Entity;

use Eiron\ORM\Model\Repository\\$repoName as Repository;
use Eiron\ORM\QueryBuilder;
use Eiron\ORM\Database;

class $className
{
    private \$id = null;
$properties

    public function getId()
    {
        return \$this->id;
    }
    
    public function setId(\$value)
    {
        \$this->id = \$value;
        return \$this;
    }$methods

    public function storeEntity()
    {
        \$qb = new QueryBuilder();
        if (is_null(\$this->id)) {
            \$qb->setQuery(\"INSERT INTO $name $insertColumns VALUES $insertValues;\")->execQuery();
            \$db = Database::getInstance();
            \$this->id = \$db->getDb()->lastInsertId();
        } else {
            \$qb->setQuery(\"UPDATE $name SET $updateValues WHERE id = \$this->id;\")->execQuery();
        }
        return \$this;
    }
}
";

fwrite($class, $classContent);
fclose($class);

$repository = fopen("./model/Repository/$repoName.php", 'x');

$repositoryContent =
"<?php

namespace ORM\Model\Repository;

use Eiron\ORM\QueryBuilder;

class $repoName 
{
    private \$columns = $columnsNames;
    private \$entity = '$className';

    public function find(\$id)
    {
        \$qb = new QueryBuilder();
        \$entity = \$qb->selectFrom(\$this->entity)->where(\"id = \$id\")->generateQuery()->execQuery();
        if (is_array(\$entity) && !empty(\$entity)) return \$entity[0];
        else return null;
    }

    /*
    * returns an array of entities
    *
    * \$values = [\"col1\" => \"= 10\",
    *            \"col2\" => \"> 10\",
    *            \"col3\" => \"!= 'yolo'\",
    *            \"col4\" => \"IS NULL\"];
    *
    * \$operator = 'AND' || 'OR';
    *
    * \$orderBy = ['col1' => 'ASC',
    *            'col2' => 'DESC'] || null;
    *
    * \$limit = (int) || null;
    *
    * \$offset = (int) || null;
    */
    public function findBy(\$values, \$operator = 'AND', \$orderBy = null, \$limit = null, \$offset = null)
    {
        \$qb = new QueryBuilder();
        \$qb = \$qb->selectFrom(\$this->entity);
        \$first = true;
        foreach (\$values as \$key => \$value) {
            if (\$first) {
                \$qb = \$qb->where(\"\$key \$value\");
                \$first = false;
            } elseif (\$operator === 'OR') {
                \$qb = \$qb->orWhere(\"\$key \$value\");
            } else {
                \$qb = \$qb->andWhere(\"\$key \$value\");
            }
        }

        if (!is_null(\$orderBy)) {
            foreach (\$orderBy as \$col => \$order) {
                \$order = (\$order === 'DESC') ? false : true;
                \$qb = \$qb->orderBy(\$col, \$order);
            }
        }
        
        if (!is_null(\$limit)) {
            \$qb = \$qb->limit(\$limit, \$offset);
        }

        \$entity = \$qb->generateQuery()->execQuery();
        if (is_array(\$entity) && !empty(\$entity)) return \$entity;
        else return null;
    }
    
    /*
    * returns an entity, or null if none or several ones are found
    *
    * \$values = [\"col1\" => \"= 10\",
    *            \"col2\" => \"> 10\",
    *            \"col3\" => \"!= 'yolo'\",
    *            \"col4\" => \"IS NULL\"];
    *
    * \$operator = 'AND' || 'OR';
    *
    * 
    */
    public function findOneBy(\$values, \$operator = 'AND')
    {
        \$qb = new QueryBuilder();
        \$response = \$this->findBy(\$values, \$operator);
        if (is_array(\$response) && count(\$response) === 1) return \$response[0];
        else return null;
    }
    
    /*
    * returns an array containing all the entities found in the table
    */
    public function findAll()
    {
        \$qb = new QueryBuilder();
        \$response = \$qb->selectFrom(\$this->entity)->generateQuery()->execQuery();
        if (is_array(\$response) && !empty(\$response)) return \$response;
        else return null;
    }
}";

fwrite($repository, $repositoryContent);
fclose($repository);

echo "Class successfully created.\n";

/*! \file table-generator.php
    \brief Call this script to generate a new table, entity and repository.

    Use "php table-generator.php" to start the table creation. You will have to provide some informations such as the table name, and the name and type of fields. It will also create the entity and repository classes in the /model directory.
*/