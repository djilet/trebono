<?php

use PhpOffice\PhpSpreadsheet\Spreadsheet;

class InvoiceList extends LocalObjectList
{
    private $module;

    /**
     * Constructor
     *
     * @param string $module Name of context module
     * @param array $data Array of items to be loaded instantly
     */
    public function InvoiceList($module, $data = array())
    {
        parent::LocalObjectList($data);

        $this->module = $module;
        $this->SetSortOrderFields(array(
            "created_asc" => "i.created ASC, i.invoice_id ASC",
            "created_desc" => "i.created DESC, i.invoice_id DESC",
            "export" => "i.company_unit_id ASC, i.created ASC, i.invoice_id ASC",
            "export_voucher" => "v.created ASC, v.voucher_id ASC"
        ));
        $this->SetOrderBy("created_desc");
        $this->SetItemsOnPage(10);
    }

    /**
     * Loads invoice list for admin panel
     *
     * @param LocalObject $request . Can contain following properties:
     *        <ul>
     *        <li><u>ItemsOnPage</u> - int - size of page when paging is used</li>
     *        <li><u>FilterCreatedRange</u> - string - property for "created" field filtration. format is "{any_date_format_from} - {any_date_format_to}"</li>
     *        <li><u>FilterTitle</u> - string - property for company_unit title filtration</li>
     *        </ul>
     * @param bool $fullList If is set to true, then all objects will be loaded at once without paging
     */
    public function LoadInvoiceListForAdmin($request, $fullList = false)
    {
        if ($fullList) {
            $this->SetItemsOnPage(0);
        } elseif ($request->IsPropertySet("ItemsOnPageInvoice")) {
            $this->SetItemsOnPage($request->GetIntProperty("ItemsOnPageInvoice"));
        }

        if ($request->GetProperty($this->GetOrderByParam())) {
            $this->SetOrderBy($request->GetProperty($this->GetOrderByParam()));
        }

        $where = array();
        //Permission filter
        $user = new User();
        $user->LoadBySession();
        $permissionList = $user->GetProperty("PermissionList");
        $permissionLinks = array();
        foreach ($permissionList as $permission) {
            if ($permission['name'] == "root") {
                $permissionLinks = array();
                break;
            }
            if ($permission['name'] != "invoice") {
                continue;
            }

            $permissionLinks[] = $permission['link_id'];
        }
        $permissionLinks = array_filter($permissionLinks);
        if ($permissionLinks) {
            $where[] = "i.company_unit_id IN (" . implode(",", $permissionLinks) . ")";
        }

        if ($request->GetProperty("FilterCreatedRange")) {
            [$from, $to] = explode(" - ", $request->GetProperty("FilterCreatedRange"));
            $where[] = "i.created >= " . Connection::GetSQLDateTime($from);
            $where[] = "i.created <= " . Connection::GetSQLDateTime($to);
        }

        if ($request->GetProperty("FilterTitle")) {
            $where[] = Connection::GetSQLDecryption("c.title") . " ~* " . Connection::GetSQLSearchRegexp($request->GetProperty("FilterTitle"));
        }

        $query = "SELECT i.invoice_id, i.company_unit_id, i.created, i.date_from, i.date_to, i.status, 
						" . Connection::GetSQLDecryption("c.title") . " AS company_unit_title, i.archive, i.invoice_type, i.details_file,
						COALESCE(SUM(l.cost::numeric +
                               COALESCE(l.flex_unit_sum::numeric, 0) +
                               COALESCE(l.flex_percentage_sum::numeric, 0)
                        ), 0) AS cost
					FROM invoice AS i 
						LEFT JOIN company_unit AS c ON c.company_unit_id=i.company_unit_id 
						LEFT JOIN invoice_line AS l ON l.invoice_id=i.invoice_id "
            . (count($where) > 0 ? " WHERE " . implode(" AND ", $where) : "")
            . " GROUP BY i.invoice_id, c.title";

        if ($request->IsPropertySet("PageInvoice")) {
            $this->SetCurrentPage($request->GetProperty("PageInvoice"));
        } else {
            $this->SetCurrentPage();
        }

        $this->LoadFromSQL($query);
        $this->PrepareContentBeforeShow();
    }

