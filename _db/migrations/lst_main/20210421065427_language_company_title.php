<?php

use Phinx\Migration\AbstractMigration;

class LanguageCompanyTitle extends AbstractMigration
{
    private $langVarList = [];

    public function init()
    {
        $this->langVarList = [
            new LangVar("en", "php", "core", "common", "benefit-portal-for-the-company", "Benefit portal for the company"),
            new LangVar("de", "php", "core", "common", "benefit-portal-for-the-company", "Das Unternehmens Benefit portal von"),
            new LangVar("tr", "php", "core", "common", "benefit-portal-for-the-company", "Das Unternehmens Benefit portal von"),
        ];
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
