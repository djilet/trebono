<?php

define("IS_ADMIN", true);
require_once(dirname(__FILE__) . "/../include/init.php");

$post = new LocalObject($_POST);

$adminPage = new PopupPage();
$content = $adminPage->Load("forgot_password.html");

if ($post->GetProperty("Action") && $post->GetProperty("email")) {
    $user = new User();
    $user->LoadByEmail($post->GetProperty("email"));
    $success = false;

    if ($user->GetProperty("user_id")) {
        if ($user->GetProperty("archive") == "Y") {
            $user->AddError("reset-password-error-deactivated-user");
        } else {
            if ($user->SendPasswordToEmail()) {
                header("Location: " . ADMIN_PATH . "index.php?SendMail=Y");
                $success = true;
            }
        }
    }

    if (!$success) {
        $content->SetVar("email", $post->GetProperty("email"));
        $content->LoadErrorsFromObject($user);
    }
}

$adminPage->Output($content);
