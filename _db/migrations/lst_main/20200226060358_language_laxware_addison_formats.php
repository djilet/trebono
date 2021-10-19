<?php

use Phinx\Migration\AbstractMigration;

class LanguageLaxwareAddisonFormats extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "company", "company_unit_edit.html", "DatevFormatAddison", "Addison");
        $this->langVarList[] = new LangVar("de", "template", "company", "company_unit_edit.html", "DatevFormatAddison", "Addison");
        $this->langVarList[] = new LangVar("tr", "template", "company", "company_unit_edit.html", "DatevFormatAddison", "Addison");

        $this->langVarList[] = new LangVar("en", "template", "company", "company_unit_edit.html", "DatevFormatLexware", "Lexware");
        $this->langVarList[] = new LangVar("de", "template", "company", "company_unit_edit.html", "DatevFormatLexware", "Lexware");
        $this->langVarList[] = new LangVar("tr", "template", "company", "company_unit_edit.html", "DatevFormatLexware", "Lexware");
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
