<?php

require_once(dirname(__FILE__) . "/include/init.php");

class ApiCoreProcessor extends ApiProcessor
{
    public function Process($method, $path, LocalObject $request, ApiResponse $response)
    {
        if (count($path) == 1 && $path[0] == "devices") {
            if ($method == "POST") {
                if ($request->ValidateNotEmpty("auth_device_id")) {
                    $device = new Device();
                    $privateKey = $device->Register($request->GetProperty("auth_device_id"), "web");
                    if ($privateKey) {
                        $response->SetStatus(ApiResponse::STATUS_OK);
                        $response->SetCode(201);
                        $response->SetData(array(
                            "private_key" => $privateKey
                        ));
                        return true;
                    }
                }
            }
        }

        if (count($path) == 2 && $path[0] == "users" && $path[1] == "check_exist") {
            if ($method == "GET") {
                $device = new Device();
                $userID = $device->GetUserID($request->GetProperty("auth_device_id"));

                $user = new User();
                if ($user->LoadByID($userID)) {
                    $companyUnitIDs = $user->GetPermissionLinkIDs("company_unit");
                    $companyUnitIDs = CompanyUnitList::AddChildIDs($companyUnitIDs);
                    if (count($companyUnitIDs) == 0) {
                        return false;
                    }

                    $checkUser = new User();
                    $checkUser->LoadByEmail($request->GetProperty("email"));

                    $linkToIDs = array();
                    foreach ($checkUser->GetProperty("PermissionList") as $permission) {
                        if ($permission["link_to"] == "company_unit") {
                            $linkToIDs[] = $permission["link_id"];
                        }
                    }

                    $linkToIDs = array_filter(array_unique($linkToIDs));

                    if (count(array_intersect($companyUnitIDs, $linkToIDs)) > 0) {
                        $accessToken = $checkUser->GenerateAccessToken();
                        $response->SetData(array(
                            "user_exists" => "Y",
                            "user_login_url" => GetUrlPrefix() . "admin/index.php?access_token=" . $accessToken
                        ));
                    } else {
                        $response->SetData(array("user_exists" => "N"));
                    }

                    $response->SetStatus(ApiResponse::STATUS_OK);
                    $response->SetCode(200);
                    return true;
                }
            }
        } elseif (count($path) == 2 && $path[0] == "users" && $path[1] == "current") {
            if ($method == "GET") {
                $device = new Device();
                $userID = $device->GetUserID($request->GetProperty("auth_device_id"));

                $user = new User();
                if ($user->LoadByID($userID)) {
                    $response->SetStatus(ApiResponse::STATUS_OK);
                    $response->SetCode(200);
                    $response->SetData($user->GetProperties());
                    return true;
                }

                $response->SetStatus(ApiResponse::STATUS_ERROR);
                $response->SetCode(403);
                return true;
            }
        } elseif (count($path) == 2 && $path[0] == "users" && $path[1] == "login") {
            if ($method == "PUT") {
                $user = new User();
                if ($user->LoadByRequest($request) && $user->Validate(array("webapi"))) {
                    if ($user->GetProperty("archive") == "N") {
                        $device = new Device();
                        if ($device->SetUserID($request->GetProperty("auth_device_id"),
                            $user->GetProperty("user_id"))) {
                            $response->SetStatus(ApiResponse::STATUS_OK);
                            $response->SetCode(200);
                            $response->SetData($user->GetProperties());
                            return true;
                        }
                    }
                }

                $response->SetStatus(ApiResponse::STATUS_ERROR);
                $response->SetCode(403);
                return true;
            }
        } elseif (count($path) == 2 && $path[0] == "users" && $path[1] == "password-change") {
            if ($method == "PUT") {
                $device = new Device();
                $userID = $device->GetUserID($request->GetProperty("auth_device_id"));

                $user = new User();
                if ($user->LoadByID($userID) && $user->Validate(array("webapi"))) {
                    if ($user->ChangePassword($userID, $request->GetProperty("password_old"),
                        $request->GetProperty("password_new"))) {
                        $response->SetStatus(ApiResponse::STATUS_OK);
                        $response->SetCode(201);
                    } else {
                        $response->SetStatus(ApiResponse::STATUS_ERROR);
                        $response->SetCode(400);
                        $response->LoadErrorsFromObject($user);
                    }
                    return true;
                }
                $response->SetStatus(ApiResponse::STATUS_ERROR);
                $response->SetCode(403);
                return true;
            }
        } elseif (count($path) == 2 && $path[0] == "users" && $path[1] == "password-reset") {
            if ($method == "POST") {
                $user = new User();
                $user->SetProperty("email", $request->GetProperty("email"));
                if ($user->SendPasswordToEmail()) {
                    $response->SetStatus(ApiResponse::STATUS_OK);
                    $response->SetCode(201);
                } else {
                    $response->SetStatus(ApiResponse::STATUS_ERROR);
                    $response->SetCode(400);
                    $response->LoadErrorsFromObject($user);
                }
                return true;
            }
        } elseif (count($path) == 2 && $path[0] == "users" && $path[1] == "image") {
            if ($method == "POST") {
                $device = new Device();
                $userID = $device->GetUserID($request->GetProperty("auth_device_id"));

                $user = new User();
                if ($user->LoadByID($userID) && $user->Validate(array("webapi"))) {
                    $updateUser = new User();
                    if ($updateUser->UpdateUserImage($userID, $user->GetProperty("user_image"))) {
                        $updateUser->LoadByID($userID);
                        $data = array("user_image_api_url" => $updateUser->GetProperty("user_image_api_url"));

                        $response->SetData($data);
                        $response->SetStatus(ApiResponse::STATUS_OK);
                        $response->SetCode(201);
                    } else {
                        $response->SetStatus(ApiResponse::STATUS_ERROR);
                        $response->SetCode(400);
                        $response->LoadErrorsFromObject($updateUser);
                    }
                    return true;
                }
                $response->SetStatus(ApiResponse::STATUS_ERROR);
                $response->SetCode(403);
                return true;
            } elseif ($method == "DELETE") {
                $device = new Device();
                $userID = $device->GetUserID($request->GetProperty("auth_device_id"));

                $user = new User();
                if ($user->LoadByID($userID) && $user->Validate(array("webapi"))) {
                    $user->RemoveUserImage($userID, $user->GetProperty("user_image"));
                    $response->SetStatus(ApiResponse::STATUS_OK);
                    $response->SetCode(200);
                    return true;
                }
                $response->SetStatus(ApiResponse::STATUS_ERROR);
                $response->SetCode(403);
                return true;
            }
        } elseif (count($path) == 2 && $path[0] == "users" && $path[1] == "fields") {
            if ($method == "PUT") {
                $device = new Device();
                $userID = $device->GetUserID($request->GetProperty("auth_device_id"));

                $user = new User();
                if ($user->LoadByID($userID) && $user->Validate(array("webapi"))) {
                    $updateUser = new User();
                    $data = array();
                    if ($request->GetProperty("field") == "email") {
                        if ($updateUser->UpdateField($userID, "email", $request->GetProperty("value"))) {
                            $response->SetStatus(ApiResponse::STATUS_OK);
                            $response->SetCode(201);
                        } else {
                            $response->SetStatus(ApiResponse::STATUS_ERROR);
                            $response->SetCode(400);
                            $response->LoadErrorsFromObject($updateUser);
                        }
                        return true;
                    }

                    if ($request->GetProperty("field") == "phone") {
                        if ($updateUser->UpdateField($userID, "phone", $request->GetProperty("value"))) {
                            $response->SetStatus(ApiResponse::STATUS_OK);
                            $response->SetCode(201);
                        } else {
                            $response->SetStatus(ApiResponse::STATUS_ERROR);
                            $response->SetCode(400);
                            $response->LoadErrorsFromObject($updateUser);
                        }
                        return true;
                    }
                }
                $response->SetStatus(ApiResponse::STATUS_ERROR);
                $response->SetCode(403);
                return true;
            }
        }
        return false;
    }
}

