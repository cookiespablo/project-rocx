<?php

/**
 * Created by PhpStorm.
 * User: sjon
 * Date: 19-4-17
 * Time: 14:17
 */
class FormField
{
    private $type;
    private $name;
    private $placeholder;
    private $value;
    private $required;

    public function __construct($name, $type = "text", $placeholder = "", $value = "")
    {
        $this->name = $name;
        $this->type = $type;
        $this->placeholder = $placeholder;
        $this->value = $value;
    }


    public function name($name)
    {
        $this->name = $name;
        return $this;
    }


    public function type($type)
    {
        $this->type = $type;
        return $this;
    }


    public function placeholder($placeholder)
    {
        $this->placeholder = $placeholder;
        return $this;
    }


    public function value($value)
    {
        $this->value = $value;
        return $this;
    }


    public function required()
    {
        $this->required = true;
        return $this;
    }


    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }


    public function getInput($first)
    {
        $html = "<";

        switch($this->type) {
            case "select":
            case "textarea":
                $html .= $this->type . ' class="form-control" ';
                break;
            case "file":
                $html .= 'input class="form-control-file" type="file"';
            default:
                $html .= 'input class="form-control" type="'.$this->type.'"';
                break;
        }

        $html .= "name='$this->name' ";
        $html .= "id='$this->name' ";

        if($this->required) {
            $html .= "required ";
        }

        switch ($this->type) {
            case "select":
                $html.= ">";
                foreach ($this->value as $key => $val){
                    $html .= "<option value='$key'>$val</option>";
                }
                break;
            case "textarea":
                $html .= ">";
                if ($this->value) $html .= $this->value;
                break;
            default:
                if($this->value) $html .= "value='$this->value' ";

                break;
        }

        switch ($this->type) {
            case "select":
            case "textarea":
                $html .= "</" . $this->type;
                break;
            default:
                break;
        }

        $html .= ">";

        if($this->type == 'file' && $this->value) {
            $html .= '<img width="200" style="margin-top:10px;" src="'.Http::$webroot.'images/'.$this->value.'">';
        }

        // if($this->type == "file") {
        //     $html .= '<';
        // }
        return $html;
    }


    public function getHtml($first)
    {
        if($this->type != 'file') {
            $html = '<div class="form-group">
                        <label for="'.$this->name.'">'.$this->placeholder.'</label>
                        '.$this->getInput($first).'
                    </div>';
        }
        else {
            $html = '<div class="form-group">
                <label for="'.$this->name.'">'.$this->placeholder.'</label>
                '.$this->getInput($first).'
            </div>';
        }

        // $html  = "<div class='input filled" . ($this->type == "file" ? " noValue" : "") . "'>";

        // $html .= "<span>$this->placeholder</span>";

        // $html .= $this->getInput($first);

        // $html .= "</div>";

        return $html;
    }
}
