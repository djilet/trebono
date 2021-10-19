<?php

require_once(dirname(__FILE__) . "/../../include/init.php");

class ApiReceiptProcessor extends ApiProcessor
{
    private $module;


    public function ApiReceiptProcessor($module)
    {
        $this->module = $module;
    }

    public function Process($method, $path, LocalObject $request, ApiResponse $response)
    {
        if (count($path) == 1 && $path[0] == "receipts") {
            if ($method == "GET") {
                $device = new Device();
                $userID = $device->GetUserID($request->GetProperty("auth_device_id"));

                $user = new User();
                if ($user->LoadByID($userID) && $user->Validate(["api"])) {
                    $employee = new Employee("company");
                    if ($employee->LoadByUserID($userID)) {
                        $receiptList = new ReceiptList($this->module);
                        $receiptList->LoadReceiptListForApi(
                            $employee->GetProperty("employee_id"),
                            $request->GetProperty("group_id"),
                            $request->GetProperty("status"),
                            $request->GetProperty("trip_id"),
                            $request->GetProperty("date_from"),
                            $request->GetProperty("date_to")
                        );
                        for ($i = 0; $i < $receiptList->GetCountItems(); $i++) {
                            $receiptFileList = new ReceiptFileList($this->module);
                            $receiptFileList->LoadFileList($receiptList->_items[$i]["receipt_id"]);
                            $receiptList->_items[$i]["file_list"] = $receiptFileList->GetItems();

                            $receiptLineList = new ReceiptLineList($this->module);
                            $receiptLineList->LoadLineList($receiptList->_items[$i]["receipt_id"]);
                            $receiptList->_items[$i]["line_list"] = $receiptLineList->GetItems();
                        }

                        $response->SetStatus(ApiResponse::STATUS_OK);
                        $response->SetCode(200);
                        $response->SetData($receiptList->GetItems());

                        return true;
                    }
                }

                $response->SetStatus(ApiResponse::STATUS_ERROR);
                $response->SetCode(403);

                return true;
            } elseif ($method == "POST") {
                $device = new Device();
                $userID = $device->GetUserID($request->GetProperty("auth_device_id"));
                $errorMessage = "CREATE";

                $user = new User();
                if ($user->LoadByID($userID) && $user->Validate(["api"])) {
                    $employee = new Employee("company");
                    if ($employee->LoadByUserID($userID)) {
                        $receipt = new Receipt($this->module);
                        $receipt->LoadFromObject($employee, ["employee_id", "user_id"]);

                        $productGroup = new ProductGroup("product");
                        if ($productGroup->LoadByID($request->GetProperty("group_id"))) {
                            $receipt->SetProperty("group_id", $request->GetProperty("group_id"));
                        } else {
                            $receipt->RemoveProperty("group_id");
                        }

                        if ($request->GetProperty("receipt_from")) {
                            $receiptTypeList = new ReceiptTypeList("product");
                            $receiptTypeList->LoadReceiptTypeListForProductGroup($receipt->GetProperty("group_id"));

                            if ($receiptTypeList->GetCountItems() > 0) {
                                if (
                                    !in_array(
                                        $request->GetProperty("receipt_from"),
                                        array_column($receiptTypeList->GetItems(), "code")
                                    )
                                ) {
                                    return false;
                                }

                                $receipt->SetProperty("receipt_from", $request->GetProperty("receipt_from"));
                            }
                        }

                        if ($request->GetProperty("trip_id")) {
                            $receipt->SetProperty("trip_id", $request->GetProperty("trip_id"));

                            $trip = new Trip("company");
                            $trip->LoadByID($request->GetProperty("trip_id"));

                            if ($trip->GetProperty("employee_id") != $employee->GetProperty("employee_id")) {
                                return false;
                            }

                            if (
                                $trip->GetProperty("finished_by_employee") == "Y" &&
                                $trip->GetProperty("finished_by_admin") == "Y"
                            ) {
                                $response->SetStatus(ApiResponse::STATUS_ERROR);
                                $response->SetCode(403);
                                $response->AddError("api-error-trip-finished", "company");

                                return true;
                            }
                        }

                        $groupID = ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__TRAVEL);
                        if ($receipt->GetProperty("group_id") == $groupID) {
                            if ($request->IsPropertySet("vat")) {
                                $receipt->SetProperty("vat", $request->GetProperty("vat"));
                            }
                            if ($request->GetProperty("currency_id")) {
                                $receipt->SetProperty("currency_id", $request->GetProperty("currency_id"));
                            }
                            if ($request->GetProperty("amount_approved")) {
                                $receipt->SetProperty("amount_approved", $request->GetProperty("amount_approved"));
                            }
                            if ($request->GetProperty("days_amount_over_16")) {
                                $receipt->SetProperty(
                                    "days_amount_over_16",
                                    $request->GetProperty("days_amount_over_16")
                                );
                            }
                            if ($request->GetProperty("days_amount_under_16")) {
                                $receipt->SetProperty(
                                    "days_amount_under_16",
                                    $request->GetProperty("days_amount_under_16")
                                );
                            }
                        }

                        $groupID = ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__RECREATION);
                        if ($receipt->GetProperty("group_id") == $groupID) {
                            if ($request->GetProperty("material_status")) {
                                $receipt->SetProperty("material_status", $request->GetProperty("material_status"));
                            }
                            if ($request->IsPropertySet("child_count")) {
                                $receipt->SetProperty("child_count", $request->GetProperty("child_count"));
                            }
                        }


                        $setsOfGoodsServices = [PRODUCT_GROUP__BENEFIT_VOUCHER, PRODUCT_GROUP__BONUS_VOUCHER];
                        if (
                            in_array(
                                ProductGroup::GetProductGroupCodeByID($receipt->GetProperty("group_id")),
                                $setsOfGoodsServices
                            )
                        ) {
                            $receipt->SetProperty("sets_of_goods", $request->GetProperty("sets_of_goods"));
                        }

                        $receipt->SetProperty("device_id", $request->GetProperty("auth_device_id"));

                        /* deny receipts right away, if employee is deactivated OR product's contract is deativatied */
                        $specificProductGroup = SpecificProductGroupFactory::Create($receipt->GetProperty("group_id"));
                        $productCode = $specificProductGroup->GetMainProductCode();

                        $contract = new Contract("contract");
                        $existProductContract = false;
                        $isVoucherService = false;

                        $existBaseContract = $contract->ContractExist(
                            OPTION_LEVEL_EMPLOYEE,
                            Product::GetProductIDByCode(PRODUCT__BASE__MAIN),
                            $employee->GetIntProperty('employee_id'),
                            GetCurrentDate()
                        );
                        if ($existBaseContract) {
                            $existProductContract = $contract->ContractExist(
                                OPTION_LEVEL_EMPLOYEE,
                                Product::GetProductIDByCode($productCode),
                                $employee->GetIntProperty('employee_id'),
                                GetCurrentDate()
                            );
                        }

                        if ($productGroup->GetProperty("voucher") == "Y") {
                            $isVoucherService = true;
                            $existOpenVoucher = false;
                            $voucherList = $specificProductGroup->GetReceiptMappedVoucherList(
                                $employee->GetProperty('employee_id')
                            );

                            foreach ($voucherList as $voucher) {
                                if (
                                    strtotime($voucher["end_date"]) >= strtotime(GetCurrentDate()) &&
                                    $voucher["amount_left"] > 0
                                ) {
                                    $existOpenVoucher = true;
                                    break;
                                }
                            }
                        }

                        if ($isVoucherService) {
                            if (!$existOpenVoucher && (!$existBaseContract || !$existProductContract)) {
                                $receipt->SetProperty(
                                    "denial_reason",
                                    Config::GetConfigValue('receipt_autodeny_no_active_contract_no_vouchers')
                                );
                                $receipt->SetProperty("status", "denied");
                            }
                        } else {
                            if (!$existBaseContract) {
                                $receipt->SetProperty(
                                    "denial_reason",
                                    Config::GetConfigValue('receipt_autodeny_employee_deactivated')
                                );
                                $receipt->SetProperty("status", "denied");
                            } elseif (!$existProductContract) {
                                $receipt->SetProperty(
                                    "denial_reason",
                                    Config::GetConfigValue('receipt_autodeny_no_active_contract')
                                );
                                $receipt->SetProperty("status", "denied");
                            }
                        }

                        if ($receipt->Create()) {
                            $response->SetStatus(ApiResponse::STATUS_OK);
                            $response->SetCode(201);
                            $response->SetData(["receipt_id" => $receipt->GetProperty("receipt_id")]);

                            return true;
                        }

                        $response->SetStatus(ApiResponse::STATUS_ERROR);
                        $response->SetCode(400);
                        $response->LoadErrorsFromObject($receipt);
                        ApiLog($errorMessage . "\n" . $response->GetErrorsAsString("\n"));

                        return true;
                    }
                }

                $response->SetStatus(ApiResponse::STATUS_ERROR);
                $response->SetCode(403);

                return true;
            }
        } elseif (count($path) == 2 && $path[0] == "receipts") {
            if ($method == "GET") {
                $device = new Device();
                $userID = $device->GetUserID($request->GetProperty("auth_device_id"));

                $user = new User();
                if ($user->LoadByID($userID) && $user->Validate(["api"])) {
                    $employee = new Employee("company");
                    if ($employee->LoadByUserID($userID)) {
                        $validateReceipt = new Receipt($this->module);
                        if (!$validateReceipt->LoadByID($path[1])) {
                            $response->SetStatus(ApiResponse::STATUS_ERROR);
                            $response->SetCode(404);

                            return true;
                        }

                        if ($validateReceipt->GetProperty("employee_id") != $employee->GetProperty("employee_id")) {
                            $response->SetStatus(ApiResponse::STATUS_ERROR);
                            $response->SetCode(403);

                            return true;
                        }

                        $receipt = new Receipt($this->module);
                        $receipt->LoadForApi($path[1]);

                        $receiptFileList = new ReceiptFileList($this->module);
                        $receiptFileList->LoadFileList($receipt->GetProperty("receipt_id"));
                        $receipt->SetProperty("file_list", $receiptFileList->GetItems());

                        $receiptLineList = new ReceiptLineList($this->module);
                        $receiptLineList->LoadLineList($receipt->GetProperty("receipt_id"));
                        $receipt->SetProperty("line_list", $receiptLineList->GetItems());

                        $specificProductGroup = SpecificProductGroupFactory::Create($receipt->GetProperty("group_id"));
                        if ($specificProductGroup) {
                            $specificProductGroup->AppendAdditionalInfo($receipt);
                        }

                        $response->SetStatus(ApiResponse::STATUS_OK);
                        $response->SetCode(200);
                        $response->SetData($receipt->GetProperties());

                        return true;
                    }
                }

                $response->SetStatus(ApiResponse::STATUS_ERROR);
                $response->SetCode(403);

                return true;
            } elseif ($method == "DELETE") {
                $device = new Device();
                $userID = $device->GetUserID($request->GetProperty("auth_device_id"));

                $errorMessage = "DELETE";
                $user = new User();
                if ($user->LoadByID($userID) && $user->Validate(["api"])) {
                    $employee = new Employee("company");
                    if ($employee->LoadByUserID($userID)) {
                        $errorMessage .= " Employee " . $employee->GetIntProperty("employee_id");
                        $receipt = new Receipt($this->module);
                        if (!$receipt->LoadByID($path[1])) {
                            $response->SetStatus(ApiResponse::STATUS_ERROR);
                            $response->SetCode(404);
                            ApiLog($errorMessage . " Wrong employee_id");

                            return true;
                        }

                        $errorMessage .= " Receipt " . $receipt->GetIntProperty("receipt_id");
                        if ($receipt->GetProperty("employee_id") != $employee->GetProperty("employee_id")) {
                            $response->SetStatus(ApiResponse::STATUS_ERROR);
                            $response->SetCode(403);
                            ApiLog($errorMessage . " No such receipt");

                            return true;
                        }

                        if ($receipt->RemoveReceiptData()) {
                            $errorMessage .= " Remove success";
                        } else {
                            $errorMessage .= "\n" . $receipt->GetErrorsAsString("\n");
                        }
                        $response->SetStatus(ApiResponse::STATUS_OK);
                        $response->SetCode(200);
                        ApiLog($errorMessage);

                        return true;
                    }
                }

                $response->SetStatus(ApiResponse::STATUS_ERROR);
                $response->SetCode(403);
                ApiLog($errorMessage . " User validation failed");

                return true;
            }
        } elseif (count($path) == 3 && $path[0] == "receipts" && $path[2] == "files") {
            if ($method == "POST") {
                $device = new Device();
                $userID = $device->GetUserID($request->GetProperty("auth_device_id"));
                $errorMessage = "POST";
                $user = new User();
                if ($user->LoadByID($userID) && $user->Validate(["api"])) {
                    $employee = new Employee("company");
                    if ($employee->LoadByUserID($userID)) {
                        $errorMessage .= " Employee " . $employee->GetIntProperty("employee_id");
                        $receipt = new Receipt($this->module);
                        if ($receipt->LoadByID($path[1])) {
                            $errorMessage .= " Receipt " . $receipt->GetIntProperty("receipt_id");
                            if ($receipt->GetProperty("employee_id") == $employee->GetProperty("employee_id")) {
                                $receiptFile = new ReceiptFile($this->module);
                                $receiptFile->SetProperty("receipt_id", $receipt->GetProperty("receipt_id"));
                                $receiptFile->SetProperty("hash", $request->GetProperty("hash"));
                                if ($receiptFile->Create($userID)) {
                                    //$receipt->CheckLimits("");

                                    $response->SetStatus(ApiResponse::STATUS_OK);
                                    $response->SetCode(201);
                                    $response->SetData([
                                        "receipt_file_id" => $receiptFile->GetProperty("receipt_file_id"),
                                        "message_list" => $receiptFile->GetMessagesAsString("\n"),
                                    ]);

                                    return true;
                                }

                                $response->SetStatus(ApiResponse::STATUS_ERROR);
                                $response->SetCode(400);
                                $response->LoadErrorsFromObject($receiptFile);
                                ApiLog($errorMessage . "\n" . $response->GetErrorsAsString("\n"));

                                return true;
                            }

                            $response->SetStatus(ApiResponse::STATUS_ERROR);
                            $response->SetCode(403);
                            ApiLog($errorMessage . " Wrong employee_id");

                            return true;
                        }

                        $response->SetStatus(ApiResponse::STATUS_ERROR);
                        $response->SetCode(404);
                        ApiLog($errorMessage . " No such receipt");

                        return true;
                    }
                }

                $response->SetStatus(ApiResponse::STATUS_ERROR);
                $response->SetCode(403);
                ApiLog($errorMessage . " User validation failed");

                return true;
            }
        } elseif (count($path) == 4 && $path[0] == "receipts" && $path[2] == "files") {
            if ($method == "DELETE") {
                $device = new Device();
                $userID = $device->GetUserID($request->GetProperty("auth_device_id"));

                $user = new User();
                if ($user->LoadByID($userID) && $user->Validate(["api"])) {
                    $employee = new Employee("company");
                    if ($employee->LoadByUserID($userID)) {
                        $receipt = new Receipt($this->module);
                        if ($receipt->LoadByID($path[1])) {
                            if ($receipt->GetProperty("employee_id") == $employee->GetProperty("employee_id")) {
                                $receiptFile = new ReceiptFile($this->module);
                                if ($receiptFile->LoadByID($path[3])) {
                                    if ($receiptFile->GetProperty("receipt_id") == $receipt->GetProperty("receipt_id")) {
                                        $receiptFileList = new ReceiptFileList($this->module);
                                        $receiptFileList->Remove([$path[3]]);

                                        $response->SetStatus(ApiResponse::STATUS_OK);
                                        $response->SetCode(200);

                                        return true;
                                    }

                                    $response->SetStatus(ApiResponse::STATUS_ERROR);
                                    $response->SetCode(400);

                                    return true;
                                }

                                $response->SetStatus(ApiResponse::STATUS_ERROR);
                                $response->SetCode(404);

                                return true;
                            }

                            $response->SetStatus(ApiResponse::STATUS_ERROR);
                            $response->SetCode(403);

                            return true;
                        }

                        $response->SetStatus(ApiResponse::STATUS_ERROR);
                        $response->SetCode(404);

                        return true;
                    }
                }

                $response->SetStatus(ApiResponse::STATUS_ERROR);
                $response->SetCode(403);

                return true;
            }
        } elseif (count($path) == 3 && $path[0] == "receipts" && $path[2] == "comments") {
            if ($method == "GET") {
                $device = new Device();
                $userID = $device->GetUserID($request->GetProperty("auth_device_id"));

                $user = new User();
                if ($user->LoadByID($userID) && $user->Validate(["api"])) {
                    $employee = new Employee("company");
                    if ($employee->LoadByUserID($userID)) {
                        $receipt = new Receipt($this->module);
                        if ($receipt->LoadByID($path[1])) {
                            if ($receipt->GetProperty("employee_id") == $employee->GetProperty("employee_id")) {
                                $receiptCommentList = new ReceiptCommentList($this->module);
                                $receiptCommentList->LoadCommentListForApi($receipt->GetProperty("receipt_id"));

                                $response->SetStatus(ApiResponse::STATUS_OK);
                                $response->SetCode(200);
                                $response->SetData($receiptCommentList->GetItems());

                                return true;
                            }

                            $response->SetStatus(ApiResponse::STATUS_ERROR);
                            $response->SetCode(403);

                            return true;
                        }

                        $response->SetStatus(ApiResponse::STATUS_ERROR);
                        $response->SetCode(404);

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
                if ($user->LoadByID($userID) && $user->Validate(["api"])) {
                    $employee = new Employee("company");
                    if ($employee->LoadByUserID($userID)) {
                        $receipt = new Receipt($this->module);
                        if (!$receipt->LoadByID($path[1])) {
                            $response->SetStatus(ApiResponse::STATUS_ERROR);
                            $response->SetCode(404);

                            return true;
                        }

                        if ($receipt->GetProperty("employee_id") != $employee->GetProperty("employee_id")) {
                            $response->SetStatus(ApiResponse::STATUS_ERROR);
                            $response->SetCode(403);

                            return true;
                        }

                        $receiptComment = new ReceiptComment($this->module);
                        $receiptComment->LoadFromObject($request);
                        $receiptComment->SetProperty("receipt_id", $receipt->GetProperty("receipt_id"));
                        $receiptComment->SetProperty("user_id", $user->GetProperty("user_id"));
                        if ($receiptComment->Create()) {
                            $response->SetStatus(ApiResponse::STATUS_OK);
                            $response->SetCode(201);
                        } else {
                            $response->SetStatus(ApiResponse::STATUS_ERROR);
                            $response->SetCode(400);
                            $response->LoadErrorsFromObject($receiptComment);
                        }

                        return true;
                    }
                }

                $response->SetStatus(ApiResponse::STATUS_ERROR);
                $response->SetCode(403);

                return true;
            }
        } elseif (count($path) == 2 && $path[1] == "approve") {
            if ($method == "PUT") {
                $receiptId = abs(intval($path[0]));
                if ($receiptId == 0) {
                    $response->SetStatus(ApiResponse::STATUS_ERROR);
                    $response->SetCode(400);

                    return true;
                }

                $device = new Device();
                $userID = $device->GetUserID($request->GetProperty("auth_device_id"));

                $user = new User();
                if (!$user->LoadByID($userID) || !$user->Validate(["api"])) {
                    $response->SetStatus(ApiResponse::STATUS_ERROR);
                    $response->SetCode(400);

                    return true;
                }

                $employee = new Employee("company");
                if (!$employee->LoadByUserID($userID)) {
                    $response->SetStatus(ApiResponse::STATUS_ERROR);
                    $response->SetCode(400);

                    return true;
                }

                $receipt = new Receipt($this->module);
                if (!$receipt->LoadForApi($receiptId)) {
                    $response->SetStatus(ApiResponse::STATUS_ERROR);
                    $response->SetCode(400);

                    return true;
                }

                if ($receipt->ApproveByEmployee()) {
                    $groupCode = ProductGroup::GetProductGroupCodeByID($receipt->GetIntProperty("group_id"));
                    $dailyAllowance = (
                        $groupCode == PRODUCT_GROUP__TRAVEL &&
                        $receipt->GetProperty("receipt_from") == "meal" &&
                        Option::GetInheritableOptionValue(
                            OPTION_LEVEL_EMPLOYEE,
                            OPTION__TRAVEL__MAIN__FIXED_DAILY_ALLOWANCE,
                            $receipt->GetIntProperty("employee_id"),
                            $receipt->GetProperty("document_date")
                        ) == "Y"
                    );
                    $message = $dailyAllowance
                        ? $receipt->GetProperty("group_code") . '-api-receipt_approve_by_employee_success-daily-allowance'
                        : $receipt->GetProperty("group_code") . '-api-receipt_approve_by_employee_success';
                    $response->SetStatus(ApiResponse::STATUS_OK);
                    $response->SetCode(200);
                    $response->AddMessage($message, 'product');

                    return true;
                }

                $response->SetStatus(ApiResponse::STATUS_ERROR);
                $response->SetCode(400);
                $response->AppendErrorsFromObject($receipt);

                return true;
            }

            $response->SetStatus(ApiResponse::STATUS_ERROR);
            $response->SetCode(403);

            return true;
        } elseif (count($path) == 2 && $path[1] == "denied") {
            if ($method == "PUT") {
                $receiptId = abs(intval($path[0]));
                if ($receiptId == 0) {
                    $response->SetStatus(ApiResponse::STATUS_ERROR);
                    $response->SetCode(400);

                    return true;
                }

                $device = new Device();
                $userID = $device->GetUserID($request->GetProperty("auth_device_id"));

                $user = new User();
                if (!$user->LoadByID($userID) || !$user->Validate(["api"])) {
                    $response->SetStatus(ApiResponse::STATUS_ERROR);
                    $response->SetCode(400);

                    return true;
                }

                $employee = new Employee("company");
                if (!$employee->LoadByUserID($userID)) {
                    $response->SetStatus(ApiResponse::STATUS_ERROR);
                    $response->SetCode(400);

                    return true;
                }

                $receipt = new Receipt($this->module);
                if (!$receipt->LoadForApi($receiptId)) {
                    $response->SetStatus(ApiResponse::STATUS_ERROR);
                    $response->SetCode(400);

                    return true;
                }

                if ($receipt->DeniedByEmployee()) {
                    $response->SetStatus(ApiResponse::STATUS_OK);
                    $response->SetCode(200);

                    return true;
                }

                $response->SetStatus(ApiResponse::STATUS_ERROR);
                $response->SetCode(400);
                $response->AppendErrorsFromObject($receipt);

                return true;
            }

            $response->SetStatus(ApiResponse::STATUS_ERROR);
            $response->SetCode(403);

            return true;
        } elseif (count($path) == 1 && $path[0] == "currency") {
            if ($method == "GET") {
                $device = new Device();
                $userID = $device->GetUserID($request->GetProperty("auth_device_id"));

                $user = new User();
                if ($user->LoadByID($userID) && $user->Validate(["api"])) {
                    $currencyList = new CurrencyList($this->module);
                    $currencyList->LoadCurrencyList(null);

                    $response->SetStatus(ApiResponse::STATUS_OK);
                    $response->SetCode(200);
                    $response->SetData($currencyList->GetItems());

                    return true;
                }

                $response->SetStatus(ApiResponse::STATUS_ERROR);
                $response->SetCode(403);

                return true;
            }
        }

        return false;
    }
}
