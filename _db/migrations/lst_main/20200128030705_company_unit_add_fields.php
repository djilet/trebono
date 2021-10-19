<?php

use Phinx\Migration\AbstractMigration;

class CompanyUnitAddFields extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "company", "company_unit_edit.html", "SEPAService", "SEPA Service Manadats reference");
        $this->langVarList[] = new LangVar("de", "template", "company", "company_unit_edit.html", "SEPAService", "SEPA Service Manadatsreferenz");
        $this->langVarList[] = new LangVar("tr", "template", "company", "company_unit_edit.html", "SEPAService", "SEPA Service Manadatsreferenz");

        $this->langVarList[] = new LangVar("en", "template", "company", "company_unit_edit.html", "SEPAVoucher", "SEPA Voucher Manadats reference");
        $this->langVarList[] = new LangVar("de", "template", "company", "company_unit_edit.html", "SEPAVoucher", "SEPA Gutschein Manadatsreferenz");
        $this->langVarList[] = new LangVar("tr", "template", "company", "company_unit_edit.html", "SEPAVoucher", "SEPA Gutschein Manadatsreferenz");
    }

    public function up()
    {
        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }

        $this->table("company_unit")
            ->addColumn("sepa_service", "string", ["null" => true])
            ->addColumn("sepa_voucher", "string", ["null" => true])
            ->save();
    }

    public function down()
    {
        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }

        $this->table("company_unit")
            ->removeColumn("sepa_service")
            ->removeColumn("sepa_voucher")
            ->save();
    }
}
