<?php

require_once(dirname(__FILE__) . "/include/init.php");

class ApiCoreProcessor extends ApiProcessor
{
    public function Process($method, $path, LocalObject $request, ApiResponse $response)
    {
        if (count($path) == 1 && $path[0] == "all_variables") {
            $variableList = Language::GetFromDB(null, null, null, null, null, true);
            if ($method == "GET") {
                $response->SetStatus(ApiResponse::STATUS_OK);
                $response->SetCode(200);
                $response->SetData($variableList);
                return true;
            }
        }
        if (count($path) == 1 && $path[0] == "variables") {
            $variableList = Language::GetFromDB($request->GetProperty("language_code"), $request->GetProperty("type"),
                $request->GetProperty("module"), $request->GetProperty("template"), $request->GetProperty("tagName"));
            if ($method == "GET") {
                $response->SetStatus(ApiResponse::STATUS_OK);
                $response->SetCode(200);
                $response->SetData($variableList);
                return true;
            }
        }
        if (count($path) == 1 && $path[0] == "devices") {
            if ($method == "POST") {
                if ($request->ValidateNotEmpty("auth_device_id") && $request->ValidateNotEmpty("client")) {
                    $device = new Device();
                    $privateKey = $device->Register($request->GetProperty("auth_device_id"),
                        $request->GetProperty("client"));
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
        if (count($path) == 2 && $path[0] == "devices" && $path[1] == "push") {
            if ($method == "PUT") {
                if ($request->ValidateNotEmpty("auth_device_id") && $request->ValidateNotEmpty("token")) {
                    $device = new Device();
                    if ($device->SetPushToken($request->GetProperty("auth_device_id"),
                        $request->GetProperty("token"))) {
                        $response->SetStatus(ApiResponse::STATUS_OK);
                        $response->SetCode(201);
                        return true;
                    } else {
                        $response->SetStatus(ApiResponse::STATUS_ERROR);
                        $response->SetCode(400);
                        return true;
                    }
                }
            }
        }
        if (count($path) == 2 && $path[0] == "app_version" && $path[1] == "last") {
            if ($method == "GET") {
                if ($request->ValidateNotEmpty("auth_device_id") && $request->ValidateNotEmpty("client")) {
                    $response->SetStatus(ApiResponse::STATUS_OK);
                    $response->SetCode(200);
                    $response->SetData(array(
                        "last_critical_version" => AppVersion::GetLastVersion($request->GetProperty("client"), true),
                        "last_version" => AppVersion::GetLastVersion($request->GetProperty("client"))
                    ));
                    return true;
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
                    $employee = new Employee("company");
                    if ($employee->LoadByUserID($user->GetProperty("user_id"))) {
                        $stmt = GetStatement(DB_CONTROL);

                        $query = "SELECT id FROM config_history WHERE config_id=" . intval(Config::GetIDByCode("app_license")) . " ORDER BY id DESC LIMIT 1";
                        $employee->SetProperty("last_license_version", intval($stmt->FetchField($query)));

                        $query = "SELECT id FROM config_history WHERE config_id=" . intval(Config::GetIDByCode("app_guideline")) . " ORDER BY id DESC LIMIT 1";
                        $employee->SetProperty("last_guideline_version", intval($stmt->FetchField($query)));

                        $query = "SELECT id FROM config_history WHERE config_id=" . intval(Config::GetIDByCode("app_org_guideline")) . " ORDER BY id DESC LIMIT 1";
                        $employee->SetProperty("last_org_guideline_version", intval($stmt->FetchField($query)));

                        $forceApproval = Option::GetInheritableOptionValue(OPTION_LEVEL_EMPLOYEE,
                            OPTION__BASE__FORCE_APPROVAL, $employee->GetIntProperty("employee_id"), GetCurrentDate());
                        if ($forceApproval == "Y") {
                            $employee->SetProperty("need_approve_receipts",
                                intval(ReceiptList::GetNumberNecessaryActionsByEmployee(false,
                                    $employee->GetProperty("employee_id"))));
                        } else {
                            $employee->SetProperty("need_approve_receipts", 0);
                        }

                        $response->SetStatus(ApiResponse::STATUS_OK);
                        $response->SetCode(200);
                        $response->SetData($employee->GetProperties());
                        return true;
                    }
                }

                $response->SetStatus(ApiResponse::STATUS_ERROR);
                $response->SetCode(403);
                return true;
            }
        } elseif (count($path) == 2 && $path[0] == "users" && $path[1] == "login") {
            if ($method == "PUT") {
                $user = new User();
                if ($user->LoadByRequest($request) && $user->Validate(array("api"))) {
                    $device = new Device();
                    if ($device->SetUserID($request->GetProperty("auth_device_id"), $user->GetProperty("user_id"))) {
                        $employee = new Employee("company");
                        if ($employee->LoadByUserID($user->GetProperty("user_id"))) {
                            $productGroupList = new ProductGroupList("product");
                            $productGroupList->LoadProductGroupListForApi($employee, false, false, true);
                            $existActiveProductGroups = in_array(true,
                                array_column($productGroupList->GetItems(), "active"));

                            if ($existActiveProductGroups || $user->GetProperty("archive") == "N") {
                                $employee->SetUsesApplication("Y");

                                $stmt = GetStatement(DB_CONTROL);

                                $query = "SELECT id FROM config_history WHERE config_id=" . intval(Config::GetIDByCode("app_license")) . " ORDER BY id DESC LIMIT 1";
                                $employee->SetProperty("last_license_version", intval($stmt->FetchField($query)));

                                $query = "SELECT id FROM config_history WHERE config_id=" . intval(Config::GetIDByCode("app_guideline")) . " ORDER BY id DESC LIMIT 1";
                                $employee->SetProperty("last_guideline_version", intval($stmt->FetchField($query)));

                                $query = "SELECT id FROM config_history WHERE config_id=" . intval(Config::GetIDByCode("app_org_guideline")) . " ORDER BY id DESC LIMIT 1";
                                $employee->SetProperty("last_org_guideline_version", intval($stmt->FetchField($query)));

                                $response->SetStatus(ApiResponse::STATUS_OK);
                                $response->SetCode(200);
                                $response->SetData($employee->GetProperties());
                                return true;
                            }
                        }
                    }
                }
                $response->SetStatus(ApiResponse::STATUS_ERROR);
                $response->LoadErrorsFromObject($user);
                $response->SetCode(403);
                return true;
            }
        } elseif (count($path) == 2 && $path[0] == "users" && $path[1] == "password-change") {
            if ($method == "PUT") {
                $device = new Device();
                $userID = $device->GetUserID($request->GetProperty("auth_device_id"));

                $user = new User();
                if ($user->LoadByID($userID) && $user->Validate(array("api"))) {
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

                if ($request->IsPropertySet("email")) {
                    $user->LoadByEmail($request->GetProperty("email"));

                    if ($user->Validate(["api"])) {
                        $device = new Device();
                        if ($device->SetUserID($request->GetProperty("auth_device_id"), $user->GetProperty("user_id"))) {
                            $employee = new Employee("company");
                            if ($employee->LoadByUserID($user->GetProperty("user_id"))) {
                                $productGroupList = new ProductGroupList("product");
                                $productGroupList->LoadProductGroupListForApi($employee, false, false, true);
                                $existActiveProductGroups = in_array(true,
                                    array_column($productGroupList->GetItems(), "active"));

                                if ($existActiveProductGroups || $user->GetProperty("archive") == "N") {
                                    if ($user->SendPasswordToEmail()) {
                                        $response->SetStatus(ApiResponse::STATUS_OK);
                                        $response->SetCode(201);
                                        return true;
                                    }
                                } else {
                                    $user->AddError("reset-password-error-deactivated-user");
                                }
                            }
                        }
                    }
                }

                $response->SetStatus(ApiResponse::STATUS_ERROR);
                $response->SetCode(400);
                $response->LoadErrorsFromObject($user);

                return true;
            }
        } elseif (count($path) == 2 && $path[0] == "users" && $path[1] == "image") {
            if ($method == "POST") {
                $device = new Device();
                $userID = $device->GetUserID($request->GetProperty("auth_device_id"));

                $user = new User();
                if ($user->LoadByID($userID) && $user->Validate(array("api"))) {
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
                if ($user->LoadByID($userID) && $user->Validate(array("api"))) {
                    $user->RemoveUserImage($userID, $user->GetProperty("user_image"));
                    $response->SetStatus(ApiResponse::STATUS_OK);
                    $response->SetCode(200);
                    return true;
                }
                $response->SetStatus(ApiResponse::STATUS_ERROR);
                $response->SetCode(403);
                return true;
            }
        } elseif (count($path) == 2 && $path[0] == "users" && $path[1] == "mobile-application-info") {
            if ($method == "PUT") {
                $device = new Device();
                $userID = $device->GetUserID($request->GetProperty("auth_device_id"));

                $user = new User();
                if ($user->LoadByID($userID) && $user->Validate(array("api"))) {
                    if ($device->SaveVersion($request->GetProperty("auth_device_id"), $userID,
                        $request->GetProperty("version"))) {
                        $response->SetStatus(ApiResponse::STATUS_OK);
                        $response->SetCode(201);
                        return true;
                    } else {
                        $response->SetStatus(ApiResponse::STATUS_ERROR);
                        $response->SetCode(400);
                        return true;
                    }
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
                if ($user->LoadByID($userID) && $user->Validate(array("api"))) {
                    $updateUser = new User();
                    if (in_array($request->GetProperty("field"), ["email", "phone"])) {
                        if (
                            $updateUser->UpdateField(
                                $userID,
                                $request->GetProperty("field"),
                                $request->GetProperty("value")
                            )
                        ) {
                            $response->SetStatus(ApiResponse::STATUS_OK);
                            $response->SetCode(201);
                        } else {
                            $response->SetStatus(ApiResponse::STATUS_ERROR);
                            $response->SetCode(400);
                            $response->LoadErrorsFromObject($updateUser);
                        }

                        return true;
                    }

                    if (in_array($request->GetProperty("field"), ["bank_name", "iban", "bic"])) {
                        $employee = new Employee("company");
                        $employee->LoadByUserID($userID);
                        $employee->SetProperty(
                            $request->GetProperty("field"),
                            $request->GetProperty("value")
                        );
                        if ($employee->Save()) {
                            $response->SetStatus(ApiResponse::STATUS_OK);
                            $response->SetCode(201);
                        } else {
                            $response->SetStatus(ApiResponse::STATUS_ERROR);
                            $response->SetCode(400);
                            $response->LoadErrorsFromObject($employee);
                        }

                        return true;
                    }
                }
                $response->SetStatus(ApiResponse::STATUS_ERROR);
                $response->SetCode(403);

                return true;
            }
        } elseif (count($path) == 2 && $path[0] == "users" && $path[1] == "bank_info") {
            if ($method == "PUT") {
                $device = new Device();
                $userID = $device->GetUserID($request->GetProperty("auth_device_id"));

                if (
                    $request->IsPropertySet("bank_name") &&
                    $request->IsPropertySet("iban") &&
                    $request->IsPropertySet("bic")
                ) {
                    $employee = new Employee("company");
                    $employee->LoadByUserID($userID);
                    $employee->AppendFromObject($request, ["bank_name", "iban", "bic"]);
                    if ($employee->Save()) {
                        $response->SetStatus(ApiResponse::STATUS_OK);
                        $response->SetCode(201);
                    } else {
                        $response->SetStatus(ApiResponse::STATUS_ERROR);
                        $response->SetCode(400);
                        $response->LoadErrorsFromObject($employee);
                    }

                    return true;
                }
                $response->SetStatus(ApiResponse::STATUS_ERROR);
                $response->SetCode(403);

                return true;
            }
        } elseif (count($path) == 1 && $path[0] == "license") {
            if ($method == "GET") {
                $config = new Config();
                $license = $config->GetLicenseTermsOrGuidelineByCode('app_license');

                $response->SetStatus(ApiResponse::STATUS_OK);
                $response->SetCode(200);
                $response->SetData($license);
                return true;
            }
        } elseif (count($path) == 2 && $path[0] == "license" && $path[1] == "accept") {
            if ($method == "POST") {
                $device = new Device();
                $userID = $device->GetUserID($request->GetProperty("auth_device_id"));

                $user = new User();
                if ($user->LoadByID($userID) && $user->Validate(array("api"))) {
                    $employee = new Employee("company");
                    if ($employee->LoadByUserID($user->GetProperty("user_id"))) {
                        $licenseVersion = $request->GetIntProperty("license_version") ? $request->GetIntProperty("license_version") : $request->GetIntProperty("version");
                        $guidelineVersion = $request->GetIntProperty("guideline_version");
                        $orgGuidelineVersion = $request->GetIntProperty("org_guideline_version");

                        if ($employee->SetAcceptedDocumentsVersions($licenseVersion, $guidelineVersion,
                            $orgGuidelineVersion)) {
                            $response->SetStatus(ApiResponse::STATUS_OK);
                            $response->SetCode(200);
                            return true;
                        }
                    }
                }

                $response->SetStatus(ApiResponse::STATUS_ERROR);
                $response->SetCode(403);
                return true;
            }
        } elseif (count($path) == 1 && $path[0] == "guideline") {
            if ($method == "GET") {
                $config = new Config();
                $guideline = $config->GetLicenseTermsOrGuidelineByCode('app_guideline');

                $response->SetStatus(ApiResponse::STATUS_OK);
                $response->SetCode(200);
                $response->SetData($guideline);
                return true;
            }
        } elseif (count($path) == 1 && $path[0] == "org_guideline") {
            if ($method == "GET") {
                $config = new Config();
                $guideline = $config->GetLicenseTermsOrGuidelineByCode('app_org_guideline');

                $response->SetStatus(ApiResponse::STATUS_OK);
                $response->SetCode(200);
                $response->SetData($guideline);
                return true;
            }
        } elseif (count($path) == 1 && $path[0] == "strings") {
            if ($method == "GET") {
                $iconActions = array("icon_actions" => Config::GetConfigValue("icon_actions"));
                PrepareDownloadPath($iconActions, "icon_actions", CONFIG_FILE_DIR, CONTAINER__CORE);

                $iconMessages = array("icon_messages" => Config::GetConfigValue("icon_messages"));
                PrepareDownloadPath($iconMessages, "icon_messages", CONFIG_FILE_DIR, CONTAINER__CORE);

                $data = array(
                    "receipt_confirm_description" => Config::GetConfigValue("mobile_app_receipt_confirm_description"),
                    "receipt_confirm_button" => Config::GetConfigValue("mobile_app_receipt_confirm_button"),
                    "employment_agreement_confirm_button" => Config::GetConfigValue("mobile_app_employment_agreement_confirm_button"),
                    "employment_agreement_text_after_confirm" => Config::GetConfigValue("mobile_app_employment_agreement_text_after_confirm"),
                    "icon_actions_download_url" => $iconActions["icon_actions_download_url"],
                    "icon_messages_download_url" => $iconMessages["icon_messages_download_url"]
                );

                $response->SetStatus(ApiResponse::STATUS_OK);
                $response->SetCode(200);
                $response->SetData($data);
                return true;
            }
        } elseif (count($path) == 1 && $path[0] == "backup") {
            if ($method == "POST") {
                $response->SetStatus(ApiResponse::STATUS_OK);
                $response->SetCode(200);
                $response->SetData(Operation::SaveCron(null, "Dumptruck backup completed<br>", "backup", null, null,
                    true));
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
    $isGetVariablesMethod = ($module == "core" && count($path) == 1 && $path[0] == "all_variables" && $requestMethod == "GET");
    $isBackupMethod = ($module == "core" && count($path) == 1 && $path[0] == "backup" && $requestMethod == "POST");
    if (!$isDeviceRegistered && !$isRegisterDeviceMethod && !$isGetVariablesMethod && !$isBackupMethod) {
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

        if (!$isRegisterDeviceMethod && !$isSignCorrect && !$isGetVariablesMethod && !$isBackupMethod) {
            $response->SetStatus("error");
            $response->SetCode(403);
            $response->AddError("api-bad-request");

        } else {
            $userID = $device->GetUserID($request->GetProperty("auth_device_id"));
            if ($userID) {
                $employee = new Employee("company");
                if ($employee->LoadByUserID($userID)) {
                    Employee::SetEmployeeField($employee->GetProperty("employee_id"), "last_api_call",
                        GetCurrentDateTime());
                }
            }

            $processor = null;
            if ($module == "core") {
                $processor = new ApiCoreProcessor();
            } else {
                $moduleObject = new Module();
                if ($moduleObject->ModuleExists($module)) {
                    $processor = $moduleObject->GetApiProcessor($module);
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