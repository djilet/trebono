<?php

use Phinx\Migration\AbstractMigration;

class LanguageNewContactFor extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "company", "common", "contact-for-stored_data", "Stored Data");
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "contact-for-stored_data", "Gespeicherte Daten");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "contact-for-stored_data", "Gespeicherte Daten");

        $this->langVarList[] = new LangVar("en", "php", "company", "common", "contact-for-company_unit", "Company unit admin");
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "contact-for-company_unit", "Unternehmenseinheit admin");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "contact-for-company_unit", "Unternehmenseinheit admin");

        $this->langVarList[] = new LangVar("en", "php", "company", "common", "contact-for-employee", "Employee admin");
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "contact-for-employee", "Mitarbeiteradministrator");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "contact-for-employee", "Mitarbeiteradministrator");
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
