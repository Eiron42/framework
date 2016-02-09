<?php

namespace Eiron\ORM;

use Symfony\Component\Yaml\Yaml;

class Database
{
    private static $instance = null;
    private $db;
    private $host;
    private $dbname;
    private $login;
    private $password;

    /** \brief The Database singleton __construct.
    *
    * This constructor is called by the getInstance() method if necessary to create the database connection, using the informations found in the /config/database.yml file.
    */
    private function __construct()
    {
        $dbInfo = Yaml::parse(file_get_contents(__DIR__ . '/../../config/database.yml'));
        $this->host = $dbInfo['host'];
        $this->dbname = $dbInfo['dbname'];
        $this->login = $dbInfo['login'];
        $this->password = $dbInfo['password'];
        $errorsLogs = fopen(__DIR__ . '/../../logs/ORM/errors.log', 'a');
        $date = date('Y-m-d H:i:s');
        try {
            $this->db = new \PDO("mysql:host=".$this->host.";dbname=".$this->dbname, $this->login, $this->password);
            $this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            fwrite($errorsLogs, "$date : " . $e->__toString() . "\n");
            fclose($errorsLogs);
            return $e;
        }
    }

    /** \brief Use this method to get an instance of the Database object, whether it's already been created or not.
    *
    * This static method is to be called to get an instance of this object. As a singleton, its constructor is private, but this method is static. It will return the instance of Database stored in $this->instance, or create it if necessary.
    */
    public static function getInstance()
    {
        if (is_null(self::$instance)) self::$instance = new Database();
        return self::$instance;
    }

    /** \brief Use this method to create a table.
    *
    * This method creates a table named $name with the fields specified in the $fields array.
    */
    public function createTable($name, $fields)
    {
        $errorsLogs = fopen(__DIR__ . '/../../logs/ORM/errors.log', 'a');
        $queriesLogs = fopen(__DIR__ . '/../../logs/ORM/access.log', 'a');
        $date = date('Y-m-d H:i:s');
        try {
            $sql = "CREATE TABLE IF NOT EXISTS $name
                    (id INT AUTO_INCREMENT PRIMARY KEY";
            foreach ($fields as $key => $value) {
                $sql .= ",\n$key $value";
            }
            $sql .= ");";
            $this->db->exec($sql);
        } catch (\PDOException $e) {
            fwrite($errorsLogs, "$date : " . $e->__toString() . "\n");
            fclose($errorsLogs);
            return $e;
        }
        
        fwrite($queriesLogs, "$date : $sql \n");
        fclose($queriesLogs);
        return true;
    }

    /** \brief Use this method to drop a table.
    *
    * This method drops the $name table.
    */
    public function dropTable($name)
    {
        $errorsLogs = fopen(__DIR__ . '/../../logs/ORM/errors.log', 'a');
        $queriesLogs = fopen(__DIR__ . '/../../logs/ORM/access.log', 'a');
        $date = date('Y-m-d H:i:s');
        $sql = "DROP TABLE IF EXISTS $name";
        try {
            $this->db->exec($sql);
        } catch (\PDOException $e) {
            fwrite($errorsLogs, "$date : " . $e->__toString() . "\n");
            fclose($errorsLogs);
            return $e;
        }
        fwrite($queriesLogs, "$date : $sql \n");
        fclose($queriesLogs);
        return true;
    }

    /** \brief Use this method to check if a table exists.
    *
    * This method checks if a table named $name already exists.
    */
    public function doesTableExists($name)
    {
        $errorsLogs = fopen(__DIR__ . '/../../logs/ORM/errors.log', 'a');
        $queriesLogs = fopen(__DIR__ . '/../../logs/ORM/access.log', 'a');
        $date = date('Y-m-d H:i:s');
        $sql = "SELECT * 
                FROM information_schema.tables
                WHERE table_name = '".$name."'
                LIMIT 1";
        try {
            $query = $this->db->query($sql);
        } catch (\PDOException $e) {
            fwrite($errorsLogs, "$date : " . $e->__toString() . "\n");
            fclose($errorsLogs);
            return $e;
        }
        
        fwrite($queriesLogs, "$date : $sql \n");
        fclose($queriesLogs);
        if ($query->fetch() === false) return false;
        else return true;
    }
    
    public function getDb()
    {
        return $this->db;
    }
}

// add login info in a yaml or something file