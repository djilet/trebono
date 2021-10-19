<?php


use Phinx\Migration\AbstractMigration;

class ConfigExportDatevLugIni extends AbstractMigration
{
	public function up()
    {
        $this->execute("INSERT INTO config (code, value, group_code, editor, updated)
						VALUES (
							'export_datev_lug_ini','',
							'e_export',
							'file',
							".Connection::GetSQLString(GetCurrentDateTime())."
						)");
    }
    
    public function down()
    {
        $this->execute("DELETE FROM config WHERE code='export_datev_lug_ini'");
    }
}
