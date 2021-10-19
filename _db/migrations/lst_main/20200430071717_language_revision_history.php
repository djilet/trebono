<?php

use Phinx\Migration\AbstractMigration;

class LanguageRevisionHistory extends AbstractMigration
{
    private $langVarList = array();
    private $deLangVarList = array();

    public function init()
    {
        $this->deLangVarList[] = new LangVar("en", "template", "product", "block_option_value_history.html", "Created", "Date & Time");
        $this->deLangVarList[] = new LangVar("de", "template", "product", "block_option_value_history.html", "Created", "Datum Änderung");
        $this->deLangVarList[] = new LangVar("tr", "template", "product", "block_option_value_history.html", "Created", "Datum Änderung");

        $this->langVarList[] = new LangVar("en", "template", "product", "block_option_value_history.html", "DateFrom", "Valid from date");
        $this->langVarList[] = new LangVar("de", "template", "product", "block_option_value_history.html", "DateFrom", "Gültig ab Datum");
        $this->langVarList[] = new LangVar("tr", "template", "product", "block_option_value_history.html", "DateFrom", "Gültig ab Datum");

        $this->langVarList[] = new LangVar("en", "template", "product", "block_option_value_history.html", "Created", "Change on date");
        $this->langVarList[] = new LangVar("de", "template", "product", "block_option_value_history.html", "Created", "Datum ändern");
        $this->langVarList[] = new LangVar("tr", "template", "product", "block_option_value_history.html", "Created", "Datum ändern");

        $this->langVarList[] = new LangVar("en", "template", "receipt", "block_option_value_history.html", "DateFrom", "Valid from date");
        $this->langVarList[] = new LangVar("de", "template", "receipt", "block_option_value_history.html", "DateFrom", "Gültig ab Datum");
        $this->langVarList[] = new LangVar("tr", "template", "receipt", "block_option_value_history.html", "DateFrom", "Gültig ab Datum");

        $this->langVarList[] = new LangVar("en", "template", "receipt", "block_option_value_history.html", "Created", "Change on date");
        $this->langVarList[] = new LangVar("de", "template", "receipt", "block_option_value_history.html", "Created", "Datum ändern");
        $this->langVarList[] = new LangVar("tr", "template", "receipt", "block_option_value_history.html", "Created", "Datum ändern");

        $this->langVarList[] = new LangVar("en", "template", "billing", "block_property_history.html", "Archive", "Archive");
        $this->langVarList[] = new LangVar("de", "template", "billing", "block_property_history.html", "Archive", "Archiv");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "block_property_history.html", "Archive", "Archiv");
    }

    public function up()
    {
        foreach($this->deLangVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }

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

        foreach($this->deLangVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }
    }
}
