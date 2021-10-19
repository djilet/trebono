<?php


use Phinx\Migration\AbstractMigration;

class PayrollPdfAndDatevUnitingChanges extends AbstractMigration
{
    public function up()
    {
    	$this->table("payroll")
			->removeColumn("type")
			->changeColumn("file", "string", ["limit" => 255, "null" => true])
			->renameColumn("file", "pdf_file")
			->addColumn("lodas_file", "string", ["limit" => 255, "null" => true])
			->addColumn("lug_file", "string", ["limit" => 255, "null" => true])
			->update();
    }
    
    public function down()
    {
    	$this->table("payroll")
			->addColumn("type", "string", ["limit" => 255, "default" => "pdf"])
			->changeColumn("pdf_file", "string", ["limit" => 255, "null" => false])
			->renameColumn("pdf_file", "file")
			->removeColumn("lodas_file")
			->removeColumn("lug_file")
			->update();
    }
}
