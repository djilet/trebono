<?php

use Phinx\Migration\AbstractMigration;

class CompanyUnitDocumentsControl extends AbstractMigration
{
    public function up()
    {
        $this->table("company_unit_document_history", ["id" => "id"])
            ->addColumn("document_id", "integer", ["null" => false])
            ->addColumn("value", "text", ["null" => true])
            ->addColumn("created", "timestamp", ["null" => false])
            ->addColumn("user_id", "integer", ["null" => false])
            ->save();
    }

    public function down()
    {
        $this->table("company_unit_document_history")->drop();
    }
}
