<?php

require_once(dirname(__FILE__) . "/../../include/init.php");

class ApiWebCompanyProcessor extends ApiProcessor
{
    private $module;

    public function ApiWebCompanyProcessor($module)
    {
        $this->module = $module;
    }

    public function Process($method, $path, LocalObject $request, ApiResponse $response)
    {
        if (count($path) == 1 && $path[0] == "company_unit") {
            if ($method == "POST") {
                $device = new Device();
                $userID = $device->GetUserID($request->GetProperty("auth_device_id"));
                $user = new User();
                if ($user->LoadByID($userID) && $user->Validate(array("webapi"))) {
                    $companyUnit = new CompanyUnit($this->module);
                    $companyUnit->AppendFromObject($request);
                    $companyUnit->SetProperty("created_from", "web_api");
                    if ($companyUnit->Save()) {
                        User::AddPermissionLink($userID, $companyUnit->GetProperty("company_unit_id"), "company_unit",
                            "web_api");

                        $response->SetStatus(ApiResponse::STATUS_OK);
                        $response->SetCode(200);
                        $response->SetData(array("company_unit_id" => $companyUnit->GetProperty("company_unit_id")));
                        return true;
                    } else {
                        $response->AppendErrorsFromObject($companyUnit);
                        $response->SetStatus(ApiResponse::STATUS_ERROR);
                        $response->SetCode(400);
                        return true;
                    }
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
        if (count($path) == 2 && $path[0] == "company_unit") {
            if ($method == "PUT") {
                $device = new Device();
                $userID = $device->GetUserID($request->GetProperty("auth_device_id"));
                $user = new User();
                if ($user->LoadByID($userID) && $user->Validate(array("webapi"))) {
                    $companyUnit = new CompanyUnit($this->module);

                    if (!$companyUnit->LoadByID($path[1])) {
                        $companyUnit->AddError("company-unit-not-found", $this->module);
                    } elseif (!CompanyUnit::ValidateAccess($path[1], $userID)) {
                        $companyUnit->AddError("company-unit-validation-failed", $this->module);
                    }

                    $companyUnit->AppendFromObject($request);
                    $companyUnit->SetProperty("created_from", "web_api");

                    if (!$companyUnit->HasErrors() && $companyUnit->Save()) {
                        $response->SetStatus(ApiResponse::STATUS_OK);
                        $response->SetCode(200);
                        $response->SetData($companyUnit->GetProperties());
                        return true;
                    } else {
                        $response->AppendErrorsFromObject($companyUnit);
                        $response->SetStatus(ApiResponse::STATUS_ERROR);
                        $response->SetCode(400);
                        return true;
                    }
                } else {
                    $response->SetStatus(ApiResponse::STATUS_ERROR);
                    $response->SetCode(400);
                    return true;
                }
            } elseif ($method == "DELETE") {
                $device = new Device();
                $userID = $device->GetUserID($request->GetProperty("auth_device_id"));
                $user = new User();
                if ($user->LoadByID($userID) && $user->Validate(array("webapi"))) {
                    $companyUnitList = new CompanyUnitList($this->module);
                    $companyUnit = new CompanyUnit($this->module);

                    if (!$companyUnit->LoadByID($path[1])) {
                        $companyUnitList->AddError("company-unit-not-found", $this->module);
                    } elseif (!CompanyUnit::ValidateAccess($path[1], $userID)) {
                        $companyUnitList->AddError("company-unit-validation-failed", $this->module);
                    }

                    $companyUnit->SetProperty("created_from", "web_api");

                    if (!$companyUnitList->HasErrors() && $companyUnitList->Remove(array($path[1]), "web_api")) {
                        $response->SetStatus(ApiResponse::STATUS_OK);
                        $response->SetCode(200);
                        $response->SetData("Company unit deactivated");
                        return true;
                    } else {
                        $response->AppendErrorsFromObject($companyUnitList);
                        $response->SetStatus(ApiResponse::STATUS_ERROR);
                        $response->SetCode(400);
                        return true;
                    }
                } else {
                    $response->SetStatus(ApiResponse::STATUS_ERROR);
                    $response->SetCode(400);
                    return true;
                }
            }
            $response->SetStatus(ApiResponse::STATUS_ERROR);
            $response->SetCode(403);
            return true;
        } elseif (count($path) == 1 && $path[0] == "contact") {
            if ($method == "POST") {
                $device = new Device();
                $userID = $device->GetUserID($request->GetProperty("auth_device_id"));
                $user = new User();
                if ($user->LoadByID($userID) && $user->Validate(array("webapi"))) {
                    $contact = new Contact($this->module);
                    $contact->AppendFromObject($request);
                    $contact->SetProperty("created_from", "web_api");

                    if (!CompanyUnit::ValidateAccess($request->GetProperty("company_unit_id"), $userID)) {
                        $contact->AddError("company-unit-validation-failed", $this->module);
                    }

                    if (!$contact->HasErrors() && $contact->Save()) {
                        $response->SetStatus(ApiResponse::STATUS_OK);
                        $response->SetCode(200);
                        $response->SetData(array("contact_id" => $contact->GetProperty("contact_id")));
                        return true;
                    } else {
                        $response->AppendErrorsFromObject($contact);
                        $response->SetStatus(ApiResponse::STATUS_ERROR);
                        $response->SetCode(400);
                        return true;
                    }
                }
            }
            $response->SetStatus(ApiResponse::STATUS_ERROR);
            $response->SetCode(403);
            return true;
        } elseif (count($path) == 2 && $path[0] == "contact") {
            if ($method == "PUT") {
                $device = new Device();
                $userID = $device->GetUserID($request->GetProperty("auth_device_id"));
                $user = new User();
                if ($user->LoadByID($userID) && $user->Validate(array("webapi"))) {
                    $contact = new Contact($this->module);

                    if (!$contact->LoadByID($path[1])) {
                        $contact->AddError("contact-not-found", $this->module);
                    } elseif (!CompanyUnit::ValidateAccess($request->GetProperty("company_unit_id"), $userID)) {
                        $contact->AddError("company-unit-validation-failed", $this->module);
                    }

                    $contact->AppendFromObject($request);
                    $contact->SetProperty("created_from", "web_api");

                    if (!$contact->HasErrors() && $contact->Save()) {
                        $response->SetStatus(ApiResponse::STATUS_OK);
                        $response->SetCode(200);
                        $response->SetData($contact->GetProperties());
                        return true;
                    } else {
                        $response->AppendErrorsFromObject($contact);
                        $response->SetStatus(ApiResponse::STATUS_ERROR);
                        $response->SetCode(400);
                        return true;
                    }
                }
            } elseif ($method == "DELETE") {
                $device = new Device();
                $userID = $device->GetUserID($request->GetProperty("auth_device_id"));
                $user = new User();
                if ($user->LoadByID($userID) && $user->Validate(array("webapi"))) {
                    $contactList = new ContactList($this->module);
                    $contact = new Contact($this->module);

                    if (!$contact->LoadByID($path[1])) {
                        $contactList->AddError("contact-not-found", $this->module);
                    } elseif (!CompanyUnit::ValidateAccess($contact->GetProperty("company_unit_id"), $userID)) {
                        $contactList->AddError("company-unit-validation-failed", $this->module);
                    }

                    if (!$contactList->HasErrors() && $contactList->Remove(array($path[1]))) {
                        $response->SetStatus(ApiResponse::STATUS_OK);
                        $response->SetCode(200);
                        $response->SetData("Contact person removed");
                        return true;
                    } else {
                        $response->AppendErrorsFromObject($contactList);
                        $response->SetStatus(ApiResponse::STATUS_ERROR);
                        $response->SetCode(400);
                        return true;
                    }
                }
            }
            $response->SetStatus(ApiResponse::STATUS_ERROR);
            $response->SetCode(403);
            return true;
        } elseif (count($path) == 1 && $path[0] == "employee") {
            if ($method == "POST") {
                $device = new Device();
                $userID = $device->GetUserID($request->GetProperty("auth_device_id"));
                $user = new User();
                if ($user->LoadByID($userID) && $user->Validate(array("webapi"))) {
                    $employee = new Employee($this->module);
                    $employee->AppendFromObject($request);
                    $employee->SetProperty("created_from", "web_api");

                    if (!CompanyUnit::ValidateAccess($request->GetProperty("company_unit_id"), $userID)) {
                        $employee->AddError("company-unit-validation-failed", $this->module);
                    }

                    if (!$employee->HasErrors() && $employee->Save()) {
                        User::AddPermissionLink($userID, $employee->GetProperty("company_unit_id"), "employee",
                            "web_api");

                        $response->SetStatus(ApiResponse::STATUS_OK);
                        $response->SetCode(200);
                        $response->SetData(array("employee_id" => $employee->GetProperty("employee_id")));
                        return true;
                    } else {
                        $response->AppendErrorsFromObject($employee);
                        $response->SetStatus(ApiResponse::STATUS_ERROR);
                        $response->SetCode(400);
                        return true;
                    }
                }
            }
            $response->SetStatus(ApiResponse::STATUS_ERROR);
            $response->SetCode(403);
            return true;
        } elseif (count($path) == 2 && $path[0] == "employee") {
            if ($method == "PUT") {
                $device = new Device();
                $userID = $device->GetUserID($request->GetProperty("auth_device_id"));
                $user = new User();
                if ($user->LoadByID($userID) && $user->Validate(array("webapi"))) {
                    $employee = new Employee($this->module);

                    if (!$employee->LoadByID($path[1])) {
                        $employee->AddError("employee-not-found", $this->module);
                    } elseif (!Employee::ValidateAccess($path[1], $userID)) {
                        $employee->AddError("employee-validation-failed", $this->module);
                    }

                    $employee->AppendFromObject($request);
                    $employee->SetProperty("created_from", "web_api");

                    if (!$employee->HasErrors() && $employee->Save()) {
                        $response->SetStatus(ApiResponse::STATUS_OK);
                        $response->SetCode(200);
                        $response->SetData($employee->GetProperties());
                        return true;
                    } else {
                        $response->AppendErrorsFromObject($employee);
                        $response->SetStatus(ApiResponse::STATUS_ERROR);
                        $response->SetCode(400);
                        return true;
                    }
                }
            } elseif ($method == "DELETE") {
                $device = new Device();
                $userID = $device->GetUserID($request->GetProperty("auth_device_id"));
                $user = new User();
                if ($user->LoadByID($userID) && $user->Validate(array("webapi"))) {
                    $employeeList = new EmployeeList($this->module);
                    $employee = new Employee($this->module);

                    if (!$employee->LoadByID($path[1])) {
                        $employeeList->AddError("employee-not-found", $this->module);
                    } elseif (!Employee::ValidateAccess($path[1], $userID)) {
                        $employeeList->AddError("employee-validation-failed", $this->module);
                    }


                    if (!$employeeList->HasErrors() && $employeeList->Remove(array($path[1]), "web_api")) {
                        $response->SetStatus(ApiResponse::STATUS_OK);
                        $response->SetCode(200);
                        $response->SetData("Employee deactivated");
                        return true;
                    } else {
                        $response->AppendErrorsFromObject($employeeList);
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

?>