<?php

require_once(dirname(__FILE__) . "/../../include/init.php");

class ApiProductProcessor extends ApiProcessor
{
    private $module;

    public function ApiProductProcessor($module)
    {
        $this->module = $module;
    }

    public function Process($method, $path, LocalObject $request, ApiResponse $response)
    {
        if (count($path) == 1 && $path[0] == "product-groups") {
            if ($method == "GET") {
                $device = new Device();
                $userID = $device->GetUserID($request->GetProperty("auth_device_id"));

                $user = new User();
                if ($user->LoadByID($userID) && $user->Validate(array("api"))) {
                    $employee = new Employee("company");
                    if ($employee->LoadByUserID($userID)) {
                        $productGroupList = new ProductGroupList($this->module);
                        $productGroupList->LoadProductGroupListForApi($employee, false, false, true);

                        $response->SetStatus(ApiResponse::STATUS_OK);
                        $response->SetCode(200);
                        $response->SetData($productGroupList->GetItems());

                        return true;
                    }
                }

                $response->SetStatus(ApiResponse::STATUS_ERROR);
                $response->SetCode(403);

                return true;
            }
        }
        if (count($path) == 1 && $path[0] == "food-calendar") {
            if ($method == "GET") {
                $device = new Device();
                $userID = $device->GetUserID($request->GetProperty("auth_device_id"));

                $user = new User();
                if ($user->LoadByID($userID) && $user->Validate(array("api"))) {
                    $employee = new Employee("company");
                    if ($employee->LoadByUserID($userID)) {
                        $specificFood = new SpecificProductGroupFood();

                        $unitDateList = $specificFood->GetUnitDateList(
                            $employee->GetProperty("employee_id"),
                            $request->GetProperty("date_from"),
                            $request->GetProperty("date_to")
                        );

                        $response->SetStatus(ApiResponse::STATUS_OK);
                        $response->SetCode(200);
                        $response->SetData($unitDateList);

                        return true;
                    }
                }

                $response->SetStatus(ApiResponse::STATUS_ERROR);
                $response->SetCode(403);

                return true;
            }
        }
        if (count($path) == 1 && $path[0] == "food-voucher-calendar") {
            if ($method == "GET") {
                $device = new Device();
                $userID = $device->GetUserID($request->GetProperty("auth_device_id"));

                $user = new User();
                if ($user->LoadByID($userID) && $user->Validate(array("api"))) {
                    $employee = new Employee('company');
                    if ($employee->LoadByUserID($userID)) {
                        $specificFoodVoucher = new SpecificProductGroupFoodVoucher();

                        $unitDateListFoodVoucher = $specificFoodVoucher->GetUnitDateList(
                            $employee->GetProperty("employee_id"),
                            $request->GetProperty("date_from"),
                            $request->GetProperty("date_to")
                        );

                        $response->SetStatus(ApiResponse::STATUS_OK);
                        $response->SetCode(200);
                        $response->SetData($unitDateListFoodVoucher);

                        return true;
                    }
                }

                $response->SetStatus(ApiResponse::STATUS_ERROR);
                $response->SetCode(403);

                return true;
            }
        }
        if (count($path) == 2 && $path[0] == "food-voucher-calendar" && $path[1] == "date") {
            if ($method == "GET") {
                $device = new Device();
                $userID = $device->GetUserID($request->GetProperty("auth_device_id"));

                $user = new User();
                if ($user->LoadByID($userID) && $user->Validate(array("api"))) {
                    $employee = new Employee("company");
                    if ($employee->LoadByUserID($userID)) {
                        if (!$request->IsPropertySet("date")) {
                            $request->SetProperty("date", GetCurrentDate());
                        }

                        $specificFoodVoucher = new SpecificProductGroupFoodVoucher();
                        $unit = $specificFoodVoucher->GetApiUnit(new Receipt("receipt", array(
                            "document_date" => date("Y-m-d", strtotime($request->GetProperty("date"))),
                            "employee_id" => $request->GetProperty("employee_id")
                        )));

                        $receiptList = $specificFoodVoucher->GetReceiptListForDate(
                            $employee->GetProperty("employee_id"),
                            date("d-m-Y", strtotime($request->GetProperty("date")))
                        );
                        for ($i = 0; $i < count($receiptList); $i++) {
                            $receiptFileList = new ReceiptFileList("receipt");
                            $receiptFileList->LoadFileList($receiptList[$i]["receipt_id"]);
                            $receiptList[$i]["file_list"] = $receiptFileList->GetItems();
                        }

                        $response->SetStatus(ApiResponse::STATUS_OK);
                        $response->SetCode(200);
                        $response->SetData(array(
                            "unit" => number_format($unit, 2, ".", ","),
                            "receipt_list" => $receiptList
                        ));

                        return true;
                    }
                }

                $response->SetStatus(ApiResponse::STATUS_ERROR);
                $response->SetCode(403);

                return true;
            }
        }
        if (count($path) == 2 && $path[0] == "food-calendar" && $path[1] == "date") {
            if ($method == "GET") {
                $device = new Device();
                $userID = $device->GetUserID($request->GetProperty("auth_device_id"));

                $user = new User();
                if ($user->LoadByID($userID) && $user->Validate(array("api"))) {
                    $employee = new Employee("company");
                    if ($employee->LoadByUserID($userID)) {
                        if (!$request->IsPropertySet("date")) {
                            $request->SetProperty("date", GetCurrentDate());
                        }

                        $specificFood = new SpecificProductGroupFood();
                        $unit = $specificFood->GetApiUnit(new Receipt("receipt", array(
                            "document_date" => date("Y-m-d", strtotime($request->GetProperty("date"))),
                            "employee_id" => $request->GetProperty("employee_id")
                        )));

                        $receiptList = $specificFood->GetReceiptListForDate(
                            $employee->GetProperty("employee_id"),
                            date("d-m-Y", strtotime($request->GetProperty("date")))
                        );
                        for ($i = 0; $i < count($receiptList); $i++) {
                            $receiptFileList = new ReceiptFileList("receipt");
                            $receiptFileList->LoadFileList($receiptList[$i]["receipt_id"]);
                            $receiptList[$i]["file_list"] = $receiptFileList->GetItems();
                        }

                        $response->SetStatus(ApiResponse::STATUS_OK);
                        $response->SetCode(200);
                        $response->SetData(array(
                            "unit" => number_format($unit, 2, ".", ","),
                            "receipt_list" => $receiptList
                        ));

                        return true;
                    }
                }

                $response->SetStatus(ApiResponse::STATUS_ERROR);
                $response->SetCode(403);

                return true;
            }
        } elseif (count($path) == 1 && $path[0] == "option-codes") {
            if ($method == "GET") {
                $device = new Device();
                $userID = $device->GetUserID($request->GetProperty("auth_device_id"));
                $user = new User();
                if ($user->LoadByID($userID) && $user->Validate(array("api"))) {
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
        } elseif (count($path) == 1 && $path[0] == "option") {
            if ($method == "GET") {
                $device = new Device();
                $userID = $device->GetUserID($request->GetProperty("auth_device_id"));
                $user = new User();
                if ($user->LoadByID($userID) && $user->Validate(array("api"))) {
                    $employee = new Employee("company");
                    if ($employee->LoadByUserID($userID)) {
                        $response->SetStatus(ApiResponse::STATUS_OK);
                        $response->SetCode(200);
                        $response->SetData(Option::GetInheritableOptionValue(
                            OPTION_LEVEL_EMPLOYEE,
                            $request->GetProperty("option_code"),
                            $employee->GetProperty("employee_id"),
                            $request->GetProperty("date")
                        ));

                        return true;
                    }
                }
            } elseif ($method == "PUT") {
                $device = new Device();
                $userID = $device->GetUserID($request->GetProperty("auth_device_id"));
                $user = new User();
                if ($user->LoadByID($userID) && $user->Validate(array("api"))) {
                    $option = new Option($this->module);
                    if (
                        strpos(
                            $request->GetProperty("option_code"),
                            "price"
                        ) !== false || strpos($request->GetProperty("option_code"), "discount") !== false
                    ) {
                        $option->AddError("option-access-denied", $this->module);
                    }

                    $employee = new Employee("company");
                    if ($employee->LoadByUserID($userID)) {
                        $option->SetProperty("created_from", "api");
                        $optionID = Option::GetOptionIDByCode($request->GetProperty("option_code"));
                        if (
                            !$option->HasErrors()
                            && $option->SaveOptionValue(
                                OPTION_LEVEL_EMPLOYEE,
                                $optionID,
                                $request->GetProperty("value"),
                                $employee->GetIntProperty("employee_id"),
                                null,
                                $user
                            )
                        ) {
                            $response->SetStatus(ApiResponse::STATUS_OK);
                            $response->SetCode(200);
                            $response->SetData("Option value was changed");

                            return true;
                        }

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
        }

        return false;
    }
}
