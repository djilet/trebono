<?php

use Phinx\Migration\AbstractMigration;

class LanguageContactForDeutsch extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "contact-for-contract", "Vertrag");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "contact-for-contract", "Vertrag");

        $this->langVarList[] = new LangVar("de", "php", "company", "common", "contact-for-invoice", "Rechnungsempfänger");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "contact-for-invoice", "Rechnungsempfänger");

        $this->langVarList[] = new LangVar("de", "php", "company", "common", "contact-for-payroll_export", "Lohn Export Empfänger");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "contact-for-payroll_export", "Lohn Export Empfänger");

        $this->langVarList[] = new LangVar("de", "php", "company", "common", "contact-for-service", "Service");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "contact-for-service", "Service");

        $this->langVarList[] = new LangVar("de", "php", "company", "common", "contact-for-support", "Support");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "contact-for-support", "Support");
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
