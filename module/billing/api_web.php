<?php

require_once(dirname(__FILE__) . "/../../include/init.php");

class ApiWebBillingProcessor extends ApiProcessor
{
    private $module;

    public function ApiWebBillingProcessor($module)
    {
        $this->module = $module;
    }

    public function Process($method, $path, LocalObject $request, ApiResponse $response)
    {
        if (count($path) == 1 && $path[0] == "payroll") {
            if ($method == "GET") {
                $device = new Device();
                $userID = $device->GetUserID($request->GetProperty("auth_device_id"));
                $user = new User();
                if ($user->LoadByID($userID) && $user->Validate(array("webapi"))) {
                    $payrollList = new PayrollList("company");
                    $request = new LocalObject(array(
                        "company_unit_id" => $request->GetProperty("company_unit_id"),
                        "FilterCreatedRange" => $request->GetProperty("date_from") . " - " . $request->GetProperty("date_to")
                    ));
                    $payrollList->LoadPayrollListForApi($request);

                    if (!Payroll::ValidateAccess(null, $userID, $request->GetProperty("company_unit_id"))) {
                        $payrollList->AddError("payroll-validation-failed", $this->module);
                    }

                    if (!$payrollList->HasErrors()) {
                        $response->SetStatus(ApiResponse::STATUS_OK);
                        $response->SetCode(200);
                        $response->SetData($payrollList->_items);

                        return true;
                    }

                    $response->AppendErrorsFromObject($payrollList);
                    $response->SetStatus(ApiResponse::STATUS_ERROR);
                    $response->SetCode(400);

                    return true;
                }
            }
            $response->SetStatus(ApiResponse::STATUS_ERROR);
            $response->SetCode(403);

            return true;
        } elseif (count($path) == 3 && $path[0] == "payroll" && $path[2] == "export") {
            if ($method == "GET") {
                $device = new Device();
                $userID = $device->GetUserID($request->GetProperty("auth_device_id"));
                $user = new User();
                if ($user->LoadByID($userID) && $user->Validate(array("webapi"))) {
                    if (!$request->IsPropertySet("datev_format")) {
                        $format = CompanyUnit::GetPropertyValue(
                            "datev_format",
                            $request->GetProperty("company_unit_id")
                        );
                    } else {
                        $format = $request->GetProperty("datev_format");
                    }

                    $payroll = new Payroll($this->module);

                    $key = "";
                    switch ($format) {
                        case 'pdf':
                            $key = "pdf_file";
                            break;
                        case 'lug':
                            $key = "lug_file";
                            break;
                        case 'lodas':
                            $key = "lodas_file";
                            break;
                        case 'logga':
                            $key = "logga_file";
                            break;
                        case 'topas':
                            $key = "topas_file";
                            break;
                        case 'perforce':
                            $key = "perforce_file";
                            break;
                        case 'addison':
                            $key = "addison_file";
                            break;
                        case 'lexware':
                            $key = "lexware_file";
                            break;
                        case 'sage':
                            $key = "sage_file";
                            break;
                        default:
                            $payroll->AddError("incorrect-payroll-file-format", $this->module);
                            break;
                    }

                    $payroll->LoadByID($path[1]);

                    if (!Payroll::ValidateAccess($request->GetProperty($path[1]), $userID)) {
                        $payroll->AddError("payroll-validation-failed", $this->module);
                    }

                    if (!$payroll->HasErrors()) {
                        $fileName = $payroll->GetProperty($key);
                        $filePath = PAYROLL_DIR . $fileName;

                        $response->SetStatus(ApiResponse::STATUS_OK);
                        $response->SetCode(200);
                        $response->SetData(OutputFile($filePath, $fileName, true));

                        return true;
                    }

                    $response->AppendErrorsFromObject($payroll);
                    $response->SetStatus(ApiResponse::STATUS_ERROR);
                    $response->SetCode(400);

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
