<?php
    class SC {
        private static function hexChar(){
            return dechex(mt_rand(0,15));
        }
        public static function randomHexStr(int $length = 32) : string {
            $arr = [];
            for ($i = 0; $i < $length; $i++) {
                $arr[$i] = self::hexChar();
            }
            return implode($arr);
        }
        public static function password(string $password,string &$hash,string &$salt) : string {
            if(!VALIDATE::password($password))return null;
            if($salt === null || !is_string($salt)){
                $salt = self::randomHexStr();
            }else{
                $salt = strtolower($salt);
            }
            $hash = hash('sha256',$password.$salt);
            return $hash;
        }
    }
    class TIMESTR {
        public static function now() : string {
            //return time().str_pad(floor(gettimeofday()["usec"]/1000),3,"0",STR_PAD_LEFT);
            $timeArray = gettimeofday();
            return sprintf("%d%03d", $timeArray["sec"], $timeArray["usec"] / 1000);
        }
        public static function getMiliseconds(string &$time, bool $integer=true,bool $override=true) : string {
            if(!VALIDATE::string($time))return null;
            $ms = substr($time, -3);
            $seconds = substr($time, 0, -3);
            if($override)$time = $seconds;
            if($integer)$ms = intval($ms);
            return $ms;
        }
        public static function getUnix(string &$time, bool $integer=true,bool $override=true){
            if(!VALIDATE::string($time))return null;
            $ms = substr($time, -3);
            $seconds = substr($time, 0, -3);
            if($override)$time = $ms;
            if($integer)$seconds = intval($seconds);
            return $seconds;
        }
        public static function merge(string $time,string $time2){
            if(!VALIDATE::string($time))$time = strval($time);
            if(!VALIDATE::string($time2))$time2 = strval($time2);
            if(strlen($time) == 3){
                return $time2.$time;
            }elseif(strlen($time2) == 3){
                return $time.$time2;
            }else{
                throw new Exception("valores invalidos");
            }
        }
    }
    class TIME {
        public static function now() : int {
            $timeArray = gettimeofday();
            return ($timeArray["sec"] * 1000) + (int) ($timeArray["usec"] / 1000);
        }
        public static function getMiliseconds($time){
            return $time % 1000;
        }
        public static function getUnix($time){
            return (int) $time / 1000;
        }
    }
    class VALIDATE {
        public static function string($value,$maxLength=1024,$minLength=0,$length=null) : bool {
            if(isset($value) && is_string($value)){
                $strlength = strlen($value);
                if($length == null){
                    return $strlength >= $minLength && $strlength <= $maxLength;
                }else{
                    return $strlength == $length;
                }
            }
            return false;
        }
        public static function int(&$value) : string {
            $regex = "/^\d+$/";
            if(isset($value) && preg_match($regex,$value)) $value = intval($value);
            return is_int($value);
        }
        public static function id(&$value) : string {
            return self::int($value) && $value > 0;
        }
        public static function float(&$value) : string {
            $regex_dot = "/^\d+(\.\d+)?$/";
            $regex_comma = "/^\d+(,\d+)?$/";
            if(preg_match($regex_comma, $value))$value = implode(".",explode(",",$value));
            return preg_match($regex_dot,$value);
        }
        public static function title($value,$minLength = 1,$maxLength = 255) : string {
            return self::string($value,$maxLength,$minLength);
        }
        public static function username($value,$minLength = 3,$maxLength = 256) : string {
            return self::string($value,$maxLength,$minLength);
        }
        public static function description($value,$minLength = 0,$maxLength = 65535) : string {
            return self::string($value,$maxLength,$minLength);
        }
        public static function password($value,$maxLength = PHP_INT_MAX) : string {
            return self::string($value,$maxLength);
        }
        public static function hash($value,$length = 64) : string {
            return self::string($value,null,null,$length) && ctype_xdigit($value);
        }
        public static function salt($value,$length = 32) : string {
            return self::string($value,null,null,$length) && ctype_xdigit($value);
        }
        public static function session($value,$length = 128) : string {
            return self::string($value,null,null,$length) && ctype_xdigit($value);
        }
        public static function email($value,$maxLength = 100) : string {
            if(self::string($value,$maxLength)){
                if(filter_var($value, FILTER_VALIDATE_EMAIL)) return true;
            }
            return false;
        }
        public static function toInt(string $value) : int | null {
            $intValue = intval($value);
            if($intValue == $value)
                return $intValue;

            return null;
        }
        public static function int2bool(string $value) : bool | null {
            $value = self::toInt($value);
            $boolTable = [1=>true,0=>false];
            if($value!=null){
                return $boolTable[$value];
            }
            return null;
        }
    }
    class HTML {
        static $table_header = "<table border=\"1\">";

        public static function matrix2table(array $matrix,array $columnsNames=[]) : string {
            $result = self::$table_header;
            if(!empty($columnsNames))$result .= "<thead>".self::array2table($columnsNames,false)."</thead>";
            $result .= "<tbody>";
            foreach ($matrix as $value) {
                $result .= self::array2table($value,false);
            }
            $result .= "</tbody></table>";
            return $result;
        }
        
        public static function array2table(array $array,bool $includeTable = true) : string {
            if($includeTable){
                return self::$table_header.self::array2table($array,false)."</tbody></table>";
            }else{
                return "<tr><th>".implode("</th><th>",$array)."</th></tr>";
            }
        }

        public static function array2list(array $array, string $name = "select", ?string $title = null) : string {
            $result = "<select name=\"$name\">";
            if($title !== null and is_string($title))
                $result .= "<option value=\"\">--- $title ---</option>";
            foreach ($array as $value => $dname) {
                $result .= "<option value=\"".(is_int($value) ? $dname : $value)."\">$dname</option>";
            }
            $result .= "</select>";
            return $result;
        }

        public static function input(
            string $label = "input", 
            string $type = "text", 
            string $name = "input", 
            string $value = null, 
            string $placeholder = null
        ) : string {
            if($value === null) $value = "";
            if($placeholder === null) $placeholder = $value;
            return "<label>$label: <input name=\"$name\" type=\"$type\" value=\"$value\" placeholder=\"$placeholder\"></label>";
        }
    }
    class JSON {
        public static function isJson() : bool {
            if($_SERVER["REQUEST_METHOD"] === "POST"){
                $contentType = $_SERVER["CONTENT_TYPE"];
                if($contentType && str_contains($contentType,"application/json")){
                    return true;
                }
            }
            return false;
        }
        public static function getJson() : array {
            if (self::isJson()) {
                $data = file_get_contents("php://input");
                $json = json_decode($data, true);
                return $json;
            }
            return [];
        }
        public static function sendJson($response){
            $json = json_encode($response);
            header("Content-Type: application/json; charset=utf-8");
            echo $json;
            die();
        }
    }

    class URL {
        public static function baseURL() : String {
            return (empty($_SERVER['HTTPS']) ? 'http' : 'https') . "://$_SERVER[HTTP_HOST]" . self::baseURI();
        }
        public static function baseURI($base = null) : String {
            if($base === null)$base = $_SERVER['REQUEST_URI'];
            $url = explode("?", $base)[0];
            return $url;
        }
        public static function URI() : String {
            return $_SERVER['REQUEST_URI'];
        }
        public static function query($args = null, $keep = null, $unset = null, $ref = null) : String {
            $array = [];
            if($args === null) $args = [];
            if($keep === null) $keep = true;
            if($unset === null) $unset = [];
            if($ref === null) $ref = false;
            if($keep) $array = self::decodeAll();
            foreach ($args as $key => $value) {
                $array[$key] = $value;
            }
            if($ref){
                if(!is_string($ref))$ref = "ref";
                $array[$ref] = URL::URIquery(unset: [$ref]);
            }
            $indx = 0;
            $query = [];
            foreach ($array as $key => $value) {
                $key = urlencode($key);
                $value = urlencode($value);
                if(!in_array($key,$unset))$query[$indx] = "$key=$value";
                $indx ++;
            }
            $ext = "";
            if(!empty($query))$ext = "?".join("&",$query);
            return $ext;
        }
        public static function URIquery($args = null, $keep = null, $unset = null, $ref = null) : String {
            return self::baseURI().self::query($args,$keep,$unset,$ref);
        }
        public static function decodeAll($array = null) : Array {
            $get = [];
            if($array == null)$array = $_GET;
            foreach ($array as $key => $value) {
                $key = urldecode($key);
                $value = urldecode($value);
                $get[$key] = $value;
            }
            return $get;
        }
        public static function decode($key, $array = null) {
            if($array == null)$array = $_GET;
            if(isset($array[$key])){
                return urldecode($array[$key]);
            }
            return null;
        }
        public static function ref(){
            return self::decode("HTTP_REFERER",$_SERVER);
        }
        public static function redirect($link){
            header("Location: $link");
            die();
        }
    } 
?>