<?php

use Phinx\Migration\AbstractMigration;

class VoucherReceiptTable extends AbstractMigration
{
    public function up()
    {
        $this->table("voucher_receipt", ["id" => "voucher_receipt_id"])
            ->addColumn("voucher_id", "integer", ["null" => false])
            ->addColumn("receipt_id", "integer", ["null" => false])
            ->addColumn("created", "timestamp", ["null" => false])
            ->save();

        $voucherReceiptsList = $this->fetchAll("SELECT voucher_id, receipt_ids FROM voucher WHERE receipt_ids IS NOT NULL");

        $values = array();

        foreach($voucherReceiptsList as $voucherReceipts)
        {
            $receipts = explode(", ", $voucherReceipts["receipt_ids"]);
            foreach($receipts as $receipt)
            {
                $values[] = "('".$voucherReceipts["voucher_id"]."', '".$receipt."', ".Connection::GetSQLString(GetCurrentDateTime()).")";
            }
        }

        $valuesStr = implode(", ", $values);

        $this->execute("INSERT INTO voucher_receipt (voucher_id,receipt_id,created) VALUES ".$valuesStr);
    }

    public function down()
    {
        $this->dropTable("voucher_receipt");
    }
}
