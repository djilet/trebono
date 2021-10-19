<?php


use Phinx\Migration\AbstractMigration;

class LanguageWebApiFields extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "company", "block_property_history.html", "ChangedByApi", "Changed via API");
        $this->langVarList[] = new LangVar("de", "template", "company", "block_property_history.html", "ChangedByApi", "Geändert über API");
        $this->langVarList[] = new LangVar("tr", "template", "company", "block_property_history.html", "ChangedByApi", "Geändert über API");

        $this->langVarList[] = new LangVar("en", "template", "product", "block_option_value_history.html", "ChangedByApi", "Changed via API");
        $this->langVarList[] = new LangVar("de", "template", "product", "block_option_value_history.html", "ChangedByApi", "Geändert über API");
        $this->langVarList[] = new LangVar("tr", "template", "product", "block_option_value_history.html", "ChangedByApi", "Geändert über API");

        $this->langVarList[] = new LangVar("en", "template", "core", "block_property_history.html", "ChangedByApi", "Changed via API");
        $this->langVarList[] = new LangVar("de", "template", "core", "block_property_history.html", "ChangedByApi", "Geändert über API");
        $this->langVarList[] = new LangVar("tr", "template", "core", "block_property_history.html", "ChangedByApi", "Geändert über API");

        $this->langVarList[] = new LangVar("en", "template", "product", "block_contract_history.html", "StartUserWebApi", "Start changed via API");
        $this->langVarList[] = new LangVar("de", "template", "product", "block_contract_history.html", "StartUserWebApi", "Start über API geändert");
        $this->langVarList[] = new LangVar("tr", "template", "product", "block_contract_history.html", "StartUserWebApi", "Start über API geändert");

        $this->langVarList[] = new LangVar("en", "template", "product", "block_contract_history.html", "EndUserWebApi", "End changed via API");
        $this->langVarList[] = new LangVar("de", "template", "product", "block_contract_history.html", "EndUserWebApi", "Ende über API geändert");
        $this->langVarList[] = new LangVar("tr", "template", "product", "block_contract_history.html", "EndUserWebApi", "Ende über API geändert");

        $this->langVarList[] = new LangVar("en", "php", "core", "common", "user-archive-y-message-web-api", "This %entity% was deleted by %username% at %datetime% via API");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "user-archive-y-message-web-api", "%entity% wurde von User %username% am %datetime% de-aktiviert über API");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "user-archive-y-message-web-api", "%entity% wurde von User %username% am %datetime% de-aktiviert über API");

        $this->langVarList[] = new LangVar("en", "php", "core", "common", "user-archive-n-message-web-api", "This %entity% was activated by %username% at %datetime% via API");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "user-archive-n-message-web-api", "%entity% wurde von User %username% am %datetime% aktiviert über API");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "user-archive-n-message-web-api", "%entity% wurde von User %username% am %datetime% aktiviert über API");
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
