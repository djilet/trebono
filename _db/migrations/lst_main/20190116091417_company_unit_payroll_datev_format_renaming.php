<?php


use Phinx\Migration\AbstractMigration;

class CompanyUnitPayrollDatevFormatRenaming extends AbstractMigration
{
    public function up()
    {
		$this->execute("UPDATE company_unit SET datev_format='lodas' WHERE datev_format='logas'");
		
		$this->table("company_unit")
			->changeColumn("datev_format", "string", ["limit" => 255, "default" => "lodas"])
			->update();
    }
    
    public function down()
    {
    	$this->execute("UPDATE company_unit SET datev_format='logas' WHERE datev_format='lodas'");
		
		$this->table("company_unit")
			->changeColumn("datev_format", "string", ["limit" => 255, "default" => "logas"])
			->update();
    }
}
