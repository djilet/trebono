<?php

define("IS_ADMIN", true);
require_once(dirname(__FILE__) . "/../include/init.php");

$user = new User();
$request = new LocalObject(array_merge($_POST, $_GET));

if ($request->GetProperty("Logout")) {
    $user->Logout();
    $adminPage = new PopupPage();
    $content = $adminPage->Load("login.html");
    $content->LoadMessagesFromObject($user);
    $adminPage->Output($content);
} else {
    if ($request->GetProperty("access_token")) {
        $user->Logout();
    }

    if (
        ($request->GetProperty("access_token")
            && $user->LoadByAccessToken($request->GetProperty("access_token"))
        ) || $user->LoadBySession()
    ) {
        $adminMenu = AdminPage::GetAdminMenu([]);
        if (!empty($adminMenu)) {
            if ($request->GetProperty("ReturnPath")) {
                header("Location: " . $request->GetProperty("ReturnPath"));
            } else {
                header("Location: " . $adminMenu[0]["Link"]);
            }
            exit();
        }
    }

    if ($request->GetProperty("email")) {
        if ($user->LoadByRequest($request)) {
            if ($user->GetProperty("archive") == "Y") {
                $user->Logout(true);
                $user->AddError("login-denied");
            } else {
                $adminMenu = AdminPage::GetAdminMenu([]);
                if (!empty($adminMenu)) {
                    if ($request->GetProperty("ReturnPath")) {
                        header("Location: " . $request->GetProperty("ReturnPath"));
                    } else {
                        header("Location: " . $adminMenu[0]["Link"]);
                    }
                    exit();
                }
            }
        }
    }

    $adminPage = new PopupPage();
    $content = $adminPage->Load("login.html");

    $session =& GetSession();
    if ($session->IsPropertySet("MustRelogin")) {
        $session->RemoveProperty("MustRelogin");
        $session->SaveToDB();
    }
    if ($session->GetProperty("LoginAttempts") >= 3) {
        $content->SetVar("NoMoreAtempts", true);
        if (!$request->IsPropertySet("email") || $request->GetProperty("email") == "") {
            $user->AddError("no-more-login-attempts");
        }
    }
    if ($request->GetProperty("SendMail")) {
        $user->AddMessage("password-is-changed-and-sent");
    }

    $content->LoadMessagesFromObject($user);
    $content->LoadErrorsFromObject($user);
    $content->LoadFromObject($request, ["ReturnPath", "RememberMe", "email"]);

    $adminPage->Output($content);
}
