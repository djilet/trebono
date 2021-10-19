<?php

use Phinx\Migration\AbstractMigration;

class LanguageCompanyStatisticFix extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("de", "template", "company", "block_voucher_statistics.html", "Employee", "Mitarbeiter");
        $this->langVarList[] = new LangVar("en", "template", "company", "block_voucher_statistics.html", "Employee", "Employee");
        $this->langVarList[] = new LangVar("tr", "template", "company", "block_voucher_statistics.html", "Employee", "Mitarbeiter");
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