$request = new LocalObject(array_merge($_POST, $_GET));
$input = file_get_contents("php://input");
if ($input !== false && strlen($input) > 0) {
    $json = json_decode($input, true);
    if ($json !== null) {
        $request->AppendFromArray($json);
    }
}

$response = new ApiResponse();
$requestMethod = $_SERVER["REQUEST_METHOD"];

$urlParser = GetURLParser();
$path = $urlParser->GetShortPathAsArray();
array_shift($path);
$module = array_shift($path);

if (count($path) > 0) {
    $isRequestValid = true;

    if (!$request->ValidateNotEmpty("auth_device_id")) {
        $isRequestValid = false;
    }

    $device = new Device();
    $isDeviceRegistered = $device->IsDeviceRegistered($request->GetProperty("auth_device_id"));
    $isRegisterDeviceMethod = ($module == "core" && count($path) == 1 && $path[0] == "devices" && $requestMethod == "POST");
    if (!$isDeviceRegistered && !$isRegisterDeviceMethod) {
        $isRequestValid = false;
    }

    if ($isRequestValid) {
        $privateKey = $device->GetPrivateKey($request->GetProperty("auth_device_id"));
        $properties = $request->GetProperties();
        unset($properties["sign"]);
        ksort($properties);

        $chunks = array();
        foreach ($properties as $key => $value) {
            $chunks[] = $key . "=" . $value;
        }

        $signRequest = $request->GetProperty("sign");
        $signSha256 = hash("sha256", implode("&", $chunks) . $privateKey);
        $isSignCorrect = $signSha256 === $signRequest;

        if (!$isRegisterDeviceMethod && !$isSignCorrect) {
            $response->SetStatus("error");
            $response->SetCode(403);
            $response->AddError("api-bad-request");

        } else {
            $processor = null;
            if ($module == "core") {
                $processor = new ApiCoreProcessor();
            } else {
                $moduleObject = new Module();
                if ($moduleObject->ModuleExists($module)) {
                    $processor = $moduleObject->GetApiWebProcessor($module);
                }
            }

            if ($processor != null) {
                if (!$processor->Process($requestMethod, $path, $request, $response)) {
                    $response->SetStatus("error");
                    $response->SetCode(404);
                    $response->AddError("api-method-not-found");
                }
            } else {
                $response->SetStatus("error");
                $response->SetCode(404);
                $response->AddError("api-resource-not-found");
            }
        }
    } else {
        $response->SetStatus("error");
        $response->SetCode(400);
        $response->AddError("api-bad-request");
    }
} else {
    $response->SetStatus("error");
    $response->SetCode(404);
    $response->AddError("api-no-path");
}
$response->Output();

?>