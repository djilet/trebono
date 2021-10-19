<?php

use Phinx\Migration\AbstractMigration;

class LanguagePartnerContractIntersection extends AbstractMigration
{
    private $langVarList = array();
    private $delLangVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "partner", "common", "contract-intersection-found", "For the customer %company_unit% and the service %product% there is already a contract from partner %partner%.");
        $this->langVarList[] = new LangVar("de", "php", "partner", "common", "contract-intersection-found", "Für den Kunden %company_unit% und den Service %product% besteht bereits ein Vertrag von partner %partner%.");
        $this->langVarList[] = new LangVar("tr", "php", "partner", "common", "contract-intersection-found", "Für den Kunden %company_unit% und den Service %product% besteht bereits ein Vertrag von partner %partner%.");

        $this->langVarList[] = new LangVar("en", "template", "partner", "partner_edit.html", "ContractIntersectionIgnore", "Do you want to continue?");
        $this->langVarList[] = new LangVar("de", "template", "partner", "partner_edit.html", "ContractIntersectionIgnore", "Wollen Sie fortfahren?");
        $this->langVarList[] = new LangVar("tr", "template", "partner", "partner_edit.html", "ContractIntersectionIgnore", "Wollen Sie fortfahren?");

        $this->delLangVarList[] = new LangVar("en", "php", "partner", "common", "contract-intersection-found", "Contract intersections are not allowed");
        $this->delLangVarList[] = new LangVar("de", "php", "partner", "common", "contract-intersection-found", "Start Datum von %product% stimmt nicht mit dem Start Datum im Kunden überein.");
        $this->delLangVarList[] = new LangVar("tr", "php", "partner", "common", "contract-intersection-found", "Start Datum von %product% stimmt nicht mit dem Start Datum im Kunden überein.");
    }

    public function up()
    {
        foreach($this->delLangVarList as $langVar)
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

        foreach($this->delLangVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }
    }
}
