<?php

define("IS_ADMIN", true);
require_once(dirname(__FILE__) . "/../include/init.php");

$auth = new User();
$auth->ValidateAccess(["root"]);

$request = new LocalObject(array_merge($_GET, $_POST));

$fileList = [];

$container = $request->GetProperty("container");

switch (true) {
    case strpos($container, "billing__invoice") === 0:
        $to = CONTAINER__BILLING__INVOICE;
        $invoiceList = new InvoiceList("billing");
        $invoiceList->LoadInvoiceListForAdmin(new LocalObject(), true);
        $itemList = $invoiceList->GetItems();
        foreach ($itemList as $invoice) {
            $companyUnit = new CompanyUnit("company");
            $companyUnit->LoadByID($invoice["company_unit_id"]);

            $fileList[] = INVOICE_DIR . date(
                "ym",
                strtotime($invoice["created"])
            ) . "_" . $companyUnit->GetProperty("customer_guid") . "_" . $invoice["invoice_guid"] . ".pdf";
        }
        break;
    case strpos($container, "agreements") === 0:
        $to = CONTAINER__AGREEMENTS;

        $stmt = GetStatement();
        $query = "SELECT file FROM agreements_employee";
        $agreementList = $stmt->FetchList($query);
        if ($agreementList) {
            foreach ($agreementList as $agreement) {
                $fileList[] = AGREEMENTS_DIR . $agreement["file"];
            }
        }
        break;
    case strpos($container, "billing__payroll") === 0:
        $to = CONTAINER__BILLING__PAYROLL;
        $payrollList = new PayrollList("billing");
        $payrollList->LoadPayrollList(new LocalObject(), true);
        $itemList = $payrollList->GetItems();
        foreach ($itemList as $payroll) {
            $fileList[] = PAYROLL_DIR . $payroll["pdf_file"];

            $fileList[] = PAYROLL_DIR . $payroll["lug_file"];
            $fileList[] = PAYROLL_DIR . $payroll["lodas_file"];
            $fileList[] = PAYROLL_DIR . $payroll["logga_file"];
            $fileList[] = PAYROLL_DIR . $payroll["topas_file"];
            $fileList[] = PAYROLL_DIR . $payroll["addison_file"];
            $fileList[] = PAYROLL_DIR . $payroll["lexware_file"];
        }
        break;
    case strpos($container, "company") === 0:
        $to = CONTAINER__COMPANY;

        $stmt = GetStatement();
        $query = "SELECT app_logo_image, app_logo_mini_image, voucher_logo_image FROM company_unit";
        $companyUnitList = $stmt->FetchList($query);
        if ($companyUnitList) {
            foreach ($companyUnitList as $companyUnit) {
                if (!empty($companyUnit["app_logo_image"])) {
                    $fileList[] = COMPANY_IMAGE_DIR . $companyUnit["app_logo_image"];
                }
                if (!empty($companyUnit["app_logo_mini_image"])) {
                    $fileList[] = COMPANY_IMAGE_DIR . $companyUnit["app_logo_mini_image"];
                }
                if (empty($companyUnit["voucher_logo_image"])) {
                    continue;
                }

                $fileList[] = COMPANY_IMAGE_DIR . $companyUnit["voucher_logo_image"];
            }
        }

        $query = "SELECT company_image FROM company";
        $companyList = $stmt->FetchList($query);
        if ($companyList) {
            foreach ($companyList as $company) {
                if (empty($company["company_image"])) {
                    continue;
                }

                $fileList[] = COMPANY_IMAGE_DIR . $company["company_image"];
            }
        }

        $query = "SELECT file FROM voucher";
        $voucherList = $stmt->FetchList($query);
        if ($voucherList) {
            foreach ($voucherList as $voucher) {
                if (empty($voucher["file"])) {
                    continue;
                }

                $fileList[] = COMPANY_VOUCHER_DIR . $voucher["file"];
            }
        }
        break;
    case strpos($container, "partner") === 0:
        $to = CONTAINER__PARTNER;
        $commisionList = new CommissionList("partner");
        $commisionList->LoadCommissionList(new LocalObject(), true);
        $itemList = $commisionList->GetItems();
        foreach ($itemList as $commision) {
            $fileList[] = REPORT_DIR . $commision["ExportFile"];
        }
        break;
    case strpos($container, "product"):
        $to = CONTAINER__PRODUCT;
        $productGroupList = new ProductGroupList("product");
        $productGroupList->LoadProductGroupListForAdmin();
        $itemList = $productGroupList->GetItems();
        foreach ($itemList as $productGroup) {
            $fileList[] = PRODUCT_IMAGE_DIR . $productGroup["product_group_image"];
        }
        break;


    case strpos($container, "receipt__") === 0:
        $containerExplode = explode("__", $container);
        switch ($containerExplode[1]) {
            case "ad":
                $to = CONTAINER__RECEIPT__AD;
                $groupID = ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__AD);
                break;
            case "base":
                $to = CONTAINER__RECEIPT__BASE;
                $groupID = ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__BASE);
                break;
            case "benefit":
                $to = CONTAINER__RECEIPT__BENEFIT;
                $groupID = ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__BENEFIT);
                break;
            case "benefit_voucher":
                $to = CONTAINER__RECEIPT__BENEFIT_VOUCHER;
                $groupID = ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__BENEFIT_VOUCHER);
                break;
            case "bonus":
                $to = CONTAINER__RECEIPT__BONUS;
                $groupID = ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__BONUS);
                break;
            case "child_care":
                $to = CONTAINER__RECEIPT__CHILD_CARE;
                $groupID = ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__CHILD_CARE);
                break;
            case "food":
                $to = CONTAINER__RECEIPT__FOOD;
                $groupID = ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__FOOD);
                break;
            case "food_voucher":
                $to = CONTAINER__RECEIPT__FOOD_VOUCHER;
                $groupID = ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__FOOD_VOUCHER);
                break;
            case "gift":
                $to = CONTAINER__RECEIPT__GIFT;
                $groupID = ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__GIFT);
                break;
            case "gift_voucher":
                $to = CONTAINER__RECEIPT__GIFT_VOUCHER;
                $groupID = ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__GIFT_VOUCHER);
                break;
            case "givve":
                $to = CONTAINER__RECEIPT__GIVVE;
                $groupID = ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__GIVVE);
                break;
            case "internet":
                $to = CONTAINER__RECEIPT__INTERNET;
                $groupID = ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__INTERNET);
                break;
            case "mobile":
                $to = CONTAINER__RECEIPT__MOBILE;
                $groupID = ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__MOBILE);
                break;
            case "recreation":
                $to = CONTAINER__RECEIPT__RECREATION;
                $groupID = ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__RECREATION);
                break;
            case "transport":
                $to = CONTAINER__RECEIPT__TRANSPORT;
                $groupID = ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__TRANSPORT);
                break;
            case "travel":
                $to = CONTAINER__RECEIPT__TRAVEL;
                $groupID = ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__TRAVEL);
                break;
            case "corporate_health_management":
                $to = CONTAINER__RECEIPT__CORPORATE_HEALTH_MANAGEMENT;
                $groupID = ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__CORPORATE_HEALTH_MANAGEMENT);
                break;
            default:
                print_r("wrong service");
                continue 2;
        }
        $stmt = GetStatement();
        $receiptList = new ReceiptList("receipt");
        $receiptList->LoadReceiptListForAdmin(new LocalObject(["FilterProductGroup" => $groupID]), true);
        foreach ($receiptList->GetItems() as $receipt) {
            $receiptFileList = new ReceiptFileList("receipt");
            $receiptFileList->LoadFileList($receipt["receipt_id"]);
            $itemList = $receiptFileList->GetItems();
            foreach ($itemList as $receiptFile) {
                $fileList[] = RECEIPT_IMAGE_DIR . "file/" . $receiptFile["file_image"];
                if (!empty($company["signature_file"])) {
                    $fileList[] = RECEIPT_IMAGE_DIR . "file/" . $receiptFile["signature_file"];
                }
                if (empty($company["signature_report_file"])) {
                    continue;
                }

                $fileList[] = RECEIPT_IMAGE_DIR . "file/" . $receiptFile["signature_report_file"];
            }

            $query = "SELECT comment_file FROM receipt_comment WHERE receipt_id=" . intval($receipt["receipt_id"]);
            $commentList = $stmt->FetchList($query);
            if (!$commentList) {
                continue;
            }

            foreach ($commentList as $comment) {
                if (empty($comment["comment_file"])) {
                    continue;
                }

                $fileList[] = RECEIPT_IMAGE_DIR . "comment/" . $comment["comment_file"];
            }
        }
        break;
    default:
        print_r("wrong container");
        break;
}

//$fileStorage = GetFileStorage(CONTAINER__CORE);
//$content = $fileStorage->GetFileContent($fileList[14]);
//$fileSys = new FileSys();
//$fileSys->PutFileContent($fileList[14], $content);
//print_r($fileSys);
//$fileStorage->MoveBetweenContainers(CONTAINER__CORE, CONTAINER__TEST, "test.txt");

$countSuccess = 0;
$countError = 0;
$fileStorage = GetFileStorage(CONTAINER__CORE);
foreach ($fileList as $filePath) {
    if ($fileStorage->MoveBetweenContainers(CONTAINER__CORE, $to, $filePath)) {
        $countSuccess++;
    } else {
        $countError++;
    }
}
print_r("Count success: " . $countSuccess . "\n");
print_r("Count error: " . $countError . "\n");

print_r($fileList);

print_r(count($fileList));


//$fileStorage = GetFileStorage(CONTAINER__CORE);
//print_r($fileStorage->GetFileContent("test.txt"));
//$fileStorage->MoveBetweenContainers(CONTAINER__CORE, CONTAINER__TEST, "test.txt");
die();
