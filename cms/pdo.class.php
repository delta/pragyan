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
 *
 *TODO
 *1 Write docblock for class and functions
 *2 Make connect a public function and pass database constants to constructor
 */

if (!defined('__PRAGYAN_CMS')) {
    header($_SERVER['SERVER_PROTOCOL'].' 403 Forbidden');
    echo "<h1>403 Forbidden</h1><h4>You are not authorized to access the page.</h4>";
    echo '<hr/>'.$_SERVER['SERVER_SIGNATURE'];
    exit(1);
}

class pdodb {
    private $_pdo;   // @object The PDO object
    private $_stmt;  // @object PDO statement object
    private $_isconnected; // @bool connected to database
    private $_params; // @array Paramaters of query

    // Constructor sets $isconnected, connects to db and creates params array
    public function __construct() {
        $this->_isconnected = false;
        $this->_connect();
        $this->_params=array();
    }

    // connect tries to connect 
    private function _connect() {
        if (MYSQL_SERVER=='localhost') {
            $dsn='mysql:dbname='.MYSQL_DATABASE.';host=127.0.0.1';
        } else {
            $dsn='mysql:dbname='.MYSQL_DATABASE.';host='.MYSQL_SERVER.'';
        }
        try {
            $this->_pdo = new PDO($dsn, MYSQL_USERNAME, MYSQL_PASSWORD, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
            $this->_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->_pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $this->_isconnected = true;
        } catch(PDOException $e) {
            displayerror($e->getMessage());
            die();
        }
    }

    // init tries to connect, prepare, parameterize, execute and reset params
    private function _init($query,$params = "") {
        if (!$this->_isconnected) {
            $this->_connect();
        }
        try {
            $this->_stmt=$this->_pdo->prepare($query);            
            $this->bindMore($params);
            if (!empty($this->_params)) {
                foreach ($this->_params as $param) {
                    $params=explode("\x7F", $param);
                    $this->_stmt->bindParam($params[0], $params[1]);
                }
            }
            $this->_stmt->execute();
        } catch(PDOException $e) {
            displayerror($e->getMessage());
            die();
        }
        $this->_params=array();
    }

    // exposing _pdo
    public function getpdo() {
        return $this->_pdo;
    }

    // bind adds parameter to params array
    public function bind($para,$value) {
        $this->_params[sizeof($this->_params)] = ":" . $para . "\x7F" . utf8_encode($value);
    }

    // bind more
    public function bindMore($parray) {
        if (empty($this->_params) && is_array($parray)) {
            $columns = array_keys($parray);
            foreach ($columns as $i => &$column) {
                $this->bind($column, $parray[$column]);
            }
        }
    }

    // returns array for SELECT and SHOW, returns number of affected rows for DELETE, INSERT and UPDATE
    public function query($query,$params=null,$fetchmode=PDO::FETCH_ASSOC) {
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

    // returns the last inserted id.
    public function lastInsertId() {
        return $this->_pdo->lastInsertId();
    }
}
