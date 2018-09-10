<?php

App::pageAuth([App::ROLE_GUEST]);

if (isset($_POST['email'])) {

    $user = User::login($_POST);

    if ($user) {
        App::redirect('home');
    }
}
?>
<div class="container">
    <div class="card card-model card-model-sm">
        <div class="card-header">
            Login
        </div>
        <div class="card-body">
            <?= User::loginForm(); ?>
        </div>
    </div>
</div>