    /**
     * Returns sum of invoiced vouchers in given month
     *
     * @param $groupCode
     * @param $date
     *
     * @return bool|int
     */
    public static function GetMonthlyVoucherInvoiceAmount($groupCode, $date)
    {
        $specificProductGroup = SpecificProductGroupFactory::CreateByCode($groupCode);
        $mainProductCode = $specificProductGroup->GetMainProductCode();
        if ($specificProductGroup == null) {
            return 0;
        }

        $where = array();
        $where[] = "i.invoice_type = 'voucher_invoice'";
        $where[] = "i.date_from = " . Connection::GetSQLDate(date("Y-m-1", strtotime($date)));
        $where[] = "i.date_to = " . Connection::GetSQLDate(date("Y-m-t", strtotime($date)));
        $where[] = "l.product_id = " . Product::GetProductIDByCode($mainProductCode);
        $where[] = "i.archive != 'Y'";

        $stmt = GetStatement();
        $query = "SELECT SUM(l.cost::numeric) AS cost 
					FROM invoice AS i 
						LEFT JOIN invoice_line AS l ON l.invoice_id=i.invoice_id "
            . (count($where) > 0 ? " WHERE " . implode(" AND ", $where) : "");

        return $stmt->FetchField($query);
    }

    /**
     * Puts additional fields that cannot be loaded by main sql-query
     */
    private function PrepareContentBeforeShow()
    {
        for ($i = 0; $i < $this->GetCountItems(); $i++) {
            if ($this->_items[$i]["invoice_type"] == "voucher_invoice") {
                $this->_items[$i]["cost_with_vat"] = round($this->_items[$i]["cost"] * (Config::GetConfigValueByDate(
                    "voucher_invoice_vat",
                    $this->_items[$i]["date_from"]
                ) / 100 + 1), 2);
            } else {
                $this->_items[$i]["cost_with_vat"] = round($this->_items[$i]["cost"] * (Config::GetConfigValueByDate(
                    "invoice_vat",
                    $this->_items[$i]["date_from"]
                ) / 100 + 1), 2);
            }

            $this->_items[$i]["status_title"] = GetTranslation(
                "invoice-status-" . $this->_items[$i]["status"],
                $this->module
            );
        }
    }

