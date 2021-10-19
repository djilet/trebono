<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once(dirname(__FILE__)."/../../include/init.php");
require_once(dirname(__FILE__)."/../../include/user.php");
es_include("localobject.php");

final class PayrollClass extends TestCase
{
    public static $moduleBilling = "billing";
    public static $moduleCompany = "company";
    public static $moduleProduct = "product";
    public static $moduleReceipt = "receipt";

    public function testConstructor()
    {
        $payroll = new Payroll(self::$moduleBilling, array("Key" => "Value"));
        $this->assertInstanceOf(Payroll::class, $payroll);
        $this->assertEquals("Value", $payroll->GetProperty("Key"));
    }

    public function testCreate()
    {
        //create company unit
        $data = array(
            "title" => "company_unit_auto_payroll",
            "agreement_enable" => "N",
            "colorscheme" => "test_color_scheme",
            "payroll_month" => "last_month",
            "financial_statement_date" => date("d")
        );
        $companyUnit = new CompanyUnit(self::$moduleCompany);
        $companyUnit->LoadFromArray($data);
        $this->assertTrue($companyUnit->Save(),"company unit errors: ".$companyUnit->GetErrorsAsString("</br>"));

        if ($companyUnit->GetProperty("payroll_month") == "last_month")
            $payrollDate = date('d.m.Y', strtotime("+1 months"));
        else
            $payrollDate = date('d.m.Y');

        //create employee
        $data = array(
            "company_unit_id" => $companyUnit->GetProperty("company_unit_id"),
            "first_name" => "for_payroll",
            "last_name" => "test_employee",
            "email" => "employee_auto_payroll@test.com",
            "working_days_per_week" => 5,
            "password1" => "12345",
            "password2" => "12345"
        );

        $employee = new Employee(self::$moduleCompany);
        $employee->LoadFromArray($data);
        $this->assertTrue($employee->Save(),"employee errors: ".$employee->GetErrorsAsString("</br>"));

        //will need user in session
        $session =& GetSession();
        $request = new LocalObject(array(
            "email" => "user_auto_payroll@test.com",
            "first_name" => "Tester",
            "last_name" => "Tester",
            "password1" => "12345",
            "password2" => "12345",
            "PermissionIDs" => array(1),
        ));
        $user = new User();
        $user->AppendFromObject($request);

        $this->assertTrue($user->Save(-1),"user errors: ".$user->GetErrorsAsString("</br>"));

        $user->LoadPermissions();
        $session->SetProperty("LoggedInUser", $user->GetProperties());

        //create receipts
        $contract = new Contract(self::$moduleProduct);

        $productGroupList = new ProductGroupList(self::$moduleProduct);
        $productGroupList->LoadProductGroupListForAdmin($employee);

        $receiptDataList = array();

        $option = new Option(self::$moduleProduct);
        $optionList = new OptionList(self::$moduleProduct);

        $noNeedForReceipts = array (PRODUCT_GROUP__BASE, PRODUCT_GROUP__AD, PRODUCT_GROUP__GIVVE, PRODUCT_GROUP__TRAVEL);
        foreach ($productGroupList->_items as $productGroup)
        {
            //creating contracts
            $productListObject = new ProductList(self::$moduleProduct);
            $productListObject->LoadProductListForAdmin($productGroup['group_id']);
            foreach ($productListObject->_items as $product)
            {
                if ($product["code"] == PRODUCT__FOOD__LUMP_SUM_TAX_EXAMINATION)
                    continue;

                $this->assertTrue($contract->OnOptionUpdate(OPTION_LEVEL_COMPANY_UNIT, $product["product_id"], $employee->GetProperty("company_unit_id"), null, date('1.m.Y', strtotime("-1 months")), null));
                $this->assertTrue($contract->ContractExist(OPTION_LEVEL_COMPANY_UNIT, $product["product_id"], $employee->GetProperty("company_unit_id"), date('1.m.Y', strtotime("-1 months"))));

                $this->assertTrue($contract->OnOptionUpdate(OPTION_LEVEL_EMPLOYEE, $product["product_id"], $employee->GetProperty("employee_id"), null, date('1.m.Y', strtotime("-1 months")), null));
                $this->assertTrue($contract->ContractExist(OPTION_LEVEL_EMPLOYEE, $product["product_id"], $employee->GetProperty("employee_id"), date('1.m.Y', strtotime("-1 months"))));

                //set options required for receipt creation
                $optionList->LoadOptionListForAdmin($product["product_id"], OPTION_LEVEL_COMPANY_UNIT);
                foreach ($optionList->_items as $companyUnitOption)
                {
                    if ($companyUnitOption["group_code"] == "limits_for_units" || ($companyUnitOption["group_code"] == "special_values" && $companyUnitOption["type"] == "currency")
                        || $companyUnitOption["code"] == OPTION__GIFT__MAIN__QTY_PER_YEAR || $companyUnitOption["code"] == OPTION__GIFT__MAIN__AMOUNT_PER_VOUCHER)
                    {
                        $this->assertTrue($option->SaveOptionValue(OPTION_LEVEL_COMPANY_UNIT, $companyUnitOption["option_id"], 2, $employee->GetProperty("company_unit_id"), date('1.m.Y', strtotime("-1 months"))));
                    }
                    elseif ($companyUnitOption["code"] == OPTION__BENEFIT__MAIN__RECEIPT_OPTION)
                    {
                        $this->assertTrue($option->SaveOptionValue(OPTION_LEVEL_COMPANY_UNIT, $companyUnitOption["option_id"], "monthly", $employee->GetProperty("company_unit_id"), date('1.m.Y', strtotime("-1 months"))));
                    }
                }
            }

            $today = date("d");
            //create one receipt for recreation product and two receipts for others except for main base, ad, travel and givve
            if ($productGroup["code"] == PRODUCT_GROUP__RECREATION ||
                ($productGroup["code"] == PRODUCT_GROUP__FOOD && $today == 1))
                $receiptCount = 1;
            else
                $receiptCount = 2;
            if (!in_array($productGroup["code"], $noNeedForReceipts))
            {
                for ($i = 0, $receiptIDs = array(), $receiptList = array(); $i < $receiptCount; $i++)
                {
                    //creating vouchers for bonus and gifts
                    if ($productGroup["code"] == PRODUCT_GROUP__BONUS || $productGroup["code"] == PRODUCT_GROUP__GIFT)
                    {
                        $voucher = new Voucher(self::$moduleCompany);
                        $data = array(
                            "group_id" => $productGroup["group_id"],
                            "employee_id" => $employee->GetProperty("employee_id"),
                            "amount" => 1,
                            "voucher_date" => date('d.m.Y', strtotime("- ".($i + 1)." days")),
                            "end_date" => date('d.m.Y', strtotime("+ 1 years")),
                            "reason" => "Year bonus",
                            "recurring" => "N"
                        );

                        $voucher->AppendFromArray($data);
                        $this->assertTrue($voucher->Save(), "voucher errors: ".$voucher->GetErrorsAsString("</br>"));
                    }

                    //receipt_from for food product
                    if ($productGroup["code"] == PRODUCT_GROUP__FOOD)
                        $receipt_from = "shop";
                    else
                        $receipt_from = "";

                    //booked for travel product
                    if ($productGroup["code"] == PRODUCT_GROUP__TRAVEL)
                    {
                        $tripID = "1";
                        $booked = "Y";
                    }
                    else
                    {
                        $tripID = NULL;
                        $booked = "N";
                    }

                    $receipt = new Receipt(self::$moduleProduct);

                    $data = array(
                        "employee_id" => $employee->GetIntProperty("employee_id"),
                        "group_id" => $productGroup["group_id"],
                        "user_id" => $employee->GetIntProperty("user_id"),
                        "trip_id" => $tripID
                    );
                    $receipt->LoadFromArray($data);
                    $this->assertTrue($receipt->Create(), "receipt errors: ".$receipt->GetErrorsAsString("</br>"));

                    if ($today <= 2)
                        $documentDate = date('d.m.Y', strtotime("-".$i." days"));
                    else
                        $documentDate = date('d.m.Y', strtotime("-".($i + 1)." days"));
                    if ($productGroup["code"] == PRODUCT_GROUP__FOOD)
                    {
                        $weekDay = date("w", strtotime($documentDate));
                        $workingDaysPerWeek = 5;
                        //rule from food service - move receipts from weekends to next monday
                        if(($weekDay == 0 ? 7: $weekDay) > $workingDaysPerWeek)
                            $documentDate = date('d.m.Y');
                    }

                    $data = array(
                        "status" => "approve_proposed",
                        "amount_approved" => 1,
                        "document_date" => $documentDate,
                        "created" => GetCurrentDateTime(),
                        "receipt_id" => $receipt->GetProperty("receipt_id"),
                        "receipt_from" => $receipt_from,
                        "booked" => $booked,
                        "document_guid" => $receipt->GetProperty("receipt_id")
                    );
                    $receipt->AppendFromArray($data);
                    $this->assertTrue($receipt->Update(), "receipt errors: ".$receipt->GetErrorsAsString("</br>"));

                    $data = array(
                        "status" => "approved"
                    );
                    $receipt->AppendFromArray($data);

                    $this->assertTrue($receipt->Update(), "receipt errors: ".$receipt->GetErrorsAsString("</br>"));

                    Receipt::SetLegalReceiptID($receipt->GetProperty("receipt_id"));
                    $receiptIDs[] = $receipt->GetProperty("receipt_id");
                    $receiptList[] = $receipt->GetProperties();
                }
                $receiptDataList[] = array("group_id" => $productGroup["group_id"], "receipt_count" => count($receiptIDs), "receipt_ids" => $receiptIDs, "receipt_list" => $receiptList);
            }
        }
        //create payroll
        $payroll = new Payroll(self::$moduleBilling);
        $this->assertNotFalse($payroll->Create($employee->GetProperty("company_unit_id"), $payrollDate));
        $payrollID = $payroll->GetProperty("payroll_id");
        //get line list for payroll PDF
        $receiptList = new ReceiptList(self::$moduleReceipt);
        $content = $receiptList->ExportForInternalPurposes($employee->GetProperty("company_unit_id"), $payrollDate, $payrollID, false, true);

        $this->assertNotFalse($employeeKey = array_search("test_employee", array_column($content, "last_name")));
        $payrollLines = $content[0]["product_group_list"];
        //compare results of receipts creation with lines from PDF
        for ($i = 0; $i < count($receiptDataList); $i++)
        {
            $groupID = $receiptDataList[$i]["group_id"];
            $this->assertEquals($groupID, $payrollLines[$groupID]["group_id"]);
            $this->assertEquals($receiptDataList[$i]["receipt_count"], $payrollLines[$groupID]["receipt_count"], "product group ".$groupID." receipt count not equal".". receipt ids in payroll - ".implode(" ", $payrollLines[$groupID]["receipt_ids"]).", expected ids - ".implode(" ", $receiptDataList[$i]["receipt_ids"]));
            for ($j = 0; $j < count($receiptDataList[$i]["receipt_ids"]); $j++)
            {
                $this->assertTrue(in_array($receiptDataList[$i]["receipt_ids"][$j], array_column($payrollLines[$groupID]["receipt_ids"], "receipt_id")), "product group ".$groupID.": receipt id ".$receiptDataList[$i]["receipt_ids"][$j]." not in ".implode(" ", $payrollLines[$groupID]["receipt_ids"]));
            }
        }
    }
}
?>