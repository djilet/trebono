<?php

use Phinx\Migration\AbstractMigration;

class ReceiptVatUpdateHistoryFix extends AbstractMigration
{
    public function up()
    {
        $historyValues = [];
        $stmt = GetStatement(DB_CONTROL);

        $receipts = $this->fetchAll(
            "SELECT receipt_id FROM receipt 
                WHERE vat = 16
                AND document_date >= " . Connection::GetSQLDateTime("2020-07-01") . "
                AND document_date <= " . Connection::GetSQLDateTime("2020-12-31")
        );

        $receiptIds = array_column($receipts, "receipt_id");

        $historyReceipts = $stmt->FetchList(
            "SELECT receipt_id FROM receipt_history 
                WHERE receipt_id IN (" . implode(", ", $receiptIds) . ")
                AND property_name = 'vat'
                AND value = 16"
        );

        if ($historyReceipts && count($historyReceipts) > 0) {
            $historyReceiptIds = array_column($historyReceipts, "receipt_id");
            $receiptIds = array_diff($receiptIds, $historyReceiptIds);
        }

        foreach ($receiptIds as $receiptId) {
            $historyValues[] = "(" . $receiptId . ", 'vat', 16, " . Connection::GetSQLDateTime(GetCurrentDateTime()) . ", " . SERVICE_USER_ID . ")";
        }

        $receipts = $this->fetchAll(
            "SELECT receipt_id FROM receipt 
                WHERE vat = 5
                AND document_date >= " . Connection::GetSQLDateTime("2020-07-01") . "
                AND document_date <= " . Connection::GetSQLDateTime("2020-12-31")
        );

        $receiptIds = array_column($receipts, "receipt_id");

        $historyReceipts = $stmt->FetchList(
            "SELECT receipt_id FROM receipt_history 
                WHERE receipt_id IN (" . implode(", ", $receiptIds) . ")
                AND property_name = 'vat'
                AND value = 5"
        );

        if ($historyReceipts && count($historyReceipts) > 0) {
            $historyReceiptIds = array_column($historyReceipts, "receipt_id");
            $receiptIds = array_diff($receiptIds, $historyReceiptIds);
        }

        foreach ($receiptIds as $receiptId) {
            $historyValues[] = "(" . $receiptId . ", 'vat', 5, " . Connection::GetSQLDateTime(GetCurrentDateTime()) . ", " . SERVICE_USER_ID . ")";
        }

        $stmt->execute(
            "INSERT INTO receipt_history (receipt_id, property_name, value, created, user_id) VALUES "
            . implode(", ", $historyValues)
        );

    }

    public function down()
    {
        $receipts = $this->fetchAll(
            "SELECT receipt_id FROM receipt 
                WHERE vat IN (5, 16)
                AND document_date >= " . Connection::GetSQLDateTime("2020-07-01") . "
                AND document_date <= " . Connection::GetSQLDateTime("2020-12-31")
        );

        $receiptIds = array_column($receipts, "receipt_id");

        $stmt = GetStatement(DB_CONTROL);
        $stmt->execute(
            "DELETE FROM receipt_history 
                WHERE receipt_id IN (" . implode(", ", $receiptIds) . ")
                AND property_name = 'vat'
                AND value IN (5, 16)
                AND created > " . Connection::GetSQLDateTime("2021-07-22")
        );
    }
}
