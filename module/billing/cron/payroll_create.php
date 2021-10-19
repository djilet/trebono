<?php

/**
 * Creates new payrolls. Should be run daily.
 */

set_time_limit(60 * 60 * 3);
ini_set('memory_limit', '3G');
require_once(dirname(__FILE__) . "/../../../include/init.php");
require_once(dirname(__FILE__) . "/../init.php");

$module = "billing";
$request = new LocalObject(array_merge($_GET, $_POST));

//define what day is it today and what companies should get payrolls
$date = $request->GetProperty("date") ?: date("Y-m-d");

$cronLog = "Started creating payrolls for $date.</br>";
$type = "payroll_create";
$countCreated = 0;
$countSent = 0;
$countExist = 0;
$errorList = "";
$operationID = Operation::SaveCron(null, $cronLog, $type, $errorList);

$companyUnitIDs = $request->GetProperty("company_unit_id")
    ? array($request->GetProperty("company_unit_id"))
    : $companyUnitIDs = CompanyUnitList::GetCompanyUnitIDsForPayrollCreation($date);

$cronLog .= "Payrolls are being created for " . count($companyUnitIDs) . " company units.</br>";
Operation::SaveCron($operationID, $cronLog, $type, $errorList);
Operation::SaveCronStatus($operationID, "Start generating");

foreach ($companyUnitIDs as $companyUnitID) {
    $companyUnit = new CompanyUnit("company");
    $companyUnit->LoadByID($companyUnitID);

    //if(strtotime($date) <= strtotime(date("Y-m-d")))
    //    ReceiptList::DenyOldReceipts($companyUnitID, $date);

    if (Payroll::PayrollExists($companyUnitID, $date)) {
        $countExist++;
        continue;
    }

    $payroll = new Payroll($module);
    $payrollID = $payroll->Create($companyUnitID, $date);

    if ($payrollID) {
        $countCreated++;

        $receiptList = new ReceiptList("receipt");

        //create payroll pdf document
        $pdfResult = $receiptList->ExportForInternalPurposes($companyUnitID, $date, $payrollID, $operationID);
        Operation::SaveCronStatus($operationID, "PDF generated for Company Unit: " . $companyUnitID);

        //create payroll datev document
        $datevResult = $receiptList->ExportToAddison($companyUnitID, $date, $payrollID, $operationID);
        Operation::SaveCronStatus($operationID, "DateV generated for Company Unit: " . $companyUnitID);

        $payrollToSend = new Payroll($module);
        $payrollToSend->LoadByID($payrollID);
        $countSent += $payrollToSend->Send();

        if ($payroll->HasErrors()) {
            $errorList .= $payroll->GetErrorsAsString("</br>");
        }
    } else {
        $errorList .= $payroll->GetErrorsAsString("</br>");
    }
}

$cronLog .= "Created $countCreated payrolls, $countSent copies were successfully sent. $countExist already existed.</br>";
Operation::SaveCron($operationID, $cronLog, $type, $errorList, null, true);
Operation::SaveCronStatus($operationID, "Complete generating");
