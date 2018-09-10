<?php


class DB
{
    private static $instance = null;

    //login credentials:
    private $servername = "localhost";
    private $username = "root";
    private $password = "root";
    private $database = "sjonframework";

    private $existingTables = [];

    private $c;

    private function __construct()
    {
        try {
            $this->c = new PDO("mysql:host=".$this->servername.";dbname=".$this->database, $this->username, $this->password);
            $this->c->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        catch (Exception $e){
            echo "<script>alert('configure your database settings in DB.php')</script>";
            exit();
        }
    }


    public static function getInstance()
    {

        if(self::$instance == null){
            self::$instance = new DB();
        }
        return self::$instance;
    }


    public function select($query)
    {
        $result = $this->c->query($query);
        return $result;
    }


    public static function prepare($query)
    {
        App::log("preparing: " . $query);
        return self::getInstance()->c->prepare($query);
    }


    public static function lastId()
    {
        return self::getInstance()->c->lastInsertId();
    }


    public static function create($class, $fields, $values)
    {
        if(count($fields) != count($values)){
            throw new Exception("Fields and Values don't have the same size");
        }
    }


    public static function close()
    {
        self::getInstance()->c = null;
        self::$instance = null;
    }


    public static function tableExists($name)
    {
        $table = strtolower($name);
        if(array_search($table, self::getInstance()->existingTables) !== false){
            return true;
        }

        $stmt = DB::prepare("SHOW TABLES LIKE '".$table."'");
        $stmt->execute();

        $exists = count($stmt->fetchAll()) > 0;
        if($exists){
            array_push(self::getInstance()->existingTables, $table);
        }

        return $exists;
    }


    public static function getBy(string $className, $field, $value, $orderBy = "", $order = 1)
    {
        $c = self::getInstance()->c;

        $class = new $className;

        $stmt = $c->prepare("SELECT * FROM `" . $class->table . "` WHERE `active` = 1 AND (`$field` = :$field)" . ($orderBy == "" ? "" : " ORDER BY `$orderBy` " . ($order == 1 ? "ASC" : "DESC")));

        $stmt->execute(array("$field" => $value));

        $result = $stmt->fetchAll(PDO::FETCH_CLASS, $className);
        return $result;
    }
}