    /**
     * Outputs csv file with company unit's invoice list formatted for Datev
     *
     * @param string $dateFrom start of export period filters invoices by "created" field
     * @param string $dateTo end of export period filters invoices by "created" field
     * @param int $companyUnitID company_unit_id of company unit invoices to be exported of. if is not passed then all company units will be processed
     * @param string $type type of invoice, "invoice" for regular and "voucher_invoice" for voucher invoices
     */
    public function ExportToDatev($dateFrom, $dateTo, $companyUnitID = null, $type = "invoice")
    {
        $dateFrom = $dateFrom ?: "1970-01-01";
        $dateTo = $dateTo ?: GetCurrentDate();

        $dateFromObject = new DateTime($dateFrom);
        //$dateToObject = new DateTime($dateTo);
        $endOfMonthObject = new DateTime(date("t.m.Y", strtotime($dateTo)));

        //load invoice list to be exported
        $where = array();
        $where[] = "i.export_id IS NULL";
        $where[] = "i.archive!='Y'";
        if ($dateFrom) {
            $where[] = "DATE(i.created) >= " . Connection::GetSQLDate($dateFrom);
        }
        if ($dateTo) {
            $where[] = "DATE(i.created) <= " . Connection::GetSQLDateTime($dateTo);
        }
        if ($companyUnitID) {
            $where[] = "i.company_unit_id=" . intval($companyUnitID);
        }
        $where[] = $type == "voucher_invoice" ? "i.invoice_type = 'voucher_invoice'" : "i.invoice_type = 'invoice'";

        $query = "SELECT i.invoice_id, i.created, i.date_from, i.date_to, i.invoice_guid, i.status,
                        COALESCE(SUM(l.cost::numeric +
                               COALESCE(l.flex_unit_sum::numeric, 0) +
                               COALESCE(l.flex_percentage_sum::numeric, 0)
                        ), 0) AS cost,
                        i.invoice_type, c.customer_guid, 
						" . Connection::GetSQLDecryption("c.title") . " AS company_unit_title  
					FROM invoice AS i 
						LEFT JOIN invoice_line AS l ON l.invoice_id=i.invoice_id 
						LEFT JOIN company_unit AS c ON c.company_unit_id=i.company_unit_id "
            . (count($where) > 0 ? " WHERE " . implode(" AND ", $where) : "")
            . " GROUP BY i.invoice_id, c.company_unit_id";
        $this->SetItemsOnPage(0);
        $this->SetOrderBy("export");
        $this->LoadFromSQL($query);
        $this->PrepareContentBeforeShow();

        //build file header
        $fileHeader = array(
            "EXTF",
            //Dateibezeichnung Always (EXTF)
            "510",
            //DATEV PRO (Always 510)
            "21",
            //Versionsnummer (Always 21)
            "Buchungsstapel",
            //Formatname
            "7",
            //Formatversion
            "=" . date("YmdHisv"),
            //Date+ Time. "=" is needed phpspreadsheet to process this value as a string
            "",
            //empty
            "BH",
            //Initial source (Always BH)
            "",
            //empty
            "",
            //empty
            Config::GetConfigValue("export_datev_tax_consultant_id"),
            //Tax Consultant ID
            $type == "voucher_invoice" ? Config::GetConfigValue("export_datev_voucher_client_id") : Config::GetConfigValue("export_datev_client_id"),
            //Customer ID Datev
            date("Y") . "0101",
            //Start Business Year (always 20180101)
            "4",
            //Account length (always 4)
            $dateFromObject->format("Ymd"),
            //Start booking period of this file
            $endOfMonthObject->format("Ymd"),
            //End booking period of this file
            "",
            //empty
            "",
            //empty
            "1",
            //Always like this
            "0",
            //Always like this
            "0",
            //(always 0) Festschreibungskennzahl 0=nein
        );

        //build invoice table header
        $invoiceTableHeader = explode(
            ";",
            "Umsatz (ohne Soll/Haben-Kz);Soll/Haben-Kennzeichen;WKZ Umsatz;Kurs;Basis-Umsatz;WKZ Basis-Umsatz;Konto;Gegenkonto (ohne BU-Schlџssel);BU-Schlџssel;Belegdatum;Belegfeld 1;Belegfeld 2;Skonto;Buchungstext;Postensperre;Diverse Adressnummer;Geschäftspartnerbank;Sachverhalt;Zinssperre;Beleglink;Beleginfo - Art 1;Beleginfo - Inhalt 1;Beleginfo - Art 2;Beleginfo - Inhalt 2;Beleginfo - Art 3;Beleginfo - Inhalt 3;Beleginfo - Art 4;Beleginfo - Inhalt 4;Beleginfo - Art 5;Beleginfo - Inhalt 5;Beleginfo - Art 6;Beleginfo - Inhalt 6;Beleginfo - Art 7;Beleginfo - Inhalt 7;Beleginfo - Art 8;Beleginfo - Inhalt 8;KOST1 - Kostenstelle;KOST2 - Kostenstelle;Kost-Menge;EU-Land u. UStID;EU-Steuersatz;Abw. Versteuerungsart;Sachverhalt L+L;FunktionsergЉnzung L+L;BU 49 Hauptfunktionstyp;BU 49 Hauptfunktionsnummer;BU 49 FunktionsergЉnzung;Zusatzinformation - Art 1;Zusatzinformation- Inhalt 1;Zusatzinformation - Art 2;Zusatzinformation- Inhalt 2;Zusatzinformation - Art 3;Zusatzinformation- Inhalt 3;Zusatzinformation - Art 4;Zusatzinformation- Inhalt 4;Zusatzinformation - Art 5;Zusatzinformation- Inhalt 5;Zusatzinformation - Art 6;Zusatzinformation- Inhalt 6;Zusatzinformation - Art 7;Zusatzinformation- Inhalt 7;Zusatzinformation - Art 8;Zusatzinformation- Inhalt 8;Zusatzinformation - Art 9;Zusatzinformation- Inhalt 9;Zusatzinformation - Art 10;Zusatzinformation- Inhalt 10;Zusatzinformation - Art 11;Zusatzinformation- Inhalt 11;Zusatzinformation - Art 12;Zusatzinformation- Inhalt 12;Zusatzinformation - Art 13;Zusatzinformation- Inhalt 13;Zusatzinformation - Art 14;Zusatzinformation- Inhalt 14;Zusatzinformation - Art 15;Zusatzinformation- Inhalt 15;Zusatzinformation - Art 16;Zusatzinformation- Inhalt 16;Zusatzinformation - Art 17;Zusatzinformation- Inhalt 17;Zusatzinformation - Art 18;Zusatzinformation- Inhalt 18;Zusatzinformation - Art 19;Zusatzinformation- Inhalt 19;Zusatzinformation - Art 20;Zusatzinformation- Inhalt 20;Stџck;Gewicht;Zahlweise;Forderungsart;Veranlagungsjahr;Zugeordnete FЉlligkeit;Skontotyp;Auftragsnummer;Buchungstyp;Ust-Schlџssel (Anzahlungen);EU-Land (Anzahlungen);Sachverhalt L+L (Anzahlungen);EU-Steuersatz (Anzahlungen);Erlљskonto (Anzahlungen);Herkunft-Kz;Leerfeld;KOST-Datum;Mandatsreferenz;Skontosperre;Gesellschaftername;Beteiligtennummer;Identifikationsnummer;Zeichnernummer;Postensperre bis;Bezeichnung SoBil-Sachverhalt;Kennzeichen SoBil-Buchung;Festschreibung;Leistungsdatum;Datum Zuord.Steuerperiode"
        );

        //build invoice table body
        $invoiceTableBody = array();
        foreach ($this->GetItems() as $invoice) {
            $row = array(
                str_replace(".", ",", $invoice["cost_with_vat"]),
                //Total Amount of invoice
                "S",
                //always S
                "EUR",
                //always EUR
                "",
                //empty
                "",
                //empty
                "",
                //empty
                ($type == "voucher_invoice" ? "10" . substr($invoice["customer_guid"], 4)
                    : substr($invoice["customer_guid"], 4)),
                //Customer ID without the first 4 digits of the year
                ($type == "voucher_invoice" ? "8200" : "8400"),
                //Always 8400 for invoice and 8200 for voucher invoice
                "",
                //empty
                date("dm", strtotime($invoice["created"])),
                //day and month of the invoice
                $invoice["invoice_guid"],
                //invoice number
                "",
                //empty
                "",
                //empty
                $invoice["company_unit_title"] . " Rechnungs Nr.: " . $invoice["invoice_guid"],
                //<customer name> Rechnungs Nr.: <Invoice Number>
            );
            for ($i = count($row); $i <= 112; $i++) {
                $row[] = "";
            }
            $row[113] = "0"; //Always 0
            $invoiceTableBody[] = $row;
        }

        //create spreadsheet and write the data
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getActiveSheet()->fromArray($fileHeader, null, "A1");
        $spreadsheet->getActiveSheet()->fromArray($invoiceTableHeader, null, "A2");
        $spreadsheet->getActiveSheet()->fromArray($invoiceTableBody, null, "A3");

        //save and output the file
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Csv($spreadsheet);
        $writer->setDelimiter(";");
        $writer->setEnclosure("");
        $writer->setLineEnding("\r\n");
        $writer->setSheetIndex(0);

        $user = new User();
        $user->LoadBySession();

        $stmt = GetStatement();
        $exportInvoice = new ExportInvoice($this->module);
        $exportInvoice->SetProperty("date_from", $dateFrom);
        $exportInvoice->SetProperty("date_to", $dateTo);
        $exportInvoice->SetProperty("type", $type);
        $exportInvoiceID = $exportInvoice->Create();

        if ($this->GetCountItems() > 0) {
            $invoiceIDs = array_column($this->GetItems(), "invoice_id");
            $query = "UPDATE invoice SET export_id=" . intval($exportInvoiceID) . " WHERE invoice_id IN(" . implode(
                ", ",
                $invoiceIDs
            ) . ")";
            $stmt->Execute($query);
        }

        $exportInvoice->LoadByID($exportInvoiceID);
        $exportNumber = $exportInvoice->GetProperty("export_number");
        $filename = "extf_buchungsstapel_" . date("Ymd") . "_" . $exportNumber . ".csv";

        $tempFilePath = PROJECT_DIR . "var/log/export_datev_" . date("U") . "_" . rand(100, 999) . ".csv";
        $writer->save($tempFilePath);
        $fileStorage = GetFileStorage(CONTAINER__BILLING__EXPORT_INVOICE);
        $fileStorage->MoveToStorage($tempFilePath, EXPORT_INVOICE_DIR, $filename);

        if (!file_exists($tempFilePath)) {
            return;
        }

        unlink($tempFilePath);
    }

