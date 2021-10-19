<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Util\Literal;

class ContactSendingPdfInvoiceOption extends AbstractMigration
{
    public function up()
    {
        $this->table("contact")
            ->addColumn("sending_pdf_invoice", Literal::from("flag"), ["null" => false, "default" => "N"])
            ->save();
    }

    public function down()
    {
        $stmt = GetStatement(DB_CONTROL);
        $stmt->execute("DELETE FROM contact_history WHERE property_name='sending_pdf_invoice'");

        $this->table("contact")
            ->removeColumn("sending_pdf_invoice")
            ->save();
    }
}
