<?php

/*

======================================================
================== Template for models ===============
======================================================

class <class name> extends Model
{
    // Define the relations here
    static protected $belongsTo = [];
    static protected $hasMany = [];

    // Define the properties here. Make them 'protected'

    public function __construct()
    {

    }

    // This method is called after filling the model with the values from the form and
    // before saving it to the database. You can add your own adjustments and checks here.
    // If a model shouldn't be saved, simply return false. Else return nothing, or true. Whatever.
    protected static function newModel($obj) {

    }
}

*/

/**
 * Created by PhpStorm.
 * User: sjon
 * Date: 29-3-17
 * Time: 20:09
 */
abstract class Model
{
    /**
     * @Type int
     */
    protected $id;

    /**
     * @@Type timestamp
     */
    protected $created_at;

    /**
     * @@Type timestamp
     */
    protected $updated_at;


    public function __get($name)
    {
        return @$this->{$name};
    }


    public function save()
    {
        if($this->id) {

            $this->update();
        }
        else{
            self::createIfNotThere();
            $this->create();
        }
    }


    private static function getDbProps()
    {
        $r = new ReflectionClass(get_called_class());
        $props = $r->getProperties();
        $dbProps = [];
        foreach ($props as $prop) {
            if ($prop->isPublic() ||
                $prop->isStatic() ||
                $prop->isPrivate() ||
                $prop->getName() == 'id' ||
                $prop->getName() == 'created_at' ||
                $prop->getName() == 'updated_at'
            ) {

                continue;
            }

            $doc = $prop->getDocComment();

            $type = explode("\n", explode("@Type ", $doc)[1])[0];

            if($type) {

                $isArray = strpos($type, "array") !== false;

                if ($isArray) continue;

                array_push($dbProps, $prop);
            }

        }
        return $dbProps;

    }


    private function create()
    {
        self::createIfNotThere();
        $props = self::getDbProps();
        //$vars = get_object_vars($this);
        $vars = [];
        foreach ($props as $prop) {
            $prop->setAccessible(true);
            $vars[$prop->getName()] = ($prop->getValue($this) !== '' ? $prop->getValue($this) : null);
            if($prop->getValue($this) !== '') {
                settype($vars[$prop->getName()], self::getPhpType($prop));
            }
        }
        unset($vars["id"]);

        $table = self::table();

        $fields = "(`" . implode("`, `", array_keys($vars)) . "`)";
        $values = "(:" . implode(", :", array_keys($vars)) . ")";

        $qry = "INSERT INTO `$table` $fields VALUES $values;";



        $stmt = DB::prepare($qry);

        try {
            $stmt->execute($vars);
            $this->id = DB::lastId();
        }
        catch(Exception $e) {
            echo "Could not execute query: <br>" . $stmt->queryString . "<br><br>";
            var_dump($vars);
            echo "<br>" . $e->getFile() . ": " . $e->getLine() . "<br>message: " . $e->getMessage();
            die();
        }
    }


    private function update($exclude = [])
    {
        array_push($exclude, 'table');

        $vars = get_object_vars($this);

        foreach($exclude as $key) {
            unset($vars[$key]);
        }

        $table = self::table();

        $qry = "UPDATE `$table` SET ";

        foreach($vars as $col => $value) {
            if($col == 'id') continue;
            $qry .= "`$col` = :$col, ";
        }

        $qry = substr($qry, 0, -2);

        $qry .= " WHERE `id` = :id";

        $stmt = DB::prepare($qry);
        try {
            $stmt->execute($vars);
        }
        catch(Exception $e) {
            echo "Could not execute query: <br>" . $stmt->queryString . "<br><br>";
            echo $e->getFile() . ": " . $e->getLine() . "<br>message: " . $e->getMessage();
        }
    }


    private function delete()
    {
        $this->active = 0;
        $this->save();
    }


    public function terminate()
    {
        $table = self::table();

        $qry = "DELETE FROM `$table` WHERE `id` = :id";

        $stmt = DB::prepare($qry);

        $stmt->execute(['id' => $this->getId()]);
    }


