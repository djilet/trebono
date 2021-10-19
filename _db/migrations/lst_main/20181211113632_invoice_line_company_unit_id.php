<?php


use Phinx\Migration\AbstractMigration;

class InvoiceLineCompanyUnitId extends AbstractMigration
{
    public function up()
    {
        $this->execute("ALTER TABLE invoice_line ADD COLUMN company_unit_id integer");
        $this->execute("UPDATE invoice_line SET company_unit_id=s.company_unit_id FROM (SELECT i.company_unit_id, il.invoice_line_id FROM invoice_line il LEFT JOIN invoice i ON il.invoice_id=i.invoice_id) s WHERE invoice_line.invoice_line_id=s.invoice_line_id");
    }

    public function down(){
        $this->execute("ALTER TABLE invoice_line DROP COLUMN company_unit_id");
    }
}
