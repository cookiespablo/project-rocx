<?php

App::pageAuth([App::ROLE_GUEST]);

if (isset($_POST['email'])) {

    $user = User::register($_POST);

    if ($user) {
        App::redirect('home');
    }
}
?>

<div class="container">
    <div class="card card-model card-model-sm">
        <div class="card-header">
            Register
        </div>
        <div class="card-body">
            <?= User::registerForm(); ?>
        </div>
    </div>
</div>
