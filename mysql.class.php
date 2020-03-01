<?php
    /**
     * Simple Mysql class using PDO
     * 
     * @package LMSQL
     * @author Arash Soleimani <arash@leomoon.com>
     * @link http://arashsoleimani.com
     * @license https://opensource.org/licenses/MIT - The MIT License
     * @version 1.0
     * 
     */
    class LMSQL {
        private $connectionInfo = array();
        private $mypdo = null;
        public $sqlQuery = "";
        public $errorMessage = null;

        /**
         * DB Constructor
         * 
         * @param string $host
         * @param string $user
         * @param string $db
         * @param bool $autoConnect
         * @param string $charset
         * 
         * @example 
         * <code>
         * $mysql = new LMSQL('localhost', 'root', '123456', 'mydatabase', true);
         * </code>
         * 
         */
        function __construct($host, $user, $pass, $db, $autoConnect=false, $charset = 'utf8') {
            $this->connectionInfo = array('host'=>$host, 'user'=>$user, 'pass'=>$pass, 'db'=>$db, 'charset'=>$charset);
            if($autoConnect){
                $this->connect();
            }
        }
        /**
         * Connect to MySQL
         */
        public function connect(){
            try {
                $this->mypdo = new PDO("mysql:host=".$this->connectionInfo['host'].";dbname=".$this->connectionInfo['db'].";charset=".$this->connectionInfo['charset'], $this->connectionInfo['user'], $this->connectionInfo['pass']);
                $this->mypdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                $this->errorMessage = "Connection failed: " . $e->getMessage();
            }
        }
        
        /**
         * Get data from table with where clause, limit, order and custom index
         * 
         * @param array $queryInfo (
         *                 'table'=>'Table Name',
         *                 'fields'=>'fields', // - (optional)
         *                 'where'=>['id' => 1, 'name'=>'Arash'], // or "id = 1 AND name = 'Arash'" - (optional)
         *                 'order'=>'id DESC', // - (optional)
         *                 'limit'=>'10', // - (optional)
         *                 'index'=>['column'=>'type', 'multi'=>true], // - (optional)
         *                 'sql'=>'SELECT * FROM news' // - (optional)
         *                  );
         * 
         * @return array
         */
        public function select($queryInfo){
            $sql = $this->buildQuery('select', $queryInfo);
            $query = $this->query($sql);
            $result = $query->fetchAll();
            if($result && isset($queryInfo['index'])){
                foreach($result as $value){
                    if(isset($queryInfo['index']['multi'])){
                        $data[$value[$queryInfo['index']['column']]][] = $value;
                    }else{
                        $data[$value[$queryInfo['index']['column']]] = $value;
                    }
                }
                return $data;
            }
            return $result;
            
        }

        /**
         * select function alias
         */ 
        public function get($queryInfo){ return $this->select($queryInfo);}

        /**
         * Get one row from table
         * 
         * @param array $queryInfo
         * 
         * @return array
         */
        public function load($queryInfo){
            $sql = $this->buildQuery('select', $queryInfo);
            $results = $this->query($sql);
            return $results->fetch();
        }
        
        /**
         * Insert data to table
         * 
         * @param array $queryInfo (
         *                 'table'=>'Table Name',
         *                 'values'=>['name' => 'arash', 'company'=>'leomoon']
         *                  );
         * 
         * @return int 1 or null
         */
        public function insert($queryInfo){
            $sql = $this->buildQuery('insert', $queryInfo);
            return $this->exec($sql);
            
        }

        /**
         * Update rows
         * 
         * @param array $queryInfo (
         *                 'table'=>'Table Name',
         *                 'where'=>['IQ' => 0], // - (optional)
         *                 'values'=>['name' => 'trump', 'role'=>'president']
         *                  );
         * 
         * @return int 1 or null
         */
        public function update($queryInfo){
            $sql = $this->buildQuery('update', $queryInfo);
            return $this->exec($sql);
            
        }

        /**
         * Delete rows
         * 
         * @param array $queryInfo (
         *                 'table'=>'Table Name',
         *                 'where'=>['status' => 0], // - (optional)
         *                 'limit'=> 1 // - (optional)
         *                  );
         * 
         * @return int 1 or null
         */
        public function delete($queryInfo){
            $sql = $this->buildQuery('delete', $queryInfo);
            return $this->exec($sql);
            
        }

        /**
         * Simple search
         * @param array $queryInfo (
         *                 'table'=>'Table Name',
         *                 'searchs'=>['title', 'body'], // or search in all fields - (optional)
         *                 'word'=>'%YourSearchWord%', // %word% or %word or word%
         *                 'where'=>['category' => 'news'], // - (optional)
         *                  );
         * 
         * @return array
         */
        public function search($queryInfo){
            $sql = $this->buildQuery('search', $queryInfo);
            $query = $this->query($sql);
            return $query->fetchAll();
        }
         
        /**
         * PDO prepare and execute
         * 
         */
        public function query($sql){
            $stmt = $this->mypdo->prepare($sql);
            $stmt->execute();

            if(!$stmt){
                $error = $this->mypdo->errorInfo();
                if(!is_null($error[2])){
                    $this->errorMessage = $error[2];
                }
            }
            
            return $stmt;
        }

        /**
         * PDO exec - run your custom query
         * 
         * @param string $sql
         */
        public function exec($sql){
            $this->mypdo->exec($sql);
            $error = $this->mypdo->errorInfo();
            if(!is_null($error[2])){
                $this->errorMessage = $error[2];
            }
            
        }

        /**
         * Get total rows from table
         * 
         * @param array $queryInfo
         * 
         * @return int
         */
        public function count($queryInfo){
            $sql = $this->buildQuery('count', $queryInfo);
            return $this->mypdo->query($sql)->fetchColumn(); 
        }
        
        /**
         * count function alias
         */
        public function total($queryInfo){
            return $this->count($queryInfo);
        }

        /**
         * Get the last inserted id
         * 
         */
        public function insertId(){
            return $this->mypdo->lastInsertId();
        }

        /**
         * Show tables and columns
         * 
         * @param array $queryInfo
         */
        public function schema($queryInfo=null){
            if(isset($queryInfo['table'])){
                $sql = "DESCRIBE ".$queryInfo['table'];
            }else{
                $sql = "SHOW TABLES";
            }
            $data = $this->query($sql);
            return $data->fetchAll();
        }

        /**
         * Build sql query for select, update, insert, delete, search
         * 
         * @param string $action
         * @param array $queryInfo
         * 
         * @return string
         */
        private function buildQuery($action, $queryInfo){
            if(in_array($action, ['select', 'search'])){
                $query = "SELECT ";
                if(isset($queryInfo['fields'])){
                    $query .= $queryInfo['fields'];
                }else{
                    $query .= "*";
                }
                $query .= " FROM ".$queryInfo['table'];
            }
            if($action == 'update'){
                $query = "UPDATE ".$queryInfo['table']." SET ".$this->buildWhereQuery($queryInfo['values']);
            }
            if($action == 'delete'){
                $query = "DELETE FROM ".$queryInfo['table'];
            }
            if($action == 'insert'){
                $query = "INSERT INTO ".$queryInfo['table']." SET ".$this->buildWhereQuery($queryInfo['values']);
            }
            if($action == 'count'){
                $query = "SELECT COUNT(1) FROM ".$queryInfo['table'];
            }
            if(isset($queryInfo['where'])){
                if(is_array($queryInfo['where'])){
                    $query .= " WHERE ". $this->buildWhereQuery($queryInfo['where']);
                }else{
                    $query .= " WHERE ".$queryInfo['where'];
                }
            }
            if(isset($queryInfo['word'])){
                $query .= $this->buildSearchQuery($queryInfo);
            }
            if(isset($queryInfo['order'])){
                $query .= " ORDER BY ".$queryInfo['order'];
            }
            if(isset($queryInfo['limit'])){
                $query .= " LIMIT ".$queryInfo['limit'];
            }
            if(isset($queryInfo['sql'])){
                $query = $queryInfo['sql'];
            }
            $this->sqlQuery = $query;
            return $query;
        }

        /**
         * Build WHERE query (key-value)
         * @param array
         * 
         * @return string
         */
        private function buildWhereQuery($where){
            $query = "";
            if($where){
                $comma = "";
                foreach ($where as $key=>$value) {
                        $query.="$comma `$key`='$value' ";
                    $comma=' AND ';
                }
            }
            return $query;
        }

        /**
         * Build search query
         * @param array
         * 
         * @return string
         */
        private function buildSearchQuery($queryInfo){
            if(isset($queryInfo['where'])){
                $sql = " AND ";
            }else{
                $sql = " WHERE ";
            }
            if(!isset($queryInfo['searchs'])){
                $tableFields = $this->schema(['table'=>$queryInfo['table']]);
                foreach($tableFields as $field){
                    $queryInfo['searchs'][] = $field['Field'];
                }
            }
            $comma = "";
            $keyword = $queryInfo['word'];
            foreach($queryInfo['searchs'] as $column){
                $sql .= "$comma `$column` LIKE '$keyword'";
                $comma=" OR ";
            }
            return $sql;
        }

        /**
         * Show errors
         */
        public function error(){
            echo $this->errorMessage."\r\n";
        }
          
    }
?>