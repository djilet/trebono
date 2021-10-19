<?php

use Phinx\Migration\AbstractMigration;

class ReceiptsVatUpdateBvs extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("de", "template", "receipt", "receipt_edit.html", "VATFive", "5%");
        $this->langVarList[] = new LangVar("en", "template", "receipt", "receipt_edit.html", "VATFive", "5%");
        $this->langVarList[] = new LangVar("tr", "template", "receipt", "receipt_edit.html", "VATFive", "5%");
    }

    public function up()
    {
        $this->execute(
            "UPDATE receipt SET vat = 7
                WHERE group_id = " . ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__BENEFIT_VOUCHER) . "
                AND sets_of_goods = " . Connection::GetSQLString("alles für deine Ernährung") . "
                AND created >= " . Connection::GetSQLDateTime("2020-01-01") . "
                AND created <= " . Connection::GetSQLDateTime("2020-06-30")
        );

        $this->execute(
            "UPDATE receipt SET vat = 5
                WHERE group_id = " . ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__BENEFIT_VOUCHER) . "
                AND sets_of_goods = " . Connection::GetSQLString("alles für deine Ernährung") . "
                AND created >= " . Connection::GetSQLDateTime("2020-07-01") . "
                AND created <= " . Connection::GetSQLDateTime("2020-12-31")
        );

        $this->execute(
            "UPDATE receipt SET vat = 7
                WHERE group_id = " . ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__BENEFIT_VOUCHER) . "
                AND sets_of_goods = " . Connection::GetSQLString("alles für deine Ernährung") . "
                AND created >= " . Connection::GetSQLDateTime("2021-01-01")
        );

        $receipts = $this->fetchAll(
            "SELECT receipt_id, vat FROM receipt 
            WHERE group_id = " . ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__BENEFIT_VOUCHER) . "
            AND sets_of_goods = " . Connection::GetSQLString("alles für deine Ernährung") . "
            AND created >= " . Connection::GetSQLDateTime("2020-01-01")
        );

        $historyValues = [];
        foreach ($receipts as $receipt) {
            $historyValues[] = "(" . $receipt["receipt_id"] . ", 'vat', " . $receipt["vat"] . ", " . Connection::GetSQLDateTime(GetCurrentDateTime()) . ", " . SERVICE_USER_ID . ")";
        }

        $stmt = GetStatement(DB_CONTROL);
        $stmt->execute(
            "INSERT INTO receipt_history (receipt_id, property_name, value, created, user_id) VALUES "
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
        $receipts = $this->fetchAll(
            "SELECT receipt_id, vat FROM receipt 
            WHERE group_id = " . ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__BENEFIT_VOUCHER) . "
            AND sets_of_goods = " . Connection::GetSQLString("alles für deine Ernährung") . "
            AND created >= " . Connection::GetSQLDateTime("2020-01-01")
        );

        $receiptIds = array_column($receipts, "receipt_id");

        $this->execute(
            "UPDATE receipt SET vat = 19
            WHERE receipt_id IN (" . implode(", ", $receiptIds) . ")"
        );

        $stmt = GetStatement(DB_CONTROL);
        $stmt->execute(
            "DELETE FROM receipt_history 
            WHERE receipt_id IN (" . implode(", ", $receiptIds) . ") 
            AND property_name = 'vat'
            AND value IN ('5', '7')"
        );

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
    }
}
