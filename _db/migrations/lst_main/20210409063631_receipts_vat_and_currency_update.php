<?php

use Phinx\Migration\AbstractMigration;

class ReceiptsVatAndCurrencyUpdate extends AbstractMigration
{
    public function up()
    {
        $voucherProductGroup = $this->fetchAll("SELECT group_id FROM product_group WHERE voucher='Y'");
        $voucherProductGroupIds = array_column($voucherProductGroup, "group_id");

        $this->execute(
            "UPDATE receipt SET vat = 19, currency_id = " . Currency::GetDefaultID() . "
            WHERE group_id IN (" . implode(", ", $voucherProductGroupIds) . ")
            AND created >= " . Connection::GetSQLDateTime("2020-01-01")
        );

        $this->execute(
            "UPDATE receipt SET vat = 7, currency_id = " . Currency::GetDefaultID() . "
            WHERE group_id = " . ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__FOOD_VOUCHER) . "
            AND receipt_from = 'shop'
            AND created >= " . Connection::GetSQLDateTime("2020-01-01")
        );

        $receipts = $this->fetchAll(
            "SELECT receipt_id, vat, currency_id FROM receipt 
            WHERE group_id IN (" . implode(", ", $voucherProductGroupIds) . ")
            AND created >= " . Connection::GetSQLDateTime("2020-01-01")
        );

        $historyValues = [];
        foreach ($receipts as $receipt) {
            $historyValues[] = "(" . $receipt["receipt_id"] . ", 'vat', " . $receipt["vat"] . ", " . Connection::GetSQLDateTime(GetCurrentDateTime()) . ", " . SERVICE_USER_ID . ")";
        }
        foreach ($receipts as $receipt) {
            $historyValues[] = "(" . $receipt["receipt_id"] . ", 'currency_id', " . $receipt["currency_id"] . ", " . Connection::GetSQLDateTime(GetCurrentDateTime()) . ", " . SERVICE_USER_ID . ")";
        }

        $stmt = GetStatement(DB_CONTROL);
        $stmt->execute(
            "INSERT INTO receipt_history (receipt_id, property_name, value, created, user_id) VALUES "
            . implode(", ", $historyValues) . ";"
        );
    }

    public function down()
    {
        $voucherProductGroup = $this->fetchAll("SELECT group_id FROM product_group WHERE voucher='Y'");
        $voucherProductGroupIds = array_column($voucherProductGroup, "group_id");

        $this->execute(
            "UPDATE receipt SET vat = NULL, currency_id = 0
            WHERE group_id IN (" . implode(", ", $voucherProductGroupIds) . ")"
        );

        $receipts = $this->fetchAll(
            "SELECT receipt_id FROM receipt 
            WHERE group_id IN (" . implode(", ", $voucherProductGroupIds) . ")
            AND created >= " . Connection::GetSQLDateTime("2020-01-01")
        );
        $receiptIds = array_column($receipts, "receipt_id");

        $stmt = GetStatement(DB_CONTROL);
        $stmt->execute(
            "DELETE FROM receipt_history 
            WHERE receipt_id IN (" . implode(", ", $receiptIds) . ") 
            AND property_name IN ('vat', 'currency_id')"
        );
    }
}
