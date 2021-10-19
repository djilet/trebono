<?php

es_include("ocr/processor.php");

class ReceiptFile extends LocalObject
{
    private $module;
    private $_acceptMimeTypes = array(
        'image/*',
        'image/png',
        'image/x-png',
        'image/gif',
        'image/jpeg',
        'image/pjpeg'
    );

    /**
     * Constructor
     *
     * @param string $module Name of context module
     * @param array $data Array of file properties to be loaded instantly
     */
    public function ReceiptFile($module, $data = array())
    {
        parent::LocalObject($data);

        $this->module = $module;
    }

    /**
     * Loads receipt file by its receipt_file_id
     *
     * @param int $id receipt_file_id
     *
     * @return bool true if loaded successfully or false on failure
     */
    public function LoadByID($id)
    {
        $query = "SELECT f.receipt_file_id, f.receipt_id, f.created, f.file_image, f.hash, f.signature_file, f.signature_report_file, f.signature_status
					FROM receipt_file AS f 
					WHERE f.receipt_file_id=" . intval($id);
        $this->LoadFromSQL($query);

        return $this->GetProperty("receipt_file_id") ? true : false;
    }

    /**
     * Creates receipt file from mobile application. Object must be loaded from request before the method will be called.
     * Required properties are: receipt_id
     *
     * @return bool true if file is created successfully or false on failure
     */
    public function Create($userID = 0)
    {
        //make temporary local file for receipt recognizing
        $fileSys = new FileSys();
        $tmpFile = $fileSys->Upload("file_image", PROJECT_DIR . "var/log/", false, $this->_acceptMimeTypes);
        if ($tmpFile) {
            $tmpFilePath = PROJECT_DIR . "var/log/" . $tmpFile["FileName"];
            //check for image duplicates based on sha256 hash
            $hash = hash("sha256", $fileSys->GetFileContent($tmpFilePath));

//            closed, because task #4232 16.09.21
//            if ($userID != 1032) {
//                $stmt = GetStatement();
//                $query = "SELECT COUNT(*) FROM receipt_file WHERE hash=" . Connection::GetSQLString($hash);
//                if ($stmt->FetchField($query) > 0) {
//                    $this->AddError("receipt-file-hash-is-not-unique", $this->module);
//                    @unlink($tmpFilePath);
//
//                    return false;
//                }
//            }

            if ($hash != $this->GetProperty("hash")) {
                $this->AddError("receipt-hash-not-equals", $this->module);
                @unlink($tmpFilePath);

                return false;
            }

            $receipt = new Receipt($this->module);
            $receipt->LoadByID($this->GetIntProperty('receipt_id'));

            $group = new ProductGroup('product');
            $group->LoadByID($receipt->GetIntProperty('group_id'));
            $needCheckImage = $group->GetProperty('need_check_image') !== 'N'; //do we need to check files because of product settings
            $needsDoubleCheck = "N"; //if OCR fails, we will save file and run the check later
            $doNotUseOCR = Config::GetConfigValue("do_not_use_ocr");

            if ($needCheckImage && $doNotUseOCR == "Y") {
                $this->AddMessage("ocr-error-proceed-check-later", $this->module);
                $needsDoubleCheck = "Y";
            } elseif ($needCheckImage) {
                //check if photo contains receipt
                $processor = new OCRProcesor("deu", null, 1);
                $checkResult = $processor->check($tmpFilePath);

                $requestData = $checkResult->requestData;
                $isSuccessful = ($checkResult->status == "fail" ? "N" : "Y");
                $isReceipt = null;
                if ($checkResult->status == "success") {
                    $isReceipt = $checkResult->isReceipt ? "Y" : "N";
                }
                OCRProcesor::SaveRequest(
                    $requestData["created"],
                    $requestData["url"],
                    $requestData["response_time"],
                    "ocr_1",
                    $isSuccessful,
                    $this->GetIntProperty('receipt_id'),
                    $userID,
                    $isReceipt
                );

                if ($checkResult->status == "fail") {
                    if (strlen($requestData["error_message"]) <= 0) {
                        $this->AddError("ocr-fail", $this->module);
                        @unlink($tmpFilePath);

                        return false;
                    }

                    $this->AddMessage("ocr-error-proceed-check-later", $this->module);
                    $needsDoubleCheck = "Y";
                } elseif ($checkResult->status == "error") {
                    $this->AddError($checkResult->errorCode, $this->module);
                    @unlink($tmpFilePath);

                    return false;
                } else {
                    if (!$checkResult->isReceipt) {
                        $this->AddError("receipt-file-doesnt-contain-receipt", $this->module);
                        @unlink($tmpFilePath);

                        return false;
                    }
                }
            }

            //if receipt is recognized then put the temp file to permanent storage and save to db
            $conventionalFileName = self::GenerateConventionalFileName(
                $this->GetProperty("receipt_id"),
                GetCurrentDateTime(),
                $tmpFile["FileExtension"]
            );
            $finalFilePath = RECEIPT_IMAGE_DIR . "file/" . $conventionalFileName;

            $receipt = new Receipt("receipt");
            $receipt->LoadByID($this->GetProperty("receipt_id"));

            $specificProductGrpup = SpecificProductGroupFactory::Create($receipt->GetProperty("group_id"));
            $container = $specificProductGrpup->GetContainer();

            $fileStorage = GetFileStorage($container);
            $fileStorage->PutFileContent($finalFilePath, $fileSys->GetFileContent($tmpFilePath));

            if ($fileStorage->FileExists($finalFilePath)) {
                $this->SetProperty("file_image", $conventionalFileName);

                $stmt = GetStatement();
                $query = "INSERT INTO receipt_file (receipt_id, file_image, created, hash, needs_check) VALUES ( 
							" . $this->GetIntProperty("receipt_id") . ",
							" . $this->GetPropertyForSQL("file_image") . ",
							" . Connection::GetSQLString(GetCurrentDateTime()) . ",
	                        " . $this->GetPropertyForSQL("hash") . ",
	                        " . Connection::GetSQLString($needsDoubleCheck) . ")
						RETURNING receipt_file_id";
                if ($stmt->Execute($query)) {
                    Receipt::SetLegalReceiptID($this->GetProperty("receipt_id"));

                    $this->SetProperty("receipt_file_id", $stmt->GetLastInsertID());
                    Receipt::Touch($this->GetProperty("receipt_id"));

                    self::WriteLog(
                        $this->GetProperty("receipt_file_id"),
                        "validating file hash on receipt file creation",
                        "info"
                    );
                    self::WriteLog(
                        $this->GetProperty("receipt_file_id"),
                        "file hash passed by mobile application: " . $this->GetProperty("hash"),
                        "info"
                    );
                    self::WriteLog(
                        $this->GetProperty("receipt_file_id"),
                        "file hash generated by server side: " . $hash,
                        "info"
                    );
                    self::WriteLog(
                        $this->GetProperty("receipt_file_id"),
                        "hash comparsion result: " . ($this->GetProperty("hash") == $hash ? "success" : "failure"),
                        "info"
                    );

                    if (
                        ProductGroup::DoesEmployeeProductGroupHaveAdvancedSecurity(
                            $receipt->GetProperty("group_id"),
                            $receipt->GetProperty("employee_id"),
                            $receipt->GetProperty("created")
                        )
                    ) {
                        if (
                            RabbitMQ::Send(
                                "signature_create",
                                array("receipt_file_id" => $this->GetProperty("receipt_file_id"), "verify" => false)
                            )
                        ) {
                            self::SetSignatureStatus($this->GetProperty("receipt_file_id"), "signature_create_started");
                            self::WriteLog($this->GetProperty("receipt_file_id"), "--------", "info");
                            self::WriteLog(
                                $this->GetProperty("receipt_file_id"),
                                "add rabbit mq task on creation",
                                "info"
                            );
                        } else {
                            self::WriteLog($this->GetProperty("receipt_file_id"), "--------", "error");
                            self::WriteLog(
                                $this->GetProperty("receipt_file_id"),
                                "add rabbit mq task on creation failed",
                                "info"
                            );
                        }
                    }

                    if ($needCheckImage || $group->GetProperty("code") == PRODUCT_GROUP__TRAVEL) {
                        if (
                            RabbitMQ::Send(
                                "line_recognize",
                                array("receipt_file_id" => $this->GetProperty("receipt_file_id"))
                            )
                        ) {
                            self::WriteLog($this->GetProperty("receipt_file_id"), "--------", "info");
                            self::WriteLog(
                                $this->GetProperty("receipt_file_id"),
                                "add rabbit mq task on line recognize",
                                "info"
                            );
                        } else {
                            self::WriteLog($this->GetProperty("receipt_file_id"), "--------", "error");
                            self::WriteLog(
                                $this->GetProperty("receipt_file_id"),
                                "add rabbit mq task on line recognize failed",
                                "info"
                            );
                        }
                    }
                    @unlink($tmpFilePath);

                    return true;
                }
            }
            $this->AppendErrorsFromObject($fileStorage);
        }