    public static function get()
    {
        self::createIfNotThere();

        $table = self::table();

        $qry = "SELECT * FROM `$table`";

        $stmt = DB::prepare($qry);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_CLASS, get_called_class());
    }


    public static function updateValues($form)
    {
        $obj = self::findById($form['id']);

        unset($form['id']);

        foreach ($form as $name => $value) {
            $obj->$name = $value;
        }

        $obj->save();
    }


    protected function belongsTo($type, $field = "")
    {
        if($field == "") {
            $field = strtolower($type);
        }
        return $type::findById(get_object_vars($this)[$field]);
    }


    protected function hasMany($type, $field = "", $orderBy = "", $order = 1)
    {
        if($field == "") {
            $field = strtolower(get_called_class());
        }
        return $type::findBy($field, $this->id, $orderBy, $order);
    }


    public static function printTable($models, $exclude = [], $hide = [],$maxSize = 20, $edit = false, $add = false, $delete = false)
    {
        self::createIfNotThere();
        $extra = [];
        array_push($exclude, 'created_at');
        array_push($exclude, 'updated_at');
        if($edit || $delete) array_push($extra, '');

        echo "<form method='post'><table>";
        echo self::tableHeader(array_merge($exclude, array_keys($hide)), $extra);
        if($add) echo self::tableRowEmpty($exclude, $hide);

        $start = isset($_GET['start']) ? $_GET['start'] : 0;
        $end = min($start + $maxSize, count($models));

        for($i = $start; $i < $end; $i++) {
            echo $models[$i]->tableRow($exclude, $hide, $edit, $delete);
        }

        echo "</table></form>";
        //TODO: refine this button. Make a real navigator
        if($end < count($models)) {
            echo "<a href='?page=" . $_GET['page'] . "&start=$end' class='button'>Next</a>";
        }
    }


    public static function tableHeader($exclude, $extra = [])
    {
        array_push($exclude, 'id');
        $vars = get_class_vars(get_called_class());
        $html = "<tr>";

            foreach ($vars as $key => $value) {
                if(array_search($key, $exclude) !== false) continue;
                $keyArr = str_split($key);
                $col = "";
                foreach ($keyArr as $letter) {
                    if(ctype_upper($letter)) $col .= " ";
                    $col .= strtolower($letter);
                }
                $html .= "<th>$col</th>";
            }
            foreach ($extra as $col) {
                $html .= "<th>$col</th>";
            }

        $html .= "</tr>";
        return $html;
    }


    public function tableRow($exclude, $hide, $edit = false, $delete = false)
    {
        array_push($exclude, 'id');
        $vars = get_object_vars($this);
        $html = "<tr>";
        foreach ($vars as $key => $value) {
            if(array_search($key, $exclude) !== false) continue;
            if(array_search($key, array_keys($hide)) !== false) {
                $html .= "<input type='hidden' size='' name='$this->id-$key' value='$value'>";
            }
            else if($edit) {
                $html .= "<td><input type='text' size='' name='$this->id-$key' value='$value'></td>";
            }
            else{
                $html .= "<td>$value</td>";
            }
        }
        if($edit || $delete) {
            $html .= '<td width="' . ($edit && $delete ? 48 : 20) . '"">';
            if($edit)   $html .= '<button type="submit" name="save" value="' . $this->id .'"><i class="fa fa-save fa-lg clickable"></i></button>';
            if($edit && $delete) $html .= '&nbsp;&nbsp;';
            if($delete) $html .= '<button type="submit" name="del" value="' . $this->id .'"><i class="fa fa-ban fa-lg clickable"></i></button>';
            $html .= "</td>";
        }
        $html .= "</tr>";
        return $html;
    }


    public static function tableRowEmpty($exclude, $hide)
    {
        array_push($exclude, 'id');
        $vars = get_class_vars(get_called_class());
        $html = "<tr>";
        foreach ($vars as $key => $value) {
            if(array_search($key, $exclude) !== false) continue;
            if(array_search($key, array_keys($hide)) !== false) {
                $html .= "<input type='hidden' size='' name='0-$key' value='$hide[$key]'>";
            }
            else {
                $html .= "<td><input type='text' size='' name='0-$key' value='$value'></td>";
            }
        }
        $html .= '<td><button type="submit" name="save" value="0"><i class="fa fa-save fa-lg clickable"></i></button></td>';
        $html .= "</tr>";
        return $html;
    }


    public static function saveTableRow($form, $refresh = true)
    {
        if(isset($form['save'])) { //Save the row
            $id = $form['save'];
            if ($id == "0") {
                $class = get_called_class();
                $obj = new $class();
                foreach ($form as $key => $value) {
                    if(explode("-", $key)[0] != $id) {
                        continue;
                    }
                    $k = explode("-", $key)[1];
                    $obj->$k = $value;
                }
                if (static::newModel($obj) !== false) {
                    $obj->save();
                    if($refresh) App::refresh();
                    else return $obj;
                } else {
                    App::addError("Did not save the new $class to the database");
                }
            } else {
                $f = ["id" => $id];
                foreach ($form as $key => $value) {
                    if(explode("-", $key)[0] == $id) {
                        $f[explode("-", $key)[1]] = $value;
                    }
                }
                self::updateValues($f);
                if($refresh) App::refresh();
            }
        }
        else if(isset($form['del'])) { //Delete the row
            User::findById($form['del'])->delete();
        }
        else {
            //Old style

            if (isset($form['id'])) {
                self::updateValues($form);
                if($refresh) App::refresh();
            } elseif (count($form) > 0) {
                $class = get_called_class();
                $obj = new $class();
                foreach ($form as $key => $value) {
                    $obj->$key = $value;
                }
                if (static::newModel($obj) !== false) {
                    $obj->save();
                    if($refresh) App::refresh();
                    else return $obj;
                } else {
                    App::addError("Did not save the new $class to the database");
                }
            }
        }
    }


    private static function getPhpType($prop)
    {
        $doc = $prop->getDocComment();

        $type = explode("(", explode("\n", explode("@Type ", $doc)[1])[0])[0];


        $translate = [
            "int" => "integer",
            "decimal" => "double",
            "varchar" => "string",
            "boolean" => "integer",
            "date" => "string",
            "timestamp" => "string"];


        if(array_search($type, array_keys($translate)) === false) {

            return "integer";
        }

        return $translate[$type];
    }


    private static function createTable()
    {
        $table = self::table();

        $query = "CREATE TABLE `". $table . "` (";
        $query .= "id int AUTO_INCREMENT, ";

        $foreign = "";

        $otherTables = [];

        $r = new ReflectionClass(get_called_class());
        $props = $r->getProperties();
        $defaults = $r->getDefaultProperties();

        // Search all docblocks from values that are protected
        foreach ($props as $prop) {
            if( $prop->isPublic() ||
                $prop->isStatic() ||
                $prop->isPrivate() ||
                $prop->getName() == 'id' ||
                $prop->getName() == 'created_at' ||
                $prop->getName() == 'updated_at') {

                continue;
            }
            try {

                $name = $prop->getName();

                $type = self::getFieldProperties($prop, "Type")[0];

                // skip the protected values that do not have a docblock or type
                if($type) {
                    $isArray = strpos($type, "array") !== false;
                    $isModel = strtolower($type) !== $type && !$isArray;

                    $canBeNull = $defaults[$name] == "";

                    if($isModel) {

                        array_push($otherTables, $type);

                        $query .= "`" . $name . "` int" . ($canBeNull ? " NULL, " : " NOT NULL, ");

                        $foreign .= "ALTER TABLE `" . $table . "` ADD CONSTRAINT `FK_ " . $table . "_" . strtolower($type) . "` FOREIGN KEY (`" . $name . "`) REFERENCES `" . strtolower($type) . "`(`id`) ON UPDATE RESTRICT ON DELETE RESTRICT; ";

                    } else if(!$isArray) {
                        $query .= "`" . $name . "` " . $type . ($defaults[$name] != null ? " DEFAULT $defaults[$name]" : "") . ", ";
                    }
                }

            } catch (Exception $err) {
                continue;
            }
        }

        $query .= "`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, ";
        $query .= "`updated_at` DATETIME ON UPDATE CURRENT_TIMESTAMP, ";

        $query .= "PRIMARY KEY (`id`))";

        // Create table
        $stmt = DB::prepare($query);
        $stmt->execute();

        // Create needed tables for foreign keys
        foreach ($otherTables as $otherTable) {
            if (!DB::tableExists($otherTable)) {
                $otherTable::createTable();
            }
        }

        // Create foreign keys (I'm a genius)
        if($foreign) {
            $stmt = DB::prepare($foreign);
            $stmt->execute();
        }
    }


    private static function getFieldProperties($field, $property)
    {

        $doc = $field->getDocComment();

        if($doc) {
            $type = @explode(" ", trim(explode("\n", explode("@$property ", $doc)[1])[0]));
            return $type;
        }
        return false;
    }


    private static function createIfNotThere()
    {
        if(!DB::tableExists(self::table())) {
            self::createTable();
        }
    }


    protected abstract static function newModel($obj);


    public function getId()
    {
        return $this->id;
    }


    public static function findBy($field, $value, $orderBy = "", $order = 1)
    {
        self::createIfNotThere();

        return DB::getBy(get_called_class(), $field, $value, $orderBy, $order);
    }


    public static function findById($id)
    {
        $user = self::findBy("id", $id);
        if($user) {
            return $user[0];
        }
        return App::redirect('404');

    }


    private static function table()
    {
        $className = get_called_class();
        return (new $className)->table;
    }
}
