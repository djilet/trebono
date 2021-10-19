<?php

use Phinx\Migration\AbstractMigration;

class LanguageNewContactForAdmin extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "company", "common", "contact-for-company_unit_admin", "Company unit admin");
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "contact-for-company_unit_admin", "Unternehmenseinheit admin");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "contact-for-company_unit_admin", "Unternehmenseinheit admin");

        $this->langVarList[] = new LangVar("en", "php", "company", "common", "contact-for-employee_admin", "Employee admin");
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "contact-for-employee_admin", "Mitarbeiteradministrator");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "contact-for-employee_admin", "Mitarbeiteradministrator");
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
