<?php
if(!defined('__PRAGYAN_CMS'))
    { 
        header($_SERVER['SERVER_PROTOCOL'].' 403 Forbidden');
        echo "<h1>403 Forbidden</h1><h4>You are not authorized to access the page.</h4>";
        echo '<hr/>'.$_SERVER['SERVER_SIGNATURE'];
        exit(1);
    }
/**
 * @package pragyan
 * @class pdo Class which uses PHP Data Objects to access database
 * @author Vignesh Manix
 * @license http://www.gnu.org/licenses/gpl.html
 * For more details, see README
 */
 
class pdodb
{
    private $pdo;   # @object The PDO object
    private $stmt;  # @object PDO statement object
    private $isConnected; # @bool Connected to database
    private $params; # @array Paramaters of query


    public function __construct()  # Constructor sets $isConnected, connects to db and creates params array
                    {
                        $this->isConnected = false;
                        $this->Connect();
                        $this->params=array();
                    }


                    private function Connect()  # Connect tries to connect 
                    {
                        if(MYSQL_SERVER=='localhost') $dsn='mysql:dbname='.MYSQL_DATABASE.';host=127.0.0.1';
                        else $dsn='mysql:dbname='.MYSQL_DATABASE.';host='.MYSQL_SERVER.'';
                        try
                            {
                                $this->pdo=new PDO($dsn,MYSQL_USERNAME,MYSQL_PASSWORD,array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
                                $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                                $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
                                $this->isConnected = true;
                            }
                        catch(PDOException $e) 
                            {
                                echo 'Unhandled Exception. <br />';
                                echo $e->getMessage();
                                die();
                            }
                    }


                    private function Init($query,$params = "") # Init tries to connect, prepare, parameterize, execute and reset params
                    {
                        if(!$this->isConnected) 
                            {
                                $this->Connect();
                            }
                        try
                            {
                                $this->stmt=$this->pdo->prepare($query);            
                                $this->bindMore($params);
                                if(!empty($this->params))
                                    {
                                        foreach($this->params as $param)
                                            {
                                                $params=explode("\x7F",$param);
                                                $this->stmt->bindParam($params[0],$params[1]);
                                            }
                                    }
                                $this->stmt->execute();
                            }
                        catch(PDOException $e)
                            {
                                echo 'Unhandled Exception. <br />';
                                echo $e->getMessage();
                                die();
                            }
                        $this->params=array();
                    }


                    public function bind($para,$value) # bind adds parameter to params array
                    {
                        $this->params[sizeof($this->params)] = ":" . $para . "\x7F" . utf8_encode($value);
                    }


                    public function bindMore($parray) # bind more
                    {
                        if(empty($this->params) && is_array($parray))
                            {
                                $columns = array_keys($parray);
                                foreach($columns as $i => &$column)
                                    {
                                        $this->bind($column, $parray[$column]);
                                    }
                            }
                    }


                    public function query($query,$params=null,$fetchmode=PDO::FETCH_ASSOC) # returns for SELECT and SHOW, returns number of affected rows for DELETE, INSERT and UPDATE
                    {
                        $query=trim($query);
                        $this->Init($query,$params);
                        $rawStatement=explode(" ", $query);
                        $statement=strtolower($rawStatement[0]);
                        if($statement==='select'||$statement==='show')
                            {
                                return $this->stmt->fetchAll($fetchmode);
                            }
                        elseif($statement==='insert'||$statement==='update'||$statement==='delete')
                            {
                                return $this->stmt->rowCount();
                            }
                        else
                            {
                                return NULL;
                            }
                    }


                    public function lastInsertId()
                    {
                        return $this->pdo->lastInsertId();
                    }
}