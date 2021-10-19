<?php

use Phinx\Migration\AbstractMigration;

class CompanyContractDocumentFix extends AbstractMigration
{
    public function up()
    {
        $this->table("company_unit_document_history")
            ->addColumn("property_name", "text", ["null" => false])
            ->save();
    }

    public function down()
    {
        $this->table("company_unit_document_history")
            ->removeColumn("property_name")
            ->save();
    }
}
