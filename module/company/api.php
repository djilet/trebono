<?php

require_once(dirname(__FILE__) . "/../../include/init.php");

class ApiCompanyProcessor extends ApiProcessor
{
    private $module;

    public function ApiCompanyProcessor($module)
    {
        $this->module = $module;
    }

    public function Process($method, $path, LocalObject $request, ApiResponse $response)
    {
        if (count($path) == 1 && $path[0] == "vouchers") {
            if ($method == "GET") {
                $device = new Device();
                $userID = $device->GetUserID($request->GetProperty("auth_device_id"));

                $user = new User();
                if ($user->LoadByID($userID) && $user->Validate(array("api"))) {
                    $employee = new Employee($this->module);
                    if ($employee->LoadByUserID($userID)) {
                        $specificProductGroup = SpecificProductGroupFactory::Create($request->GetProperty("group_id"));

                        if (method_exists($specificProductGroup, "GetReceiptMappedVoucherList")) {
                            $voucherList = $specificProductGroup->GetReceiptMappedVoucherList($employee->GetProperty('employee_id'));
                        } else {
                            $specificProductGroup = new SpecificProductGroupBonus();
                            $voucherList = $specificProductGroup->GetReceiptMappedVoucherList($employee->GetProperty('employee_id'));
                        }

                        $companyUnit = new CompanyUnit("company");
                        $companyUnit->LoadByID($employee->GetProperty("company_unit_id"));

                        $currentMonthPayrollTime = strtotime(date("Y-m-" . intval($companyUnit->GetProperty("financial_statement_date"))));
                        $currentTime = time();
                        if ($currentTime >= $currentMonthPayrollTime) {
                            $payrollPeriodStartTime = strtotime(date("Y-m-01"));
                        } else {
                            $payrollPeriodStartTime = strtotime(date("Y-m-01", strtotime("- 1 month")));
                        }

                        $voucherListActive = array();
                        foreach ($voucherList as $key => $voucher) {
                            $threeMonthsAgo = date("Y-m-d", strtotime("- 3 month"));
                            if (
                                ($voucher["amount_left"] <= 0
                                    && strtotime($voucher["voucher_date"]) < strtotime($threeMonthsAgo)
                                )
                                || strtotime($voucherList[$key]["end_date"]) < $payrollPeriodStartTime
                                || strtotime($voucherList[$key]["voucher_date"]) > $currentTime
                            ) {
                                continue;
                            }

                            if (isset($voucher["receipt_list"])) {
                                foreach ($voucher["receipt_list"] as $receiptKey => $receipt) {
                                    if (intval($receipt["creditor_export_id"]) <= 0) {
                                        continue;
                                    }

                                    $voucherList[$key]["receipt_list"][$receiptKey]["payment_date"] = date(
                                        "Y-m-d",
                                        strtotime(VoucherExport::GetPropertyByID(
                                            $receipt["creditor_export_id"],
                                            "created"
                                        ))
                                    );
                                }
                            }
                            $voucherListActive[] = $voucherList[$key];
                        }

                        $response->SetStatus(ApiResponse::STATUS_OK);
                        $response->SetCode(200);
                        $response->SetData($voucherListActive);

                        return true;
                    }
                }

                $response->SetStatus(ApiResponse::STATUS_ERROR);
                $response->SetCode(403);

                return true;
            }
        } elseif (count($path) == 1 && $path[0] == "voucher-preference") {
            if ($method == "GET") {
                $device = new Device();
                $userID = $device->GetUserID($request->GetProperty("auth_device_id"));

                $user = new User();
                if ($user->LoadByID($userID) && $user->Validate(array("api"))) {
                    $employee = new Employee($this->module);
                    if ($employee->LoadByUserID($userID)) {
                        $optionReceipt = new Receipt("receipt", [
                            "receipt_id" => null,
                            "group_id" => $request->GetProperty("group_id"),
                            "employee_id" => $employee->GetIntProperty("employee_id"),
                            "document_date" => GetCurrentDate(),
                        ]);
                        $result = Receipt::GetSetsOfGoodsList($optionReceipt, true);
                        $item = [];
                        $item["receipt_sets_of_goods"] = $result["sets_of_goods"];
                        $item["show_voucher_category_select"] = $result["show_voucher_category_select"];
                        $item["has_available_restrictions"] = $result["has_available_restrictions"];
                        $item["no_available_vouchers"] = $result["no_available_vouchers"];
                        $item["default_reason_code"]
                            = OPTIONS_VOUCHER_DEFAULT_REASON[ProductGroup::GetProductGroupCodeByID(
                                $request->GetProperty("group_id")
                            )];
                        $item["preferred_category_button"] = GetTranslation(
                            "preferred-voucher-category-select",
                            "company",
                            ["category" => $result["selected"]]
                        );

                        $response->SetStatus(ApiResponse::STATUS_OK);
                        $response->SetCode(200);
                        $response->SetData($item);

                        return true;
                    }
                }

                $response->SetStatus(ApiResponse::STATUS_ERROR);
                $response->SetCode(403);

                return true;
            }
        } elseif (count($path) == 1 && $path[0] == "check-available-vouchers") {
            if ($method == "GET") {
                $device = new Device();
                $userID = $device->GetUserID($request->GetProperty("auth_device_id"));

                $user = new User();
                if ($user->LoadByID($userID) && $user->Validate(array("api"))) {
                    $employee = new Employee($this->module);
                    if ($employee->LoadByUserID($userID)) {
                        $optionReceipt = new Receipt("receipt", [
                            "receipt_id" => null,
                            "group_id" => $request->GetProperty("group_id"),
                            "employee_id" => $employee->GetIntProperty("employee_id"),
                            "document_date" => GetCurrentDate(),
                        ]);
                        $voucherList = VoucherList::GetAvailableVoucherListForReceipt($optionReceipt);
                        $result = [];
                        $result["has_vouchers"] = !empty($voucherList);
                        $result["error_message"] = empty($voucherList)
                            ? GetTranslation("no-available-vouchers", "company")
                            : "";

                        $response->SetStatus(ApiResponse::STATUS_OK);
                        $response->SetCode(200);
                        $response->SetData($result);

                        return true;
                    }
                }

                $response->SetStatus(ApiResponse::STATUS_ERROR);
                $response->SetCode(403);

                return true;
            }
        } elseif (count($path) == 1 && $path[0] == "givve_access") {
            if ($method == "PUT") {
                $device = new Device();
                $userID = $device->GetUserID($request->GetProperty("auth_device_id"));
                $user = new User();
                if ($user->LoadByID($userID) && $user->Validate(array("api"))) {
                    $employee = new Employee($this->module);
                    if ($employee->LoadByUserID($userID)) {
                        if (
                            $employee->GetGivveAccess(
                                $request->GetProperty("givve_login"),
                                $request->GetProperty("givve_password")
                            )
                        ) {
                            $response->SetStatus(ApiResponse::STATUS_OK);
                            $response->SetCode(200);

                            return true;
                        }

                        $response->AppendErrorsFromObject($employee);
                        $response->SetStatus(ApiResponse::STATUS_ERROR);
                        $response->SetCode(400);

                        return true;
                    }
                }
                $response->SetStatus(ApiResponse::STATUS_ERROR);
                $response->SetCode(403);

                return true;
            }
        } elseif (count($path) == 2 && $path[0] == "givve_access" && $path[1] == "clear") {
            if ($method == "PUT") {
                $device = new Device();
                $userID = $device->GetUserID($request->GetProperty("auth_device_id"));

                $user = new User();
                if ($user->LoadByID($userID) && $user->Validate(array("api"))) {
                    $employee = new Employee($this->module);
                    if ($employee->LoadByUserID($userID)) {
                        Employee::SetGivveAccessToken($employee->GetProperty("employee_id"));

                        $response->SetStatus(ApiResponse::STATUS_OK);
                        $response->SetCode(200);

                        return true;
                    }
                }

                $response->SetStatus(ApiResponse::STATUS_ERROR);
                $response->SetCode(403);

                return true;
            }
        } elseif (count($path) == 1 && $path[0] == "givve_vouchers") {
            if ($method == "GET") {
                $device = new Device();
                $userID = $device->GetUserID($request->GetProperty("auth_device_id"));
                $user = new User();
                if ($user->LoadByID($userID) && $user->Validate(array("api"))) {
                    $employee = new Employee($this->module);
                    if ($employee->LoadByUserID($userID)) {
                        /*$voucherList = new GivveVoucherList("company");
                        $voucherList->LoadVoucherList($employee->GetProperty('employee_id'));*/

                        $voucherList = $employee->GetGivveVoucherList();

                        $response->SetStatus(ApiResponse::STATUS_OK);
                        $response->SetCode(200);
                        //$response->SetData($voucherList->GetItems());
                        $response->SetData($voucherList);

                        return true;
                    }
                }
                $response->SetStatus(ApiResponse::STATUS_ERROR);
                $response->SetCode(403);

                return true;
            }
        } elseif (count($path) == 1 && $path[0] == "givve_transactions") {
            if ($method == "GET") {
                $device = new Device();
                $userID = $device->GetUserID($request->GetProperty("auth_device_id"));
                $user = new User();
                if ($user->LoadByID($userID) && $user->Validate(array("api")) && $request->IsPropertySet('voucher_id')) {
                    $employee = new Employee($this->module);
                    if ($employee->LoadByUserID($userID)) {
                        /*$transactionList = new GivveTransactionList("company");
                        $transactionList->LoadTransactionList($request->GetProperty('voucher_id'));*/

                        $transactionList = $employee->GetGivveTransactionList($request->GetProperty("voucher_id"));

                        $response->SetStatus(ApiResponse::STATUS_OK);
                        $response->SetCode(200);
                        //$response->SetData($transactionList->GetItems());
                        $response->SetData($transactionList);

                        return true;
                    }
                }
                $response->SetStatus(ApiResponse::STATUS_ERROR);
                $response->SetCode(403);

                return true;
            }
        }
        if (count($path) == 1 && $path[0] == "trips") {
            if ($method == "GET") {
                $device = new Device();
                $userID = $device->GetUserID($request->GetProperty("auth_device_id"));
                $user = new User();
                if ($user->LoadByID($userID) && $user->Validate(array("api"))) {
                    $employee = new Employee($this->module);
                    if ($employee->LoadByUserID($userID)) {
                        $tripList = new TripList($this->module);
                        $tripList->LoadTripListListForApi($employee->GetProperty("employee_id"));

                        $response->SetStatus(ApiResponse::STATUS_OK);
                        $response->SetCode(200);
                        $response->SetData($tripList->GetItems());

                        return true;
                    }
                }
                $response->SetStatus(ApiResponse::STATUS_ERROR);
                $response->SetCode(403);

                return true;
            } elseif ($method == "POST") {
                $device = new Device();
                $userID = $device->GetUserID($request->GetProperty("auth_device_id"));
                $user = new User();
                if ($user->LoadByID($userID) && $user->Validate(array("api"))) {
                    $employee = new Employee($this->module);
                    if ($employee->LoadByUserID($userID)) {
                        $trip = new Trip($this->module);
                        $trip->SetProperty("employee_id", $employee->GetProperty("employee_id"));
                        $trip->SetProperty("trip_name", $request->GetProperty("trip_name"));
                        $trip->SetProperty("purpose", $request->GetProperty("purpose"));
                        $trip->SetProperty("start_date", $request->GetProperty("start_date"));
                        $trip->SetProperty("end_date", $request->GetProperty("end_date"));

                        if ($trip->Create()) {
                            $response->SetStatus(ApiResponse::STATUS_OK);
                            $response->SetCode(201);
                            $response->SetData(array("trip_id" => $trip->GetProperty("trip_id")));

                            return true;
                        }

                        $response->SetStatus(ApiResponse::STATUS_ERROR);
                        $response->SetCode(400);
                        $response->LoadErrorsFromObject($trip);

                        return true;
                    }
                }
                $response->SetStatus(ApiResponse::STATUS_ERROR);
                $response->SetCode(403);

                return true;
            }
        } elseif (count($path) == 2 && $path[1] == "finish") {
            if ($method == "PUT") {
                $device = new Device();
                $userID = $device->GetUserID($request->GetProperty("auth_device_id"));
                $user = new User();
                if ($user->LoadByID($userID) && $user->Validate(array("api")) && $request->IsPropertySet('trip_id')) {
                    $employee = new Employee($this->module);
                    if ($employee->LoadByUserID($userID)) {
                        if (Trip::FinishByEmployee($request->GetProperty('trip_id'))) {
                            $response->SetStatus(ApiResponse::STATUS_OK);
                            $response->SetCode(200);
                            $response->AddMessage('api-trip-finished-by-employee-success', $this->module);

                            return true;
                        }
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

