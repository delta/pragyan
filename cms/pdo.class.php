<?php
/**
 * PDO Class
 *
 * PHP version 5
 * 
 * @category CMS
 * @package  Pragyan
 * @class    pdo Class which uses PHP Data Objects to access database
 * @author   Vignesh Manix <vigneshmanix@gmail.com>
 * @license  http://www.gnu.org/licenses/gpl.html GNU GPL V3
 * @link     https://github.com/delta/pragyan/
 * For more details, see README
 */

if (!defined('__PRAGYAN_CMS')) {
    header($_SERVER['SERVER_PROTOCOL'].' 403 Forbidden');
    echo "<h1>403 Forbidden</h1><h4>You are not authorized to access the page.</h4>";
    echo '<hr/>'.$_SERVER['SERVER_SIGNATURE'];
    exit(1);
}

class pdodb
{
    private $_pdo;   // @object The PDO object
    private $_stmt;  // @object PDO statement object
    private $_isconnected; // @bool connected to database
    private $_params; // @array Paramaters of query


    public function __construct()  // Constructor sets $isconnected, connects to db and creates params array
    {
        $this->_isconnected = false;
        $this->_connect();
        $this->_params=array();
    }


    private function _connect()  // connect tries to connect 
    {
        if (MYSQL_SERVER=='localhost') {
            $dsn='mysql:dbname='.MYSQL_DATABASE.';host=127.0.0.1';
        } else {
            $dsn='mysql:dbname='.MYSQL_DATABASE.';host='.MYSQL_SERVER.'';
        }
        try
        {
            $this->_pdo = new PDO($dsn, MYSQL_USERNAME, MYSQL_PASSWORD, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
            $this->_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->_pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $this->_isconnected = true;
        }
        catch(PDOException $e) 
        {
            echo 'Unhandled Exception. <br />';
            echo $e->getMessage();
            die();
        }
    }


    private function _init($query,$params = "") // init tries to connect, prepare, parameterize, execute and reset params
    {
        if (!$this->_isconnected) {
            $this->_connect();
        }
        try
        {
            $this->_stmt=$this->_pdo->prepare($query);            
            $this->bindMore($params);
            if (!empty($this->_params)) {
                foreach ($this->_params as $param) {
                    $params=explode("\x7F", $param);
                    $this->_stmt->bindParam($params[0], $params[1]);
                }
            }
            $this->_stmt->execute();
        }
        catch(PDOException $e)
        {
            echo 'Unhandled Exception. <br />';
            echo $e->getMessage();
            die();
        }
        $this->_params=array();
    }


    public function bind($para,$value) // bind adds parameter to params array
    {
        $this->_params[sizeof($this->_params)] = ":" . $para . "\x7F" . utf8_encode($value);
    }


    public function bindMore($parray) // bind more
    {
        if (empty($this->_params) && is_array($parray)) {
            $columns = array_keys($parray);
            foreach ($columns as $i => &$column) {
                $this->bind($column, $parray[$column]);
            }
        }
    }


    public function query($query,$params=null,$fetchmode=PDO::FETCH_ASSOC) // returns array for SELECT and SHOW, returns number of affected rows for DELETE, INSERT and UPDATE
    {
        $query=trim($query);
        $this->_init($query, $params);
        $rawStatement=explode(" ", $query);
        $statement=strtolower($rawStatement[0]);
        if ($statement==='select'||$statement==='show') {
            return $this->_stmt->fetchAll($fetchmode);
        } elseif ($statement==='insert'||$statement==='update'||$statement==='delete') {
            return $this->_stmt->rowCount();
        } else {
            return null;
        }
    }


    public function lastInsertId()
    {
        return $this->_pdo->lastInsertId();
    }
}