    /**
     * Removes invoices from database by provided ids. Also removes their lines.
     *
     * @param array $ids array of invoice_id's
     */
    /*public function Remove($ids)
    {

        if (is_array($ids) && count($ids) > 0)
        {
            $stmt = GetStatement();

            //remove lines
            $invoiceLineIDs = array_keys($stmt->FetchIndexedList("SELECT invoice_line_id FROM invoice_line WHERE invoice_id IN (".implode(", ", Connection::GetSQLArray($ids)).")"));
            $invoiceLineList = new InvoiceLineList($this->module);
            $invoiceLineList->Remove($invoiceLineIDs);

            $query = "DELETE FROM invoice WHERE invoice_id IN (".implode(", ", Connection::GetSQLArray($ids)).")";
            $stmt->Execute($query);

            if($stmt->GetAffectedRows() > 0)
            {
                $this->AddMessage("object-removed", $this->module, array("Count" => $stmt->GetAffectedRows()));
            }
        }

    }*/

    /**
     * Gets invoice list statistics for dashboard
     *
     * @param string $dateFrom start of period filters invoices by "created" field
     * @param string $dateTo end of period filters invoices by "created" field
     * @param string $filter which type of invoices would be icluded in selection, can be "future", "past", all would be included if else
     * @param string $includeFees which type of invoice lines would be icluded in selection, can be "implementation", "recurring", all would be included if else
     * @param string $invoiceType which type of invoice should be included (voucher invoice or regular one)
     *
     * @return array of statistic values for each service
     */
    public static function GetInvoiceListStatisticsForDashboard($dateFrom, $dateTo, $filter, $includeFees, $invoiceType)
    {
        $stmt = GetStatement(DB_MAIN);
        $where = array();
        $where[] = "i.archive!='Y'";
        if ($dateFrom) {
            $where[] = "DATE(i.created) >= " . Connection::GetSQLDateTime($dateFrom);
        }
        if ($dateTo) {
            $where[] = "DATE(i.created) <= " . Connection::GetSQLDateTime($dateTo);
        }
        //exclude invoices for future date or for past date
        if ($filter == "future") {
            $where[] = "DATE(i.date_to) >= DATE(i.created)";
        } elseif ($filter == "past") {
            $where[] = "DATE(i.date_to) <= DATE(i.created)";
        }

        if ($includeFees == "implementation") {
            $where[] = "l.type='implementation'";
        } elseif ($includeFees == "recurring") {
            $where[] = "l.type='recurring'";
        }

        if ($invoiceType == "invoice") {
            $where[] = "i.invoice_type='invoice'";
        } elseif ($invoiceType == "voucher_invoice") {
            $where[] = "i.invoice_type='voucher_invoice'";
        }

        $query = "SELECT pg.code,
                    COALESCE(SUM(l.cost::numeric +
                               COALESCE(l.flex_unit_sum::numeric, 0) +
                               COALESCE(l.flex_percentage_sum::numeric, 0)
                        ), 0) AS cost,
                    l.date_from, l.invoice_id
		        FROM product_group AS pg
                    LEFT JOIN product AS p ON p.group_id=pg.group_id 
                    LEFT JOIN (SELECT l.cost, l.flex_unit_sum, l.flex_percentage_sum, l.product_id, i.date_from, i.invoice_id FROM invoice AS i LEFT JOIN invoice_line AS l ON i.invoice_id=l.invoice_id " . (count($where) > 0 ? " WHERE " . implode(
            " AND ",
            $where
        ) : "") . ") AS l ON l.product_id=p.product_id
                GROUP BY pg.group_id, l.date_from, l.invoice_id
                ORDER BY pg.sort_order";
        $invoiceStatisticsTmp = $stmt->FetchList($query);
        $invoiceStatistics = array();
        foreach ($invoiceStatisticsTmp as $productGroup) {
            if ($invoiceType == "invoice") {
                $vat = Config::GetConfigValueByDate("invoice_vat", $productGroup["date_from"]) / 100 + 1;
            } elseif ($invoiceType == "voucher_invoice") {
                $vat = Config::GetConfigValueByDate("voucher_invoice_vat", $productGroup["date_from"]) / 100 + 1;
            }

            $productGroup["cost"] *= $vat;
            $key = array_search($productGroup["code"], array_column($invoiceStatistics, "code"));
            if ($key === false) {
                $invoiceStatistics[] = ["code" => $productGroup["code"], "cost" => $productGroup["cost"]];
            } else {
                $invoiceStatistics[$key]["cost"] += $productGroup["cost"];
            }
        }

