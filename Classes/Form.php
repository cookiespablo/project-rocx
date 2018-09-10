<?php

/**
 * Created by PhpStorm.
 * User: sjon
 * Date: 19-4-17
 * Time: 14:14
 */
class Form
{
    private $fields = [];
    private $action;
    private $method;
    private $hasFileUpload = false;

    public function __construct($action = null, $method = "POST")
    {
        $this->action = $action;
        $this->method = $method;
    }


    public function addField($field)
    {
        if($field->getType() === "file"){
            $this->hasFileUpload = true;
        }
        array_push($this->fields, $field);
    }


    public function getHTML()
    {
        $html = "<form method='$this->method'";

        if($this->action) {
            $html .= " action='$this->action'";
        }

        if($this->hasFileUpload) {
            $html .= " enctype='multipart/form-data'";
        }
        $html .= ">";
        $first = true;

        foreach ($this->fields as $field){
            $html .= $field->getHTML($first);
            $first = false;
        }

        $html .= '<input type="submit" class="btn btn-primary" value="submit">';

        $html .= "</form>";

        return $html;
    }
}
