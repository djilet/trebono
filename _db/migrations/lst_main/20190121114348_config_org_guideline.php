<?php


use Phinx\Migration\AbstractMigration;

class ConfigOrgGuideline extends AbstractMigration
{
    public function up()
    {
        $updated = Connection::GetSQLString(GetCurrentDateTime());
        $this->execute("INSERT INTO config (code, value, group_code, editor, updated)
                                        VALUES ('app_org_guideline', 'test','x_app_license', 'ckeditor', ".$updated.")");
    }
    
    public function down(){

        $this->execute("DELETE FROM config WHERE group_code='app_org_guideline'");
    }
}
