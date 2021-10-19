<?php
/**
 * Integration with givve vouchers. Should be runned every night(?)
 */
/*set_time_limit(60*60);

define("IS_ADMIN", true);

require_once(dirname(__FILE__)."/../../../include/init.php");

$module = "company";

$import = new GivveTransactionImport();

$transactionList = new GivveTransactionList($module);
$transaction = new GivveTransaction($module);
$voucher = new GivveVoucher($module);

$employeeList = EmployeeList::GetEmployeeListGivveLogin();

foreach ($employeeList as $employee)
{
    $employeeData = array();
    $employeeData["identifier"] = $employee["givve_login"];
    $employeeData["password"] = $employee["givve_password"];
    $employeeData["accessors"] = array();
    $employeeData["accessors"][]= "multiplier"; //TODO customer

    $dataString = json_encode($employeeData);
    $result = $import->GetAccessToken($dataString);

    Employee::SetGivveAccessToken($result["data"]["access_token"], $result["data"]["refresh_token"], $employee["employee_id"]);
}

$employeeList = EmployeeList::GetEmployeeListGivveToken();

foreach ($employeeList as $employee)
{
    $result = $import->GetVoucherList($employee["givve_access_token"]);
    //if access denied, try to refresh access token
    if (isset($result["code"]) && $result["code"] == "token_expired")
    {
        $refresh = array();
        $refresh["identifier"] = $employee["givve_refresh_token"];

        $newToken = $import->GetAccessToken(json_encode($refresh));

        Employee::SetGivveAccessToken($newToken["data"]["access_token"], $newToken["data"]["refresh_token"], $employee["employee_id"]);
        $result = $import->GetVoucherList($newToken["data"]["access_token"]);
    }

    if (isset($result["data"]))
    {
        $voucherList = $result["data"];
        foreach ($voucherList as $newVoucher)
        {
            $result = $import->GetVoucher($newVoucher["id"], $employee["givve_access_token"]);
            if (!isset($result["data"]))
                continue;

            $newVoucher = $result["data"];
            $newVoucher['employee_id'] = $employee["employee_id"];

            //check if it's new voucher. if it is, save it
            if (!$voucher->LoadByImportData($newVoucher["id"], $employee["employee_id"]))
            {
                $voucher->SaveFromImportData($newVoucher);
            }

            $result = $import->GetTransactionList($newVoucher['id'], $employee["givve_access_token"]);
            if (isset($result["data"]))
            {
                $transactionList = $result["data"];
                foreach ($transactionList as $newTrasaction)
                {
                    $newTrasaction["voucher_id"] = $newVoucher["id"];
                    //check if it's new transaction. if it is, save it
                    if (!$transaction->LoadByImportData($newTrasaction["id"], $newVoucher["id"]))
                    {
                        $transaction->SaveFromImportData($newTrasaction);
                    }
                }
            }
        }
    }
}*/