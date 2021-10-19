<?php

use PhpOffice\PhpSpreadsheet\Spreadsheet;

class MasterData extends LocalObject
{

    private $module;

    /**
     * Constructor
     *
     * @param string $module Name of context module
     * @param array $data Array of master data properties to be loaded instantly
     */
    public function MasterData($module, $data = array())
    {
        parent::LocalObject($data);

        $this->module = $module;
    }

    /**
     * Created new master data record in database. Object must be loaded before the method will be called.
     * Required properties are: type
     *
     * @return bool master_data_id of new record on success or false on sql-failure
     */
    public function Create()
    {
        $exportType = $this->GetProperty("type");
        if ($exportType == "employee") {
            $employeeList = EmployeeList::GetEmployeeListForMasterDataCreation($this->GetProperty("new"));
            if (count($employeeList) == 0) {
                $this->AddError("master-data-no-employees", "billing");

                return false;
            }
        } else {
            $companyUnitList = CompanyUnitList::GetCompanyUnitListForMasterDataCreation(
                $exportType,
                $this->GetProperty("new")
            );

            if (count($companyUnitList) == 0) {
                $this->AddError("master-data-no-company-units", "billing");

                return false;
            }
        }

        $stmt = GetStatement();
        $query = "INSERT INTO master_data (created, type, new) VALUES (
						" . Connection::GetSQLString(GetCurrentDateTime()) . ",
						" . $this->GetPropertyForSQL("type") . ",
						" . $this->GetPropertyForSQL("new") . ")
					RETURNING master_data_id";

        if ($stmt->Execute($query)) {
            return $stmt->GetLastInsertID();
        }

        $this->AddError("sql-error");

