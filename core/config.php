<?php
session_start();

//Include models
spl_autoload_register(function ($class_name) {
    if(file_exists('../Models/' . $class_name . '.php')) {
        include '../Models/' . $class_name . '.php';
    }
    elseif(file_exists('../Classes/' . $class_name . '.php')) {
        include '../Classes/' . $class_name . '.php';
    }
    else {
        dd('Class '.$class_name.' not found');
    }
});

Http::boot();


if(isset($_GET['page'])) {
    $page = $_GET['page'];
    if(!file_exists("../Pages/$page.php")){
        App::redirect('home');
    }
}
else {
    App::redirect('home');
}

// load all the base functions
function dd($text)
{
    if(is_array($text) || is_object($text)) {
        var_dump($text);
        die();
    }
    else {
        echo $text;
        die();
    }
}
