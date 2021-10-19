<?php

use Phinx\Migration\AbstractMigration;

class BenefitVoucherCreditorNumberCompanyUnit extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "company", "company_unit_edit.html", "CreditorNumber", "Creditor number");
        $this->langVarList[] = new LangVar("de", "template", "company", "company_unit_edit.html", "CreditorNumber", "Gläubigernummer");
        $this->langVarList[] = new LangVar("tr", "template", "company", "company_unit_edit.html", "CreditorNumber", "Gläubigernummer");
    }

    public function up()
    {
        $this->table("company_unit")
            ->addColumn("creditor_number", "string", ["length" => 255, "null" => true])
            ->save();

        $bookkeeping = $this->FetchRow("SELECT variable_id FROM language_variable WHERE tag_name='BookkeepingAccountInformation'");
        if (intval($bookkeeping) == 0)
        {
            $this->langVarList[] = new LangVar("de", "template", "company", "company_unit_edit.html", "BookkeepingAccountInformation", "Buchhaltungskontoinformationen");
            $this->langVarList[] = new LangVar("en", "template", "company", "company_unit_edit.html", "BookkeepingAccountInformation", "Bookkeeping Account Information");
            $this->langVarList[] = new LangVar("tr", "template", "company", "company_unit_edit.html", "BookkeepingAccountInformation", "Buchhaltungskontoinformationen");
        }
        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }
    }

    public function down()
    {
        $this->table("company_unit")
            ->removeColumn("creditor_number")
            ->save();

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
    }
}
