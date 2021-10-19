<?php

use Phinx\Migration\AbstractMigration;

class LanguageContractPropertyHistory extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "product", "block_contract_property_history.html", "PropertyValueHistory", "Revision History");
        $this->langVarList[] = new LangVar("de", "template", "product", "block_contract_property_history.html", "PropertyValueHistory", "Änderungshistorie");
        $this->langVarList[] = new LangVar("tr", "template", "product", "block_contract_property_history.html", "PropertyValueHistory", "Änderungshistorie");

        $this->langVarList[] = new LangVar("en", "template", "product", "block_contract_property_history.html", "ContractID", "Contract ID");
        $this->langVarList[] = new LangVar("de", "template", "product", "block_contract_property_history.html", "ContractID", "Vertrag ID");
        $this->langVarList[] = new LangVar("tr", "template", "product", "block_contract_property_history.html", "ContractID", "Vertrag ID");

        $this->langVarList[] = new LangVar("en", "template", "product", "block_contract_property_history.html", "Value", "Value");
        $this->langVarList[] = new LangVar("de", "template", "product", "block_contract_property_history.html", "Value", "Werte");
        $this->langVarList[] = new LangVar("tr", "template", "product", "block_contract_property_history.html", "Value", "Werte");

        $this->langVarList[] = new LangVar("en", "template", "product", "block_contract_property_history.html", "UserName", "Name");
        $this->langVarList[] = new LangVar("de", "template", "product", "block_contract_property_history.html", "UserName", "Ändernder");
        $this->langVarList[] = new LangVar("tr", "template", "product", "block_contract_property_history.html", "UserName", "Ändernder");

        $this->langVarList[] = new LangVar("en", "template", "product", "block_contract_property_history.html", "Created", "Created");
        $this->langVarList[] = new LangVar("de", "template", "product", "block_contract_property_history.html", "Created", "Datum Änderung");
        $this->langVarList[] = new LangVar("tr", "template", "product", "block_contract_property_history.html", "Created", "Datum Änderung");
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
