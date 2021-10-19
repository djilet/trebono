<?php

use Phinx\Migration\AbstractMigration;

class LegalReceiptIdSequence extends AbstractMigration
{
    public function up()
    {
        $this->execute("CREATE SEQUENCE IF NOT EXISTS receipt_legal_receipt_id_seq");

        $this->execute("SELECT setval('receipt_legal_receipt_id_seq',
                (SELECT max(legal_receipt_id)+1 FROM receipt), false)");
    }

    public function down()
    {
        $this->execute("DROP SEQUENCE receipt_legal_receipt_id_seq");
    }
}
