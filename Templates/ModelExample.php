<?php

class ClassName extends Model
{
    // Set the table name
    protected $table = '';

    // Define the relations here
    static protected $belongsTo = [];
    static protected $hasMany = [];

    // Define the properties here. Make them 'protected' and add a docblock

    /**
     * @Type varchar(255)
     */
    protected $example;


    // This method is called after filling the model with the values from the form and
    // before saving it to the database. You can add your own adjustments and checks here.
    // If a model shouldn't be saved, simply return false. Else return nothing, or true. Whatever.
    protected static function newModel($obj) {

    }


    public function __construct(){

    }
}
