<?php


use Phinx\Migration\AbstractMigration;

class LegalReceiptId extends AbstractMigration
{
    public function up()
    {
		$this->table("receipt")
			->addColumn("legal_receipt_id", "integer", ["null" => true, "default" => null])
			->addIndex(["legal_receipt_id"], ["unique" => true, "name" => "receipt_legal_receipt_id"])
			->save();
		
		//fetch receipt_ids from receipt_file because receipts without photos shouldn't have legal_receipt_id
		$receiptList = $this->fetchAll("SELECT receipt_id FROM receipt_file GROUP BY receipt_id ORDER BY receipt_id ASC");
		foreach($receiptList as $key => $receipt)
		{
			$receiptID = $receipt["receipt_id"];
			
			$row = $this->fetchRow("SELECT MAX(legal_receipt_id) AS max_legal_receipt_id FROM receipt WHERE legal_receipt_id IS NOT NULL");
			$max = $row["max_legal_receipt_id"]; 
			$next = $max ? $max + 1 : 1;
			
			$this->execute("UPDATE receipt SET legal_receipt_id=".intval($next)." 
				WHERE receipt_id=".intval($receipt["receipt_id"])." AND legal_receipt_id IS NULL");
		}
		
		$this->execute("UPDATE config SET value=REPLACE(value, '%receipt_id%', '%legal_receipt_id%')");
    }
    
    public function down()
    {
		$this->table("receipt")
			->removeIndexByName("receipt_legal_receipt_id")
			->removeColumn("legal_receipt_id")
			->save();
		
		$this->execute("UPDATE config SET value=REPLACE(value, '%legal_receipt_id%', '%receipt_id%')");
    }
}