        $colorCodeMap = array("success", "info", "warning", "danger", "puple", "primary", "orange", "success", "info");
        $costAll = 0;
        if (is_array($invoiceStatistics)) {
            $costAll = array_sum(array_column($invoiceStatistics, "cost"));

            array_unshift($invoiceStatistics, array("cost" => $costAll, "code" => "all"));

            for ($i = 0; $i < count($invoiceStatistics); $i++) {
                $invoiceStatistics[$i]["title_translation"] = GetTranslation(
                    "product-group-" . $invoiceStatistics[$i]["code"],
                    "product"
                );

                $invoiceStatistics[$i]["percent"] = $costAll > 0 ? $invoiceStatistics[$i]["cost"] / $costAll * 100 : 0;

                $invoiceStatistics[$i]["color_code"] = $colorCodeMap[$i] ?? "primary";
            }
        }

        return $invoiceStatistics;
    }

    /**
     * NOT Removes invoices from database by provided ids.
     * Just make them inactive.
     *
     * @param array $ids array of invoice_id's
     */
    public function Remove($ids, $createdFrom = "admin")
    {
        if (!is_array($ids) || count($ids) <= 0) {
            return;
        }

        $stmt = GetStatement();

        $query = "UPDATE invoice SET archive='Y' WHERE invoice_id IN (" . implode(
            ", ",
            Connection::GetSQLArray($ids)
        ) . ")";
        $stmt->Execute($query);

        //Save history
        $user = new User();
        $user->LoadBySession();
        $userId = $user->GetProperty("user_id");

        $values = array();
        for ($i = 0; $i < count($ids); $i++) {
            $values[] = "(" . $ids[$i] . ",'archive','Y'," . $userId . ",'" . GetCurrentDateTime() . "'," . Connection::GetSQLString($createdFrom) . ")";
        }
        $stmt1 = GetStatement(DB_CONTROL);
        $query = "INSERT INTO invoice_history (invoice_id, property_name, value, user_id, created, created_from) VALUES" . implode(
            ",",
            $values
        );
        $stmt1->Execute($query);

        if ($stmt->GetAffectedRows() > 0) {
            $this->AddMessage("object-disactivated", $this->module, array("Count" => $stmt->GetAffectedRows()));
        }

        $invoice = new Invoice($this->module);

        foreach ($ids as $invoiceID) {
            //generate archive invoice pdf
            $pdfPath = PROJECT_DIR . "var/log/invoice_" . $invoice->GetProperty("invoice_guid") .
                "_archive_" . GetCurrentDate() . ".pdf";
            $invoice->LoadByID($invoiceID);
            $invoice->GenerateInvoicePDF($invoice, $pdfPath, GetCurrentDate());

            $companyUnit = new CompanyUnit("company");
            $companyUnit->LoadByID($invoice->GetProperty("company_unit_id"));
            $fileName = date("ym", strtotime($invoice->GetProperty("created"))) . "_" .
                $companyUnit->GetProperty("customer_guid") . "_" .
                $invoice->GetProperty("invoice_guid") . ".pdf";

            $fileStorage = GetFileStorage(CONTAINER__BILLING__INVOICE);
            $fileStorage->MoveToStorage($pdfPath, INVOICE_ARCHIVE_DIR, $fileName);

            $stmt = GetStatement(DB_MAIN);
            $query = "SELECT invoice_type FROM invoice WHERE invoice_id=" . Connection::GetSQLString($invoiceID);
            $type = $stmt->FetchField($query);

            if ($type != "voucher_invoice") {
                BillableItemList::removeFromInvoice($invoiceID);
            }

            $query = "SELECT voucher_id FROM voucher WHERE invoice_export_id=" . Connection::GetSQLString($invoiceID);
            $voucherList = array_keys($stmt->FetchIndexedList($query));
            if (count($voucherList) <= 0) {
                continue;
            }

            $query = "UPDATE voucher SET invoice_export_id=NULL WHERE voucher_id IN (" . implode(
                ", ",
                Connection::GetSQLArray($voucherList)
            ) . ")";
            $stmt->Execute($query);
        }
    }

    /**
     * Revert the operation of Remove invoices by provided ids.
     *
     * @param array $ids array of invoice_id's
     */
    public function Activate($ids, $createdFrom = "admin")
    {
        if (!is_array($ids) || count($ids) <= 0) {
            return;
        }

        $stmt = GetStatement();

        $query = "UPDATE invoice SET archive='N' WHERE invoice_id IN (" . implode(
            ", ",
            Connection::GetSQLArray($ids)
        ) . ")";
        $stmt->Execute($query);

        //Save history
        $user = new User();
        $user->LoadBySession();
        $userId = $user->GetIntProperty("user_id");

        $values = array();
        for ($i = 0; $i < count($ids); $i++) {
            $values[] = "(" . $ids[$i] . ",'archive','N'," . $userId . ",'" . GetCurrentDateTime() . "'," . Connection::GetSQLString($createdFrom) . ")";
        }

        $stmt1 = GetStatement(DB_CONTROL);
        $query = "INSERT INTO invoice_history (invoice_id, property_name, value, user_id, created, created_from) VALUES" . implode(
            ",",
            $values
        );

        $stmt1->Execute($query);

        if ($stmt->GetAffectedRows() <= 0) {
            return;
        }

        $this->AddMessage("object-activated", $this->module, array("Count" => $stmt->GetAffectedRows()));
    }
}
