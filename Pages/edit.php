<?php

App::pageAuth(['user'], "login");

if (isset($_POST['email'])) {
    User::updateUser($_POST);
}
?>

<div class="container">
    <div class="card card-model card-model-sm">
        <div class="card-header">
            Update
        </div>
        <div class="card-body">
            <?= User::editUserForm(); ?>
        </div>
    </div>
</div>