        @unlink($tmpFilePath);
        $this->AppendErrorsFromObject($fileSys);

        return false;
    }

    /**
     * Returns filename generated by conventional way
     *
     * @param int $receiptID receipt_id of receipt_file attached to
     * @param string $created date of receipt_file creation
     * @param string $extension extension of receipt file
     *
     * @return string
     */
    public static function GenerateConventionalFileName($receiptID, $created, $extension)
    {
        $stmt = GetStatement();

        $receipt = new Receipt("receipt");
        $receipt->LoadByID($receiptID);

        $employee = new Employee("company");
        $employee->LoadByID($receipt->GetProperty("employee_id"));

        $companyUnit = new CompanyUnit("company");
        $companyUnit->LoadByID($employee->GetProperty("company_unit_id"));

        return date("Y_m", strtotime($created)) .
            "_" . $companyUnit->GetProperty("customer_guid") .
            "_" . $employee->GetProperty("employee_guid") .
            "_" . $receipt->GetProperty("receipt_id") .
            "_" . uniqid() . "." . $extension;
    }

    /**
     * Set signature file name. Object must be loaded from request before the method will be called.
     *
     * @param string $signatureFileName signature_file
     */
    public function SetSignatureFile($signatureFileName)
    {
        $stmt = GetStatement();

        $query = "UPDATE receipt_file SET signature_file=" . Connection::GetSQLString($signatureFileName) . "
					WHERE receipt_file_id=" . $this->GetIntProperty("receipt_file_id");

        $stmt->Execute($query);
    }

    /**
     * Set signature report file name. Object must be loaded from request before the method will be called.
     *
     * @param string $signatureReportFileName signature_report_file
     */
    public function SetSignatureReportFile($signatureReportFileName)
    {
        $stmt = GetStatement();

        $query = "UPDATE receipt_file SET signature_report_file=" . Connection::GetSQLString($signatureReportFileName) . "
					WHERE receipt_file_id=" . $this->GetIntProperty("receipt_file_id");

        $stmt->Execute($query);
    }

    /**
     * Insert or update log message in receipt_file_log
     *
     * @param int $receiptFileID receipt_file_id
     * @param string $content log message
     * @param string $type type of message to simplify search
     */
    public static function WriteLog($receiptFileID, $content, $type)
    {
        $stmt = GetStatement(DB_CONTROL);

        $query = 'INSERT INTO receipt_file_log(receipt_file_id, updated, content)
                    VALUES(' . intval($receiptFileID) . ', ' . Connection::GetSQLString(GetCurrentDateTime()) . ', ' . Connection::GetSQLString(date("d.m.Y H:i:s") . " [" . $type . "] " . $content) . ')
                ON CONFLICT(receipt_file_id)
                DO UPDATE SET updated = ' . Connection::GetSQLString(GetCurrentDateTime()) . ', content = receipt_file_log.content || ' . Connection::GetSQLString("\n" . date("d.m.Y H:i:s") . " [" . $type . "] " . $content);

        $stmt->Execute($query);
    }

    /**
     * Get log content from receipt_file_log
     *
     * @param int $receiptFileID receipt_file_id
     *
     * @return false or array
     */
    public static function GetLog($receiptFileID)
    {
        $stmt = GetStatement(DB_CONTROL);

        $query = 'SELECT receipt_file_id, updated, content FROM receipt_file_log WHERE receipt_file_id=' . intval($receiptFileID);

        return $stmt->FetchRow($query);
    }

    /**
     * Get signature status.
     *
     * @param int $receiptFileID receipt_file_id
     */
    public static function GetSignatureStatus($receiptFileID)
    {
        $stmt = GetStatement();

        $query = "SELECT signature_status
					FROM receipt_file
					WHERE receipt_file_id=" . intval($receiptFileID);

        return $stmt->FetchField($query);
    }

    /**
     * Set signature status.
     *
     * @param int $receiptFileID receipt_file_id
     * @param string $status signature_status
     */
    public static function SetSignatureStatus($receiptFileID, $status)
    {
        $stmt = GetStatement();

        $query = "UPDATE receipt_file SET signature_status=" . Connection::GetSQLString($status) . "
					WHERE receipt_file_id=" . intval($receiptFileID);

        $stmt->Execute($query);
    }

    /**
     * Output (download) evidence pack. Object must be loaded from request before the method will be called.
     */
    public function CreateAndOutputEvidencePack()
    {
        //generate transfer note
        $fileSys = new FileSys();
        $transferNoteFilePath = $this->GenerateTransferNotePDF();

        $receipt = new Receipt("receipt");
        $receipt->LoadByID($this->GetProperty("receipt_id"));

        $specificProductGrpup = SpecificProductGroupFactory::Create($receipt->GetProperty("group_id"));
        $container = $specificProductGrpup->GetContainer();

        $fileStorage = GetFileStorage($container);
        $fileName = "evidence_" . $this->GetProperty("receipt_id") . "_" . $this->GetProperty("receipt_file_id") . ".zip";
        $filePath = PROJECT_DIR . "var/log/" . $fileName;

        //create zip archive
        $z = new ZipArchive();

        $z->open($filePath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        //add image, signature, siganture report, transfer note and log to archive
        $z->addFromString(
            $this->GetProperty("file_image"),
            $fileStorage->GetFileContent(RECEIPT_IMAGE_DIR . "file/" . $this->GetProperty("file_image"))
        );
        if ($this->GetProperty("signature_file")) {
            $z->addFromString(
                $this->GetProperty("signature_file"),
                $fileStorage->GetFileContent(RECEIPT_IMAGE_DIR . "file/" . $this->GetProperty("signature_file"))
            );
        }
        if ($this->GetProperty("signature_report_file")) {
            $z->addFromString(
                $this->GetProperty("signature_report_file"),
                $fileStorage->GetFileContent(RECEIPT_IMAGE_DIR . "file/" . $this->GetProperty("signature_report_file"))
            );
        }
        $z->addFromString(
            "trebono_transfervermerk_" . $this->GetProperty("receipt_id") . "_" . $this->GetProperty("receipt_file_id") . ".pdf",
            $fileSys->GetFileContent($transferNoteFilePath)
        );

        $z->close();

        //remove transfer note
        $fileSys->Remove($transferNoteFilePath);

        if ($fileSys->FileExists($filePath)) {
            //output zip archive
            header("Content-Type: application/zip");
            header("Content-disposition: attachment; filename=\"" . $fileName . "\"");
            header("Cache-Control: public, must-revalidate, max-age=0");
            header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
            echo $fileSys->GetFileContent($filePath);

            //remove zip archive
            $fileSys->Remove($filePath);
            exit();
        }

        Send404();
    }

    /**
     * Generate pdf transfer note pdf and store it in var/log
     * Object must be loaded by id before this method will be called
     *
     * @return string file path
     */
    public function GenerateTransferNotePDF()
    {
        $tmpPath = PROJECT_DIR . "var/log/";
        $fileName = "trebono_transfervermerk_" . $this->GetProperty("receipt_id") . "_" . $this->GetProperty("receipt_file_id");

        //load receipt, product group, employee, org guideline, company unit and signature report data
        //receipt
        $receipt = new Receipt($this->module);
        $receipt->LoadByID($this->GetProperty("receipt_id"));

        $receiptArray = array();
        foreach ($receipt->GetProperties() as $key => $property) {
            $receiptArray["receipt_" . $key] = $property;
        }

        //product group
        $productGroup = new ProductGroup("product");
        $productGroup->LoadByID($receipt->GetProperty("group_id"));

        //specific product group conatiner
        $specificProductGrpup = SpecificProductGroupFactory::Create($receipt->GetProperty("group_id"));
        $container = $specificProductGrpup->GetContainer();

        $productGroupArray = array();
        foreach ($productGroup->GetProperties() as $key => $property) {
            if ($key == "title" || $key == "title_translation") {
                $property = GetTranslation(
                    "product-group-" . $productGroup->GetProperty("code"),
                    "product",
                    null,
                    "de"
                );
            }

            $productGroupArray["product_group_" . $key] = $property;
        }

        //employee
        $employee = new Employee("company");
        $employee->LoadByID($receipt->GetProperty("employee_id"));

        $employeeArray = array();
        foreach ($employee->GetProperties() as $key => $property) {
            $employeeArray["employee_" . $key] = $property;
        }

        //organizational guideline
        $orgGuidelineEmployee = Employee::GetPropertyHistoryValueEmployee(
            "org_guideline_version",
            $receipt->GetProperty("employee_id"),
            $receipt->GetProperty("created")
        );
        $orgGuidelineConfig = false;
        if ($orgGuidelineEmployee) {
            $orgGuidelineConfig = Config::GetConfigHistoryValue($orgGuidelineEmployee["value"]);
        }
        //company unit
        $companyUnit = new CompanyUnit("company");
        $companyUnit->LoadByID($employee->GetProperty("company_unit_id"));

        $companyUnitArray = array();
        foreach ($companyUnit->GetProperties() as $key => $property) {
            $companyUnitArray["company_unit_" . $key] = $property;
        }

        //signature report
        $fileStorage = GetFileStorage($container);
        $reportHash = "";
        $checkTime = "";
        if ($this->GetProperty("signature_report_file") && $fileStorage->FileExists(RECEIPT_IMAGE_DIR . "file/" . $this->GetProperty("signature_report_file"))) {
            try {
                $reportXml = simplexml_load_string($fileStorage->GetFileContent(RECEIPT_IMAGE_DIR . "file/" . $this->GetProperty("signature_report_file")));
                if (isset($reportXml->document->hash)) {
                    $reportHash = array((string)$reportXml->document->hash)[0];
                }
                if (isset($reportXml->summary->checktime)) {
                    $checkTime = array((string)$reportXml->summary->checktime)[0];
                }
            } catch (Exception $e) {
            }
        } else {
            $reportHash = strtoupper(hash(
                "sha256",
                $fileStorage->GetFileContent(RECEIPT_IMAGE_DIR . "file/" . $this->GetProperty("file_image"))
            ));
            $checkTime = $receipt->GetProperty("status_updated");
        }

        $compareHashResult = strtoupper($this->GetProperty("hash")) == $reportHash ? "success" : "fail";

        //create pdf template
        $popupPage = new PopupPage($this->module);
        $content = $popupPage->Load("transfer_note_pdf.html");
        $content->SetVar("Module", $this->module);

        //load all data to content
        $content->LoadFromObject($this);

        $content->LoadFromArray($receiptArray);
        $content->LoadFromArray($employeeArray);
        $content->LoadFromArray($companyUnitArray);
        $content->LoadFromArray($productGroupArray);

        $replacements = array(
            "org_guideline_version" => $orgGuidelineEmployee["value"] ?? null,
            "org_guideline_accept_date" => ($orgGuidelineEmployee ? date(
                "d.m.Y H:i:s",
                strtotime($orgGuidelineEmployee["created"])
            ) : null),
            "org_guideline_create_date" => ($orgGuidelineConfig ? date(
                "d.m.Y H:i:s",
                strtotime($orgGuidelineConfig["date_from"])
            ) : null),
            "org_guideline_url" => GetUrlPrefix() . ADMIN_FOLDER . "/config.php?config_version_id=" . ($orgGuidelineEmployee["value"] ?? null)
        );
        $content->SetVar(
            "org_guideline_block_html",
            GetTranslation("receipt-file-transfer-note-org-guideline-html", $this->module, $replacements)
        );

        //receipt file hash to uppercase
        $content->SetVar("hash", strtoupper($this->GetProperty("hash")));

        $content->SetVar("signature_report_hash", $reportHash);
        $content->SetVar("signature_report_date", $checkTime);
        $content->SetVar("compare_hash_result", $compareHashResult);

        $html = $popupPage->Grab($content);

        //create pdf
        $pdf = new mPDF("utf-8", "A4", "11", "dejavusans", 15, 15, 40, 16, 4, 6);

        $pdf->PDFA = true;
        $pdf->PDFAauto = true;

        $css = file_get_contents(PROJECT_DIR . "module/" . $this->module . "/template/transfer_note_pdf_style.css");

        $pdf->WriteHTML($css, 1);

        $pdf->writeHTML($html, 2);

        //$pdf->Output($fileName, "I");die();

        $pdf->Output($tmpPath . $fileName, "F");

        return $tmpPath . $fileName;
    }
}