        return false;
    }

    /**
     * Loads stored data by its master_data_id
     *
     * @param int $masterDatalID master_data_id
     *
     * @return bool true if loaded successfully or false on failure
     */
    public function LoadByID($masterDatalID)
    {
        $stmt = GetStatement(DB_MAIN);
        $query = "SELECT master_data_id, created, type, new
					FROM master_data
					WHERE master_data_id=" . intval($masterDatalID);

        $this->LoadFromSQL($query, $stmt);

        return $this->GetProperty("master_data_id") ? true : false;
    }

    /**
     * Generated CSV file for master data and outputs it to browser or to temporary local file.
     * Object must be loaded before the method will be called.
     * Required properties are: type
     */
    function GenerateMasterDataCSV()
    {
        $exportType = $this->GetProperty("type");
        if ($exportType == "employee") {
            $dataForRows = EmployeeList::GetEmployeeListForMasterDataCreation($this->GetProperty("new"));
            if (count($dataForRows) == 0) {
                $this->AddError("master-data-no-employees", "billing");

                return false;
            }
            $db = DB_PERSONAL;
            $table = "employee";
            $columnWhere = "employee_id";
        } else {
            $dataForRows = CompanyUnitList::GetCompanyUnitListForMasterDataCreation(
                $exportType,
                $this->GetProperty("new")
            );

            if (count($dataForRows) == 0) {
                $this->AddError("master-data-no-company-units", "billing");

                return false;
            }
            $db = DB_MAIN;
            $table = "company_unit";
            $columnWhere = "company_unit_id";
        }

        $partOfFilename = "";
        $payment = "";
        switch ($exportType) {
            case "employee":
                $partOfFilename = "STD_Konto_Mitarbeiter_2KSG_";
                $payment = "U";
                $exportColumnUpdate = 'master_data_export_update_id';
                if ($this->GetProperty("new") == "Y") {
                    $exportColumn = "master_data_export_id";
                }
                $secondLine = ";Anlage Stammdaten Lieferantenkonten";
                break;
            case "company_unit_service":
                $partOfFilename = "STD_Konto_Service_Kunden_";
                $payment = "E";
                $exportColumnUpdate = 'master_data_service_update_id';
                if ($this->GetProperty("new") == "Y") {
                    $exportColumn = "master_data_service_id";
                }
                $secondLine = ";Anlage Stammdaten Lieferantenkonten";
                break;
            case "company_unit_voucher":
                $partOfFilename = "STD_Konto_Voucher_Kunden_";
                $payment = "A";
                $exportColumnUpdate = 'master_data_voucher_update_id';
                if ($this->GetProperty("new") == "Y") {
                    $exportColumn = "master_data_voucher_id";
                }
                $secondLine = ";Anlage Stammdaten Lieferantenkonten";
                break;
            case "sepa_service":
                $partOfFilename = "STD_SEPA_Kunde_Service_Export_";
                $exportColumnUpdate = 'master_data_sepa_service_update_id';
                if ($this->GetProperty("new") == "Y") {
                    $exportColumn = "master_data_sepa_service_id";
                }
                $secondLine = ";Anlage Stammdaten Debitorenkonten";
                break;
            case "sepa_voucher":
                $partOfFilename = "STD_SEPA_Kunde_Gutschein_Export_";
                $exportColumnUpdate = 'master_data_sepa_voucher_update_id';
                if ($this->GetProperty("new") == "Y") {
                    $exportColumn = "master_data_sepa_voucher_id";
                }
                $secondLine = ";Anlage Stammdaten Debitorenkonten";
                break;
        }

        $filename = $partOfFilename . date_create($this->GetProperty("created"))->format("Ymd") . "_" .
            $this->GetProperty("master_data_id") . ".csv";

        $tableBody = array();
        foreach ($dataForRows as $data) {
            $creditorNumber = $exportType == "employee" ? $data["creditor_number"] : substr($data["customer_guid"], 4);
            if ($exportType == "company_unit_voucher" || $exportType == "sepa_voucher") {
                $creditorNumber = "10" . $creditorNumber;
            }

            $iban = str_replace(' ', '', $data["iban"]) ?? "";

            if ($exportType == "employee" || $exportType == "company_unit_service" || $exportType == "company_unit_voucher") {
                if ($this->GetProperty("new") == "Y") {
                    $name = $exportType == "employee"
                        ? trim(Employee::GetNameByID($data["employee_id"]))
                        : trim($data["title"]);
                    $bankName = $exportType == "employee" ? trim($data["bank_name"]) : trim($data["bank_details"]);

                    $row = array(
                        "K",
                        $creditorNumber,
                        strlen($name) > 0 ? '"' . $name . '"' : "",
                        strlen($name) > 0 ? '"' . $name . '"' : "",
                        substr($iban, 4, 8) ?? "",
                        substr($iban, 12) ?? "",
                        strlen($bankName) > 0 ? $bankName : "",
                        $iban,
                        "1",
                        "2",
                        '"' . $payment . '"',
                    );
                } else {
                    $row = array(
                        "K",
                        $creditorNumber,
                        substr($iban, 4, 8) ?? "",
                        substr($iban, 12) ?? "",
                        "",
                        $iban,
                        "",
                        "N"
                    );
                }
            } else {
                $sepaFirstCreatedDate = strlen($data["sepa_first_created"]) > 0 ?
                    date("d.m.Y", strtotime($data["sepa_first_created"])) : "";
                $sepaNumber = trim($data["sepa_number"]);
                $sepaDateSign = strlen($data["sepa_date_sign"]) > 0 ?
                    date("d.m.Y", strtotime($data["sepa_date_sign"])) : "";

                $row = array(
                    "SM",
                    $creditorNumber,
                    $iban,
                    $sepaFirstCreatedDate ?? "",
                    strlen($sepaDateSign) > 0 ? $sepaDateSign : "",
                    ($exportType == "sepa_service" ? "1" : "2"),
                    $sepaNumber ?? ""
                );
            }
            $tableBody[] = $row;
        }

        //create spreadsheet and write the data
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getActiveSheet()->fromArray($tableBody, null, "A3");

        //save file
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Csv($spreadsheet);
        $writer->setDelimiter(";");
        $writer->setLineEnding("\r\n");
        $writer->setSheetIndex(0);
        $writer->setEnclosure(null);

        $tempFilePath = PROJECT_DIR . "var/log/" . $partOfFilename .
            date_create($this->GetProperty("created"))->format("Ymd") . "_" .
            $this->GetProperty("master_data_id") . ".csv";
        $writer->setUseBOM(true);
        $writer->save($tempFilePath);
        $content = file_get_contents($tempFilePath);
        $f = fopen($tempFilePath, "w");
        fwrite($f, $content);
        fclose($f);

        $file = file($tempFilePath);
        $file[0] = PHP_EOL;
        $file[1] = $secondLine . PHP_EOL;
        file_put_contents($tempFilePath, implode("", $file));

        $fileStorage = GetFileStorage(CONTAINER__BILLING__MASTER_DATA);
        $fileStorage->MoveToStorage($tempFilePath, MASTER_DATA_DIR, $filename);

        if ($this->GetProperty("new") == "Y") {
            $set = " SET " . $exportColumn . "=" . $this->GetProperty("master_data_id") . ", " .
                $exportColumnUpdate . "=" . $this->GetProperty("master_data_id");
        } else {
            $set = " SET " . $exportColumnUpdate . "=" . $this->GetProperty("master_data_id");
        }

        $stmt = GetStatement($db);
        $query = "UPDATE " . $table . $set . " WHERE " . $columnWhere . " IN(" . implode(
            ",",
            array_column($dataForRows, $columnWhere)
        ) . ")";

        if ($stmt->Execute($query)) {
            return true;
        }

        $this->AddError("sql-error");

        return false;
    }
}
