<?php

use Phinx\Migration\AbstractMigration;

class LanguageVoucherExportCreatedUser extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "billing", "payroll_list.html", "CreatedUser", "Created user");
        $this->langVarList[] = new LangVar("de", "template", "billing", "payroll_list.html", "CreatedUser", "Benutzer erstellt");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "payroll_list.html", "CreatedUser", "Benutzer erstellt");
    }

    public function up()
    {
        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }
    }

    public function down()
    {
        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
    }
}
