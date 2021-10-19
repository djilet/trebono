<?php

require_once(dirname(__FILE__) . "/../../include/init.php");

class ApiWebProductProcessor extends ApiProcessor
{
    private $module;

    public function ApiWebProductProcessor($module)
    {
        $this->module = $module;
    }

    public function Process($method, $path, LocalObject $request, ApiResponse $response)
    {
        if (count($path) == 1 && $path[0] == "contract") {
            if ($method == "POST") {
                $device = new Device();
                $userID = $device->GetUserID($request->GetProperty("auth_device_id"));
                $user = new User();
                if ($user->LoadByID($userID) && $user->Validate(array("webapi"))) {
                    $contract = new Contract($this->module);
                    if ($request->GetProperty("level") == OPTION_LEVEL_COMPANY_UNIT) {
                        if (!CompanyUnit::ValidateAccess($request->GetProperty("entity_id"), $userID)) {
                            $contract->AddError("contract-validation-failed", $this->module);
                        }
                    } elseif ($request->GetProperty("level") == OPTION_LEVEL_EMPLOYEE) {
                        if (!Employee::ValidateAccess($request->GetProperty("entity_id"), $userID)) {
                            $contract->AddError("contract-validation-failed", $this->module);
                        }
                    } else {
                        $contract->AddError("contract-incorrect-level", $this->module);
                    }

                    $contract->SetProperty("start_from", "web_api");
                    $productID = Product::GetProductIDByCode($request->GetProperty("product_code"));

                    if (!$contract->HasErrors() && $contract->OnOptionUpdate(constant($request->GetProperty("level")),
                            $productID, $request->GetProperty("entity_id"), null, $request->GetProperty("start_date"),
                            null)) {
                        $response->SetStatus(ApiResponse::STATUS_OK);
                        $response->SetCode(200);
                        $response->SetData(array("contract_id" => $contract->GetProperty("contract_id")));
                        return true;
                    } else {
                        $response->AppendErrorsFromObject($contract);
                        $response->SetStatus(ApiResponse::STATUS_ERROR);
                        $response->SetCode(400);
                        return true;
                    }
                }
            }
            $response->SetStatus(ApiResponse::STATUS_ERROR);
            $response->SetCode(403);
            return true;
        }
        if (count($path) == 2 && $path[0] == "contract") {
            if ($method == "PUT") {
                $device = new Device();
                $userID = $device->GetUserID($request->GetProperty("auth_device_id"));
                $user = new User();
                if ($user->LoadByID($userID) && $user->Validate(array("webapi"))) {
                    $contract = new Contract($this->module);

                    if ($request->GetProperty("level") == OPTION_LEVEL_COMPANY_UNIT) {
                        if (!CompanyUnit::ValidateAccess($request->GetProperty("entity_id"), $userID)) {
                            $contract->AddError("contract-validation-failed", $this->module);
                        }
                    } elseif ($request->GetProperty("level") == OPTION_LEVEL_EMPLOYEE) {
                        if (!Employee::ValidateAccess($request->GetProperty("entity_id"), $userID)) {
                            $contract->AddError("contract-validation-failed", $this->module);
                        }
                    } else {
                        $contract->AddError("contract-incorrect-level", $this->module);
                    }

                    $contract->SetProperty("end_from", "web_api");
                    $productID = Product::GetProductIDByCode($request->GetProperty("product_code"));

                    if (!$contract->HasErrors() && $contract->OnOptionUpdate(constant($request->GetProperty("level")),
                            $productID, $request->GetProperty("entity_id"), $path[1], null,
                            $request->GetProperty("end_date"))) {
                        $response->SetStatus(ApiResponse::STATUS_OK);
                        $response->SetCode(200);
                        $response->SetData("Contract successfully ended");
                        return true;
                    } else {
                        $response->AppendErrorsFromObject($contract);
                        $response->SetStatus(ApiResponse::STATUS_ERROR);
                        $response->SetCode(400);
                        return true;
                    }
                }
            }
            $response->SetStatus(ApiResponse::STATUS_ERROR);
            $response->SetCode(403);
            return true;
        } elseif (count($path) == 1 && $path[0] == "product-codes") {
            if ($method == "GET") {
                $device = new Device();
                $userID = $device->GetUserID($request->GetProperty("auth_device_id"));
                $user = new User();
                if ($user->LoadByID($userID) && $user->Validate(array("webapi"))) {
                    $codeList = ProductList::GetFullProductListForWebApi();
                    $response->SetStatus(ApiResponse::STATUS_OK);
                    $response->SetCode(200);
                    $response->SetData($codeList);
                    return true;
                }
            }
            $response->SetStatus(ApiResponse::STATUS_ERROR);
            $response->SetCode(403);
            return true;
        } elseif (count($path) == 2 && $path[0] == "option") {
            if ($method == "PUT") {
                $device = new Device();
                $userID = $device->GetUserID($request->GetProperty("auth_device_id"));
                $user = new User();
                if ($user->LoadByID($userID) && $user->Validate(array("webapi"))) {
                    $option = new Option($this->module);
                    if (strpos($request->GetProperty("option_code"),
                            "price") !== false || strpos($request->GetProperty("option_code"), "discount") !== false) {
                        $option->AddError("option-access-denied", $this->module);
                    }

                    if ($request->GetProperty("level") == OPTION_LEVEL_COMPANY_UNIT) {
                        if (!CompanyUnit::ValidateAccess($path[1], $userID)) {
                            $option->AddError("option-validation-failed", $this->module);
                        }
                    } elseif ($request->GetProperty("level") == OPTION_LEVEL_EMPLOYEE) {
                        if (!Employee::ValidateAccess($path[1], $userID)) {
                            $option->AddError("option-validation-failed", $this->module);
                        }
                    } else {
                        $option->AddError("option-incorrect-level", $this->module);
                    }

                    $option->SetProperty("created_from", "web_api");
                    $optionID = Option::GetOptionIDByCode($request->GetProperty("option_code"));

                    if (!$option->HasErrors() && $option->SaveOptionValue(constant($request->GetProperty("level")),
                            $optionID, $request->GetProperty("value"), $path[1], null, $user)) {
                        $response->SetStatus(ApiResponse::STATUS_OK);
                        $response->SetCode(200);
                        $response->SetData("Option value was changed");
                        return true;
                    } else {
                        $response->AppendErrorsFromObject($option);
                        $response->SetStatus(ApiResponse::STATUS_ERROR);
                        $response->SetCode(400);
                        return true;
                    }
                }
            }
            $response->SetStatus(ApiResponse::STATUS_ERROR);
            $response->SetCode(403);
            return true;
        } elseif (count($path) == 1 && $path[0] == "option-codes") {
            if ($method == "GET") {
                $device = new Device();
                $userID = $device->GetUserID($request->GetProperty("auth_device_id"));
                $user = new User();
                if ($user->LoadByID($userID) && $user->Validate(array("webapi"))) {
                    $codeList = OptionList::GetOptionListForWebApi();
                    $response->SetStatus(ApiResponse::STATUS_OK);
                    $response->SetCode(200);
                    $response->SetData($codeList);
                    return true;
                }
            }
            $response->SetStatus(ApiResponse::STATUS_ERROR);
            $response->SetCode(403);
            return true;
        }
        return false;
    }

}

?>