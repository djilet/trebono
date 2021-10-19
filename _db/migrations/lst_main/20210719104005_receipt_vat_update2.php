<?php

use Phinx\Migration\AbstractMigration;

class ReceiptVatUpdate2 extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("de", "template", "receipt", "receipt_edit.html", "VATSixteen", "16%");
        $this->langVarList[] = new LangVar("en", "template", "receipt", "receipt_edit.html", "VATSixteen", "16%");
        $this->langVarList[] = new LangVar("tr", "template", "receipt", "receipt_edit.html", "VATSixteen", "16%");
    }

    public function up()
    {
        $historyValues = [];

        $receipts = $this->fetchAll(
            "SELECT receipt_id, vat FROM receipt 
                WHERE vat=19
                AND document_date >= " . Connection::GetSQLDateTime("2020-07-01") . "
                AND document_date <= " . Connection::GetSQLDateTime("2020-12-31")
        );

        foreach ($receipts as $receipt) {
            $historyValues[] = "(" . $receipt["receipt_id"] . ", 'vat', 16, " . Connection::GetSQLDateTime(GetCurrentDateTime()) . ", " . SERVICE_USER_ID . ")";
        }

        $receipts = $this->fetchAll(
            "SELECT receipt_id, vat FROM receipt 
                WHERE vat=7
                AND document_date >= " . Connection::GetSQLDateTime("2020-07-01") . "
                AND document_date <= " . Connection::GetSQLDateTime("2020-12-31")
        );

        foreach ($receipts as $receipt) {
            $historyValues[] = "(" . $receipt["receipt_id"] . ", 'vat', 5, " . Connection::GetSQLDateTime(GetCurrentDateTime()) . ", " . SERVICE_USER_ID . ")";
        }

        $this->execute(
            "UPDATE receipt SET vat = 16
                WHERE vat=19
                AND document_date >= " . Connection::GetSQLDateTime("2020-07-01") . "
                AND document_date <= " . Connection::GetSQLDateTime("2020-12-31")
        );

        $this->execute(
            "UPDATE receipt SET vat = 5
                WHERE vat=7
                AND document_date >= " . Connection::GetSQLDateTime("2020-07-01") . "
                AND document_date <= " . Connection::GetSQLDateTime("2020-12-31")
        );

        $stmt = GetStatement(DB_CONTROL);
        $stmt->execute(
            "INSERT INTO receipt_history (receipt_id, property_name, value, document_date, user_id) VALUES "
            . implode(", ", $historyValues) . ";"
        );

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }
    }

    public function down()
    {
        $receipts16 = $this->fetchAll(
            "SELECT receipt_id FROM receipt 
                WHERE vat=16
                AND document_date >= " . Connection::GetSQLDateTime("2020-07-01") . "
                AND document_date <= " . Connection::GetSQLDateTime("2020-12-31")
        );

        $receipts5 = $this->fetchAll(
            "SELECT receipt_id, vat FROM receipt 
                WHERE vat=5
                AND document_date >= " . Connection::GetSQLDateTime("2020-07-01") . "
                AND document_date <= " . Connection::GetSQLDateTime("2020-12-31")
        );

        $receiptIds = array_column(array_merge($receipts16, $receipts5), "receipt_id");

        $stmt = GetStatement(DB_CONTROL);
        $stmt->execute(
            "DELETE FROM receipt_history 
            WHERE receipt_id IN (" . implode(", ", $receiptIds) . ")");

        $voucherProductGroup = $this->fetchAll("SELECT group_id FROM product_group WHERE voucher='Y'");
        $voucherProductGroupIds = array_column($voucherProductGroup, "group_id");

        $this->execute(
            "UPDATE receipt SET vat = 19
            WHERE group_id IN (" . implode(", ", $voucherProductGroupIds) . ")
            AND document_date >= " . Connection::GetSQLDateTime("2020-07-01") . "
            AND document_date <= " . Connection::GetSQLDateTime("2020-12-31")
        );

        $this->execute(
            "UPDATE receipt SET vat = 5
            WHERE sets_of_goods = " . Connection::GetSQLString("alles für deine Ernährung") . "
            AND document_date >= " . Connection::GetSQLDateTime("2020-07-01") . "
            AND document_date <= " . Connection::GetSQLDateTime("2020-12-31")
        );

        $this->execute(
            "UPDATE receipt SET vat = 7
            WHERE group_id = " . ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__FOOD_VOUCHER) . "
            AND receipt_from = 'shop'
            AND document_date >= " . Connection::GetSQLDateTime("2020-07-01") . "
            AND document_date <= " . Connection::GetSQLDateTime("2020-12-31")
        );

        $receipts = $this->fetchAll(
            "SELECT receipt_id, vat, currency_id FROM receipt 
            WHERE group_id IN (" . implode(", ", $voucherProductGroupIds) . ")
            AND document_date >= " . Connection::GetSQLDateTime("2020-07-01") . "
            AND document_date <= " . Connection::GetSQLDateTime("2020-12-31")
        );

        $historyValues = [];
        foreach ($receipts as $receipt) {
            $historyValues[] = "(" . $receipt["receipt_id"] . ", 'vat', " . $receipt["vat"] . ", " . Connection::GetSQLDateTime(GetCurrentDateTime()) . ", " . SERVICE_USER_ID . ")";
        }

        $stmt = GetStatement(DB_CONTROL);
        $stmt->execute(
            "INSERT INTO receipt_history (receipt_id, property_name, value, document_date, user_id) VALUES "
            . implode(", ", $historyValues) . ";"
        );

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
    }
}
