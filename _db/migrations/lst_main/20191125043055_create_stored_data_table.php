<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Util\Literal;

class CreateStoredDataTable extends AbstractMigration
{
    public function up()
    {
        $this->table("stored_data", ["id" => "stored_data_id"])
        ->addColumn("company_unit_id", "integer", ["null" => false])
        ->addColumn("created", "timestamp", ["null" => false])
        ->addColumn("date_from", "date", ["null" => false])
        ->addColumn("date_to", "date", ["null" => false])
        ->addColumn("services", "string", ["length" => 255])
        ->addColumn("employees", "text")
        ->addColumn("status", "string", ["length" => 255])
        ->addColumn("archive", Literal::from("flag"), ["null" => false, "default" => "N"])
        ->save();
    }
    
    public function down()
    {
        $this->dropTable("stored_data");
    }
}
