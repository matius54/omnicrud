<?php
    class DB{
        private static ?DB $instance = null;
        
        private ?PDO $conn = null;

        private ?PDOStatement $result = null;

        static public function getInstance() : DB {
            if(self::$instance == null){
                self::$instance = new DB;
            }
            return self::$instance;
        }

        private function __construct(){
            try{
                $this->connect();
            }
            catch(PDOException $e){
                $this->showException($e,"No se ha podido establecer la conexion con la base de datos");
                die();
            }
        }

        public function __destruct(){
            $this->close();
        }

        private static function showException(
                PDOException $e,
                ?string $message = null,
                ?string $sql = null,
                ?array $args = null
            ) : void 
        {  
            if(!isset($_SESSION)) session_start();
            if(isset($_SESSION["db_error"])) return;
            $html = "";
            if($message)$html .= "<h1>$message.</h1>";
            switch($e->getCode()){
                case 2002:
                    $html .= "<h3>Revisa que el host este bien</h3>";
                break;
                case 1045:
                    $html .= "<h3>Revisa que el usuario y contraseña esten bien.</h3>";
                break;
            }
            if($sql)$html .= "<p>Solicitud SQL: $sql.</p>";
            if($args)$html .= "<p>Argumentos SQL: [".implode(", ",$args)."].</p>";
            $html .= "<p>Codigo de error: ".$e->getCode().".</p>";
            $html .= "<p>Mensaje de error: ".$e->getMessage().".</p>";
            $_SESSION["db_error"] = $html;
        }

        private function getBindType(mixed $param) : int {
            if(is_int($param)){
                return PDO::PARAM_INT;
            }else if(is_string($param)){
                return PDO::PARAM_STR;
            }else if(is_bool($param)){
                return PDO::PARAM_BOOL;
            }
            return PDO::PARAM_NULL;
        }

        public function connect() : void {
            if(!isset($_SESSION)) session_start();
            $connect = json_decode($_SESSION["connection"]);
            $host = $connect->host ?? "";
            $username = $connect->username ?? "";
            $password = $connect->password ?? "";
            $this->conn = new PDO("mysql:host=".$host.";charset=utf8;dbname=".$_SESSION["database"], $username, $password);
        }

        public static function connect_no_dbname() : PDO {
            if(!isset($_SESSION)) session_start();
            $connect = isset($_SESSION["connection"]) ? json_decode($_SESSION["connection"]) : [];
            $host = $connect->host ?? "";
            $username = $connect->username ?? "";
            $password = $connect->password ?? "";
            return new PDO("mysql:host=".$host.";charset=utf8", $username, $password);
        }
        public static function createDB(string $database) : void {
            try{
                $conn = self::connect_no_dbname();
                $conn->exec("DROP DATABASE IF EXISTS `$database`");
                $conn->exec("CREATE DATABASE IF NOT EXISTS `$database`");
                $conn = null;
            }catch(PDOException $e){
                self::showException($e,"Ha ocurrido un error creando la base de datos");
            }
        }
        public static function dropDB(string $database) : void {
            try{
                $conn = self::connect_no_dbname();
                $conn->exec("DROP DATABASE IF EXISTS `$database`");
                $conn = null;
            }catch(PDOException $e){
                self::showException($e,"Ha ocurrido un error eliminando la base de datos");
            }
        }
        public static function getDB() : array {
            $excluded = [
                "information_schema",
                "performance_schema",
                "mysql"
            ];
            //$excluded = [];
            try{
                $conn = self::connect_no_dbname();
                $prepared = $conn->prepare("SHOW DATABASES");
                $prepared->execute();
                $result = $prepared->fetchAll(PDO::FETCH_COLUMN);
                foreach ($result as $key => $value){
                    if(in_array($value,$excluded)) unset($result[$key]);
                }
                $result = array_values($result);
                return $result;
            }catch(PDOException $e){
                self::showException($e,"Ha ocurrido un error obteniendo las bases de datos");
                return [];
            }
        }
        public static function testDB() : bool {
            try{
                $conn = self::connect_no_dbname();
                $conn = null;
                return true;
            }catch(PDOException $e){
                self::showException($e,"Ha ocurrido un error creando la base de datos");
                return false;
            }
            return false;
        }

        private function createTables() : void {
            //$this->conn->exec("CREATE TABLE IF NOT EXISTS `$table` (".implode(",",$value).")");
        }

        public function close() : void {
            $this->conn = null;
        }

        public function execute(string $sql,array $arg=[]) : void {
            try{
                $prepared = $this->conn->prepare($sql);
                foreach ($arg as $index => $value) {
                    $prepared->bindValue($index + 1, $value, $this->getBindType($value));
                }
                $prepared->execute();
                $this->result = $prepared;
            }catch(PDOException $e){
                $this->showException($e,"Ha ocurrido un error ejecutando SQL",$sql,$arg);
            }
        }

        public function fetch(bool $htmlspecialchars = false) : Array {
            if($this->result === null) return [];
            $result = $this->result->fetch(PDO::FETCH_ASSOC);
            if(!is_array($result))$result = [];
            if($htmlspecialchars){
                foreach ($result as $key => $value) {
                    $result[$key] = htmlspecialchars($value);
                }
            }
            return $result;
        }

        public function fetchAll(bool $htmlspecialchars = false) : Array {
            if($this->result === null) return [];
            $data = $this->result->fetchAll(PDO::FETCH_ASSOC);
            if($htmlspecialchars && is_array($data)) {    
                foreach ($data as $index => $element) {
                    if(!is_array($data)) continue;
                    foreach ($element as $key => $value) {
                        $element[$key] = htmlspecialchars($value);
                    }
                    $data[$index] = $element;
                }
            }
            return $data;
        }

        public function rowCount() : int | null {
            if($this->result === null) return null;
            return $this->result->rowCount();
        }

        public function beginTransaction() : bool {
            return $this->conn->beginTransaction();
        }

        public function rollback() : bool {
            return $this->conn->rollback();
        }

        public function commit() : bool {
            return $this->conn->commit();
        }
    }
?>