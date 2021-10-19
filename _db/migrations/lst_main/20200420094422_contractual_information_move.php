<?php

use Phinx\Migration\AbstractMigration;

class ContractualInformationMove extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {

        $this->langVarList[] = new LangVar("en", "template", "receipt", "receipt_edit.html", "InternetContractualInfo", "Contractual information Internet");
        $this->langVarList[] = new LangVar("de", "template", "receipt", "receipt_edit.html", "InternetContractualInfo", "Vertragsinformationen Internet");
        $this->langVarList[] = new LangVar("tr", "template", "receipt", "receipt_edit.html", "InternetContractualInfo", "Vertragsinformationen Internet");

        $this->langVarList[] = new LangVar("en", "template", "receipt", "receipt_edit.html", "MobileContractualInfo", "Contractual information Mobile");
        $this->langVarList[] = new LangVar("de", "template", "receipt", "receipt_edit.html", "MobileContractualInfo", "Vertragsinformationen Mobil");
        $this->langVarList[] = new LangVar("tr", "template", "receipt", "receipt_edit.html", "MobileContractualInfo", "Vertragsinformationen Mobil");
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
