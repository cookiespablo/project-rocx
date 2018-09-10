<?php

App::pageAuth(['user'], "login");

// Example to get user
$user = User::findById(1);

?>

