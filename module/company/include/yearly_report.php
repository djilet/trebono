<?php

use PhpOffice\PhpSpreadsheet\Spreadsheet;

class YearlyReport extends LocalObject
{

    private $module;

    /**
     * Constructor
     *
     * @param string $module Name of context module
     * @param array $data Array of bookkeeping export properties to be loaded instantly
     */
    public function YearlyReport($module, $data = array())
    {
        parent::LocalObject($data);

        $this->module = $module;
    }

    /**
     * Created new export record in database. Object must be loaded before the method will be called.
     * Required properties are: company_unit_id, date_from, date_to, services, employees
     *
     * @return bool bookkeeping_export_id of new record on success or false on sql-failure
     */
    public function Create()
    {
        $stmt = GetStatement();

        $query = "INSERT INTO yearly_employee_report (company_unit_id, created, date_from, date_to, file, user_id, archive) VALUES (
						" . $this->GetIntProperty("company_unit_id") . ",
						" . Connection::GetSQLString(GetCurrentDateTime()) . ",
						" . $this->GetPropertyForSQL("date_from") . ",
						" . $this->GetPropertyForSQL("date_to") . ",
						" . $this->GetPropertyForSQL("file") . ",
						" . $this->GetPropertyForSQL("user_id") . ",
						" . $this->GetPropertyForSQL("archive") . ")
					RETURNING report_id";

        if ($stmt->Execute($query)) {
            $this->SetProperty("report_id", $stmt->GetLastInsertID());

            return true;
        }

        $this->AddError("sql-error");

