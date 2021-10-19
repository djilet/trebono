<?php

/**
 * User: der
 * Date: 18.09.18
 * Time: 16:45
 */

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Report extends LocalObject
{

    var $recordList = array();
    var $filename = "report";
    var $partner_id = 0;
    var $date = null;

    /**
     * Constructor
     *
     * @param string $module Name of context module
     * @param array $data Array of invoice properties to be loaded instantly
     */
    public function Report($data = array())
    {
        parent::LocalObject($data);
    }

    /*
     * Load in $this data for specified partner and date.
     * @param int $partnerID id of partner
     * @param string $time of datetime. According to appropriate php datetime formats.
     * */
    public function LoadByPartner($partnerID, $time = "")
    {
        //Date period
        $this->date = !$time ? date_create() : date_create($time);

        $day = $this->date->format("j");
        $quarterMiddle = array("02-15", "05-15", "08-15", "11-15");

        $begin = clone$this->date;
        $begin->sub(new DateInterval("P3M" . $day . "D"));

        //Concerned data
        $this->partner_id = intval($partnerID);
        $this->filename = $this->date->format("Y-m-d") . "-" . str_replace(
            " ",
            "_",
            Partner::GetTitleByID($partnerID)
        ) . "-report";

        //Main request
        $state1 = GetStatement(DB_CONTROL);
        $query1 = "SELECT pc.partner_id, pc.commission, pc.implementation_fee, pc.long, pc.partner_type,
                          pc.company_unit_id, pc.product_id, pc.start_date, pc.end_date
                    FROM partner_contract pc                
                    WHERE pc.partner_id=" . intval($partnerID) . "
                        AND pc.start_date<'" . $this->date->format("Y-m-d") . "'
                        AND (pc.end_date>'" . $begin->format("Y-m-d") . "' OR pc.end_date IS NULL)
                    ORDER BY company_unit_id";
        $contractList = $state1->FetchList($query1);
        if (!$contractList) {
            return;
        }

        //Necessary fields
        $partnerTypes = Connection::GetSQLArray(array_column($contractList, "partner_type"));
        $companies = Connection::GetSQLArray(array_column($contractList, "company_unit_id"));
        $products = Connection::GetSQLArray(array_column($contractList, "product_id"));

        //Employee list
        $query1 = "SELECT array_agg(employee_id), value AS company_unit_id FROM employee_history 
                      WHERE value_id IN (
                        SELECT value_id FROM employee_history 
                          WHERE property_name='company_unit_id'                           
                            AND value IN(" . implode(",", $companies) . ") 
                        )
                       GROUP BY value";
        $userList = $state1->FetchIndexedList($query1, "company_unit_id");

        //Fill the fields
        $state2 = GetStatement(DB_MAIN);
        $query2 = "SELECT partner_type_id, abbreviation, period, report_date FROM partner_type WHERE partner_type_id IN (" . implode(
            ",",
            $partnerTypes
        ) . ")";
        $partnerTypes = $state2->FetchIndexedList($query2, "partner_type_id");

        $query2 = "SELECT product_id, code FROM product WHERE product_id IN (" . implode(",", $products) . ")";
        $products = $state2->FetchIndexedList($query2, "product_id");

        $query2 = "SELECT company_unit_id, " . Connection::GetSQLDecryption("title") . " AS title, customer_guid, payment_type, invoice_date FROM company_unit WHERE company_unit_id IN (" . implode(
            ",",
            $companies
        ) . ")";
        $companies = $state2->FetchIndexedList($query2, "company_unit_id");

        //Generate $this data
        $this->recordList = array();
        foreach ($contractList as $contract) {
            $record = array();
            if (!isset($contract['partner_type'])) {
                continue;
            }

            if (!isset($companies[$contract['company_unit_id']])) {
                continue;
            }

            if ($partnerTypes[$contract['partner_type']]['report_date'] == "invoice date") {
                if ($day != $companies[$contract['company_unit_id']]['invoice_date']) {
                    continue;
                }
            }
            if ($partnerTypes[$contract['partner_type']]['report_date'] == "quarter middle") {
                if (!in_array($this->date->format("m-d"), $quarterMiddle)) {
                    continue;
                }
            }
            if (
                !in_array(
                    $partnerTypes[$contract['partner_type']]['report_date'],
                    array("invoice date", "quarter middle")
                )
            ) {
                continue;
            }

            if ($partnerTypes[$contract['partner_type']]['period'] == "month") {
                $begin = clone$this->date;
                $begin->modify("-1 month");
                $end = clone$this->date;
                $end->modify("-1 day");
            } elseif ($partnerTypes[$contract['partner_type']]['period'] == "quarter") {
                $begin = clone$this->date;
                $begin->modify("-4 month");
                $begin->modify("-" . ($day - 1) . " day");
                $end = clone$this->date;
                $end->modify("-1 month");
                $end->modify("-" . $day . " day");
            }
            //If the commission length is limited
            if (intval($contract['long'])) {
                $conEnd = date_create($contract['start_date']);
                $conEnd->modify("+" . $contract['long'] . " month");
                if ($conEnd < $begin) {
                    continue;
                }
                if ($conEnd < $end) {
                    $end = $conEnd;
                }
            }

            $users = isset($userList[$contract['company_unit_id']]) ?
                substr($userList[$contract['company_unit_id']]['array_agg'], 1, -1) : "";

            $query = "SELECT invoice_id, created, invoice_guid FROM invoice 
                        WHERE company_unit_id=" . $contract['company_unit_id'] . " AND date_to='" . $end->format("Y-m-d") . "' AND archive != 'Y' AND invoice_type = 'invoice' ORDER BY Created DESC";

            $invoiceList = $state2->FetchList($query);
            foreach ($invoiceList as $invoice) {
                $record['company_unit_id'] = $contract['company_unit_id'];
                $record['invoice_guid'] = $invoice['invoice_guid'];
                $record['invoice_date'] = $invoice['created'];
                $record['customer_id'] = $companies[$contract['company_unit_id']]['customer_guid'];
                $record['customer_name'] = $companies[$contract['company_unit_id']]['title'];
                $record['product_id'] = $contract['product_id'];
                $record['service'] = GetTranslation("product-" . $products[$contract['product_id']]['code'], "product");
                $record['partner_type'] = $partnerTypes[$contract['partner_type']]['abbreviation'];
                $record['timeframe'] = $begin->format("j.n.y") . "-" . $end->format("j.n.y");
                //Check the contract dates
                if ($begin->format("Y-m-d") < $contract['start_date']) {
                    $begin = date_create($contract['start_date']);
                }
                if ($contract['end_date'] && ($end->format("Y-m-d") > $contract['end_date'])) {
                    $end = date_create($contract['end_date']);
                }
                if ($begin > $end) {
                    continue;
                }

                $record['users_begin'] = $users ?
                    $state1->FetchField(
                        "SELECT count(*) FROM employee_contract 
                        WHERE product_id=" . $contract['product_id'] . "
                            AND employee_id IN (" . $users . ")
                            AND start_date<'" . $begin->format("Y-m-d") . "'
                            AND (end_date>'" . $begin->format("Y-m-d") . "' OR end_date IS NULL)
                            AND created<=" . Connection::GetSQLString($invoice['created'])
                    ) :
                    0;
                $record['users_end'] = $users ?
                    $state1->FetchField(
                        "SELECT count(*) FROM employee_contract 
                        WHERE product_id=" . $contract['product_id'] . "
                            AND employee_id IN (" . $users . ")
                            AND start_date<'" . $end->format("Y-m-d") . "'
                            AND (end_date>'" . $end->format("Y-m-d") . "' OR end_date IS NULL)
                            AND (end_date_created<=" . Connection::GetSQLString($invoice['created']) . " OR end_date_created IS NULL)"
                    ) :
                    0;

                $optionList = new OptionList("product");
                $optionList->LoadOptionListForAdmin($contract["product_id"], OPTION_LEVEL_COMPANY_UNIT);
                $optionKeys = array_column($optionList->GetItems(), "code");
                $optionKeys = array_map(static function ($v) {
                    return explode("__", $v)[2];
                }, $optionKeys);
                $options = array_combine($optionKeys, $optionList->_items);

                $record['price'] = Option::GetInheritableOptionValue(
                    OPTION_LEVEL_COMPANY_UNIT,
                    $options['monthly_price']['code'],
                    $contract['company_unit_id'],
                    GetCurrentDateTime()
                ) ?? 0;
                $record['customer_discount'] = Option::GetInheritableOptionValue(
                    OPTION_LEVEL_COMPANY_UNIT,
                    $options['monthly_discount']['code'],
                    $contract['company_unit_id'],
                    GetCurrentDateTime()
                ) ?? 0;
                $record['paid_net'] = 0;
                $lineList = new InvoiceLineList("product");
                $lineList->LoadInvoiceLineList($invoice["invoice_id"]);
                foreach ($lineList->_items as $line) {
                    if ($line['type'] != 'recurring' || $line['product_id'] != $contract['product_id']) {
                        continue;
                    }

                    $record['paid_net'] += $line['cost'];
                }
                $record['commission_rate'] = $contract['commission'] ?? 0;
                $record['commission_value'] = $contract['commission'] * 0.01 * $record['paid_net'];
                $record['revenue'] = "service";
                if ($record['commission_value']) {
                    $this->recordList[] = $record;
                }
                if (!$contract['implementation_fee']) {
                    continue;
                }
                //Additional records for implementation_fee
                $record['service'] = "Implementation " . $record['service'];
                $record['price'] = Option::GetInheritableOptionValue(
                    OPTION_LEVEL_COMPANY_UNIT,
                    $options['implementation_price']['code'],
                    $contract['company_unit_id'],
                    GetCurrentDateTime()
                ) ?? 0;
                $record['customer_discount'] = Option::GetInheritableOptionValue(
                    OPTION_LEVEL_COMPANY_UNIT,
                    $options['implementation_discount']['code'],
                    $contract['company_unit_id'],
                    GetCurrentDateTime()
                ) ?? 0;
                $record['paid_net'] = 0;
                $cost = 0;
                foreach ($lineList->_items as $line) {
                    if ($line['type'] != 'implementation' || $line['product_id'] != $contract['product_id']) {
                        continue;
                    }

                    $record['paid_net'] += $line['cost'];
                    $cost = $line['cost'];
                }
                $record['commission_rate'] = $contract['implementation_fee'] ?? 0;
                $record['commission_value'] = $contract['implementation_fee'] * 0.01 * $cost;
                $record['revenue'] = "implementation";
                if (!$record['commission_value']) {
                    continue;
                }

                $this->recordList[] = $record;
            }
        }
        $customerID = array_column($this->recordList, "customer_id");
        $invoiceID = array_column($this->recordList, "invoice_guid");
        array_multisort($customerID, SORT_ASC, $invoiceID, SORT_DESC, $this->recordList);
    }

    /*
     * Generate xlsx file with $this data and send it to client.
     * @return false if an error arise.
     * */
    public function Export()
    {
        if (!$this->recordList) {
            $this->AddMessage("export-no-records", "partner");

            return false;
        }

        $filename = VAR_DIR . $this->filename . '.xlsx';

        $styleArray = ['font' => ['bold' => true]];
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        for ($a = "A"; $a <= "L"; $a++) {
            $sheet->getColumnDimension($a)->setAutoSize(true);
        }
        $sheet->getRowDimension("1")->setRowHeight(42);
        $sheet->getStyle("A1:Z1")->applyFromArray($styleArray);
        $sheet->getStyle("H2:L100")->getAlignment()->setHorizontal("right");
        $sheet->getStyle("A2:D100")->getAlignment()->setHorizontal("left");
        $sheet->getStyle("A2:D100")->getNumberFormat()->setFormatCode("@");
        $sheet->getStyle("L2:L1000")->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE);

        //TH
        $sheet->setCellValue("A1", "Customer ID");
        $sheet->setCellValue("B1", "Customer Name");
        $sheet->setCellValue("C1", "Invoice id");
        $sheet->setCellValue("D1", "Service / Implementation");
        $sheet->setCellValue("E1", "Timeframe");
        $sheet->setCellValue("F1", "Total No.\nUsers beginning\nof Period");
        $sheet->setCellValue("G1", "Total No.\nUsers end\nof Period");
        $sheet->setCellValue("H1", "List price");
        $sheet->setCellValue("I1", "Customer\nDiscount");
        $sheet->setCellValue("J1", "By Customer\npaid net\nprice in Euro");
        $sheet->setCellValue("K1", "Commission Rate\nin %");
        $sheet->setCellValue("L1", "Commission value\nin Euro net");

        $r = 2;
        foreach ($this->recordList as $record) {
            $sheet->setCellValue("A" . $r, $record['customer_id']);
            $sheet->setCellValue("B" . $r, $record['customer_name']);
            $sheet->setCellValue("C" . $r, $record['invoice_guid']);
            $sheet->setCellValue("D" . $r, $record['service']);
            $sheet->setCellValue("E" . $r, $record['timeframe']);
            $sheet->setCellValue("F" . $r, $record['users_begin']);
            $sheet->setCellValue("G" . $r, $record['users_end']);
            $sheet->setCellValue("H" . $r, number_format($record['price'], 2, ",", ""));
            $sheet->setCellValue("I" . $r, $record['customer_discount'] . " %");
            $sheet->setCellValue("J" . $r, number_format($record['paid_net'], 2, ",", ""));
            $sheet->setCellValue("K" . $r, $record['commission_rate'] . " %");
            $sheet->setCellValue("L" . $r, $record['commission_value']);
            $r++;
        }

        $writer = new Xlsx($spreadsheet);
        try {
            $writer->save($filename);
        } catch (\Exception $exc) {
            $this->AddError("error-save-excel", "partner");

            return false;
        }

        $fileStorage = GetFileStorage(CONTAINER__PARTNER);
        $fileStorage->Remove(REPORT_DIR . $this->filename . ".xlsx");
        if (!$fileStorage->MoveToStorage($filename, REPORT_DIR, $this->filename . ".xlsx")) {
            $this->LoadErrorsFromObject($fileStorage);
            if (!$fileStorage->HasErrors()) {
                $this->AddError("error-filestorage", "partner");
            }

            return false;
        }

        $file = array();
        $file['ExportFile'] = $this->filename . ".xlsx";
        PrepareDownloadPath($file, "ExportFile", REPORT_DIR, CONTAINER__PARTNER);
        if (!isset($file['ExportFile_download_path'])) {
            $this->AddError("error-download", "partner");

            return false;
        }

        return $file['ExportFile_download_path'];
    }

    /*
     * Generate simple html page with $this data.
     * */
    public function Print()
    {
        echo "<table>";
        echo "<tr><th>Customer ID</th><th>Customer</th><th>Service</th><th>Timeframe</th><th>Users beginning</th><th>Total No.</th>
                <th>List price</th><th>Customer discount</th><th>Paid net</th><th>Commission Rate</th><th>Commission value</th></tr>";
        foreach ($this->recordList as $record) {
            echo "<tr>";
            echo "<td>" . $record['customer_id'] . "</td>";
            echo "<td>" . $record['customer_name'] . "</td>";
            echo "<td>" . $record['service'] . "</td>";
            echo "<td>" . $record['timeframe'] . "</td>";
            echo "<td>" . $record['users_begin'] . "</td>";
            echo "<td>" . $record['users_end'] . "</td>";
            echo "<td>" . number_format($record['price'], 2) . " </td>";
            echo "<td>" . $record['customer_discount'] . " %</td>";
            echo "<td>" . number_format($record['paid_net'], 2) . " </td>";
            echo "<td>" . $record['commission_rate'] . " %</td>";
            echo "<td>" . number_format($record['commission_value'], 2) . " </td>";
            echo "</tr>";
        }
        echo "</table>";
        exit;
    }

    /*
     * Save commission information to DB
     * */
    public function SaveCommissionLines()
    {
        $stmt = GetStatement(DB_MAIN);
        $query = "SELECT * FROM commission_line WHERE partner_id=" . $this->partner_id . " AND date='" . $this->date->format('Y-m-d') . "'";
        $updateList = $stmt->FetchList($query);
        $updateKeys = array();
        foreach ($updateList as $key => $item) {
            $updateKeys[$key] = $item['company_unit_id'] . "_" . $item['product_id'] . "_" . $item['revenue'];
        }
        $updateList = array_combine($updateKeys, $updateList);

        $values = array();
        $updates = array();
        foreach ($this->recordList as $record) {
            if (
                in_array(
                    $record['company_unit_id'] . "_" . $record['product_id'] . "_" . $record['revenue'],
                    $updateKeys
                )
            ) {
                $updates[] = "UPDATE commission_line SET value=" . $record['commission_value'] . " 
                    WHERE commission_line_id=" . $updateList[$record['company_unit_id'] . "_" . $record['product_id'] . "_" . $record['revenue']]['commission_line_id'];
            } else {
                $values[] = "(" . $this->partner_id . "," . intval($record['product_id']) . "," . intval($record['company_unit_id']) . ",'" .
                    $record['partner_type'] . "'," . $record['commission_value'] . ",'" . $this->date->format('Y-m-d') . "', '" . $record['revenue'] . "')";
            }
        }

        if ($values) {
            $stmt = GetStatement(DB_MAIN);
            $query = "INSERT INTO commission_line (partner_id, product_id, company_unit_id, type, value, date, revenue)
                  VALUES " . implode(",", $values);
            if (!$stmt->Execute($query)) {
                $this->AddError("sql-error");
            }
        }
        foreach ($updates as $query) {
            if ($stmt->Execute($query)) {
                continue;
            }

            $this->AddError("sql-error");
        }
    }
}
