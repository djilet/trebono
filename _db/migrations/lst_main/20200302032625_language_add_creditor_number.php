<?php

use Phinx\Migration\AbstractMigration;

class LanguageAddCreditorNumber extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "receipt", "receipt_list.html", "CreditorNumber", "Creditor number");
        $this->langVarList[] = new LangVar("de", "template", "receipt", "receipt_list.html", "CreditorNumber", "Kreditoren Nummer in trebono Buchhaltung");
        $this->langVarList[] = new LangVar("tr", "template", "receipt", "receipt_list.html", "CreditorNumber", "Gläubigernummer");
        $this->langVarList[] = new LangVar("en", "template", "receipt", "receipt_list.html", "FilterCreditorNumberPlaceholder", "Enter creditor number here");
        $this->langVarList[] = new LangVar("de", "template", "receipt", "receipt_list.html", "FilterCreditorNumberPlaceholder", "Eingabe Gläubigernummer");
        $this->langVarList[] = new LangVar("tr", "template", "receipt", "receipt_list.html", "FilterCreditorNumberPlaceholder", "Eingabe Gläubigernummer");
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