        return false;
    }

    /**
     * Loads report by its report_id
     *
     * @param int $id report_id
     *
     * @return bool true if loaded successfully or false on failure
     */
    public function LoadByID($id)
    {
        $stmt = GetStatement(DB_MAIN);
        $query = "SELECT *
					FROM yearly_employee_report
					WHERE report_id=" . intval($id);

        $this->LoadFromSQL($query, $stmt);

        return $this->GetProperty("report_id") ? true : false;
    }

    /**
     * Generated ZIP archive for report and outputs it to browser or to temporary local file.
     * Object must be loaded before the method will be called.
     */
    public function GenerateReportZIP()
    {
        $yearEnd = date($this->GetProperty("yearly_report_date") . "-12-31");
        $date = strtotime($yearEnd) <= strtotime(GetCurrentDate())
            ? $yearEnd
            : date($this->GetProperty("yearly_report_date") . "-m-d");

        $companyUnitID = $this->GetProperty("company_unit_id");
        $employeeIDs = EmployeeList::GetFullHierarchyEmployeeIDsByCompanyUnitID($companyUnitID);
        sort($employeeIDs);

        $header = explode(
            ";",
            "trebono ID; Nachname; Vorname; Personalnummer; Sachlohnart; Verfügbar im Jahr; Genehmigt im Jahr; Jahres Sachlohn Budget"
        );

        $lineList = [];
        foreach ($employeeIDs as $employeeID) {
            $employee = new Employee("company", ["employee_id" => $employeeID]);
            $employee->LoadByID($employeeID);

            $statistics = Statistics::GetStatistics($employee, $date, null, ["approved_year", "available_year"], true);
            foreach ($statistics['product_groups'] as $data) {
                $lineList[] = [
                    $employeeID,
                    $employee->GetProperty("last_name"),
                    $employee->GetProperty("first_name"),
                    $employee->GetProperty("employee_guid"), //employee id for payroll
                    GetTranslation("product-group-" . $data["code"], "product", [], "de"), //product group title
                    GetPriceFormat($data["available_year"]),
                    GetPriceFormat($data["approved_year"]),
                ];
            }

            if (count($statistics['product_groups']) <= 0) {
                continue;
            }

            $lineList[] = [
                $employeeID,
                $employee->GetProperty("last_name"),
                $employee->GetProperty("first_name"),
                $employee->GetProperty("employee_guid"),
                "Jahr " . $this->GetProperty("yearly_report_date") . " Total",
                GetPriceFormat($statistics["total_available_year"]),
                GetPriceFormat($statistics["total_year"]), //total approved year
                GetPriceFormat($employee->GetProperty("yearly_total_benefits")),
            ];
        }

        $zipFilename = str_replace(
            " ",
            "_",
            CompanyUnit::GetTitleByID($companyUnitID)
        ) . "_" . $companyUnitID . "_" . $this->GetProperty("yearly_report_date");
        $archiveLocalPath = PROJECT_DIR . "var/log/yearly_export_" . $zipFilename . ".zip";
        $fileSys = new FileSys();

        $zip = new ZipArchive();
        $resultOpen = $zip->open($archiveLocalPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        if ($resultOpen === false) {
            return false;
        }

        //create spreadsheet and write the data
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getActiveSheet()->fromArray($header, null, "A1");
        $spreadsheet->getActiveSheet()->fromArray($lineList, null, "A2");

        //save file
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Csv($spreadsheet);
        $writer->setDelimiter(";");
        $writer->setLineEnding("\r\n");
        $writer->setSheetIndex(0);

        $filename = date("ymd", strtotime($date)) . "_" . $zipFilename . ".csv";
        $path = PROJECT_DIR . "var/log/yearly_export_" . $filename;

        $writer->setUseBOM(true);
        $writer->save($path);

        $content = file_get_contents($path);
        $content = str_replace("\"", "", $content);
        $f = fopen($path, "w");
        fwrite($f, $content);
        fclose($f);

        $zip->addFromString($filename, $fileSys->GetFileContent($path));
        $fileSys->Remove($path);

        $zip->close();

        if ($fileSys->FileExists($archiveLocalPath)) {
            $this->SetProperty("company_unit_id", $companyUnitID);
            $this->SetProperty("file", $zipFilename . ".zip");
            $this->SetProperty("date_from", date("Y-01-01", strtotime($date)));
            $this->SetProperty("date_to", $date);
            $this->SetProperty("archive", "N");
            if (!$this->Create()) {
                return false;
            }

            $zipFilename .= "_" . $this->GetProperty("report_id") . ".zip";

            $fileStorage = GetFileStorage(CONTAINER__COMPANY);

            $fileStorage->MoveToStorage($archiveLocalPath, COMPANY_UNIT_YEARLY_REPORT_DIR, $zipFilename);
            $fileSys->Remove($archiveLocalPath);
            $this->UpdateField("file", $zipFilename);

            $values = "(" . $this->GetProperty("report_id") . ",'archive','N','" . $this->GetProperty("user_id") . "','" . GetCurrentDateTime() . "','admin')";
            $stmtControl = GetStatement(DB_CONTROL);
            $query = "INSERT INTO yearly_employee_report_history (report_id, property_name, value, user_id, created, created_from) VALUES " . $values;
            $stmtControl->Execute($query);
        }

        return true;
    }

    /**
     * Output (download) report archive. Object must be loaded from request before the method will be called.
     */
    public function OutputZipFile()
    {
        $fileName = $this->GetProperty("file");
        $filePath = COMPANY_UNIT_YEARLY_REPORT_DIR . $this->GetProperty("file");

        $fileStorage = GetFileStorage(CONTAINER__COMPANY);

        if ($fileStorage->FileExists($filePath)) {
            header("Content-Type: application/zip");
            header("Content-disposition: attachment; filename=\"" . $fileName . "\"");
            header("Cache-Control: public, must-revalidate, max-age=0");
            header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
            echo $fileStorage->GetFileContent($filePath);
            exit();
        }

        Send404();
    }

    /**
     * Change report record in DB
     *
     * @param string $field Name of param to change
     * @param mixed $value New value of param
     */
    public function UpdateField($field, $value)
    {
        $stmt = GetStatement(DB_MAIN);
        $query = "UPDATE yearly_employee_report SET " . $field . "=" . Connection::GetSQLString($value) . " WHERE report_id=" . $this->GetIntProperty("report_id");

        return $stmt->Execute($query);
    }

    /**
     * Get property by id
     *
     * @param string $field Name of param
     * @param mixed $id Report id
     */
    public static function GetPropertyByID($field, $id)
    {
        $stmt = GetStatement(DB_MAIN);
        $query = "SELECT " . $field . " FROM yearly_employee_report WHERE report_id=" . Connection::GetSQLString($id);

        return $stmt->FetchField($query);
    }

    /**
     * Returns properties value history
     *
     * @param array $property
     * @param int $reportID
     * @param bool $orderDesc
     *
     * @return array value properties
     */
    public static function GetPropertyValueListReport($property, $reportID, $orderDesc = false, $languageCode = null)
    {
        $stmt = GetStatement(DB_CONTROL);

        $orderBy = $orderDesc ? " ORDER BY created DESC" : " ORDER BY created ASC";

        $query = "SELECT value_id, report_id, created, value, property_name, created_from, user_id
					FROM yearly_employee_report_history
					WHERE property_name=" . Connection::GetSQLString($property) . " AND report_id=" . intval($reportID) . " 
					" . $orderBy;
        $valueList = $stmt->FetchList($query);

        $usernameList = [];
        foreach ($valueList as $key => $item) {
            if (!isset($usernameList[$item["user_id"]])) {
                $usernameList[$item["user_id"]] = User::GetNameByID($item["user_id"]);
            }
            $valueList[$key]["user_name"] = $usernameList[$item["user_id"]];

            if ($valueList[$key]["property_name"] != "archive") {
                continue;
            }

            $valueList[$key]["value_text"] = $valueList[$key]["value"] == "N" ? GetTranslation("Active", null, [], $languageCode) : GetTranslation("Cancel", null, [], $languageCode);
        }

        return $valueList;
    }
}
