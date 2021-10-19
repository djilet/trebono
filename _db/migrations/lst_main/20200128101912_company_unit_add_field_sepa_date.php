<?php

use Phinx\Migration\AbstractMigration;

class CompanyUnitAddFieldSepaDate extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "company", "company_unit_edit.html", "SEPAServiceDate", "SEPA signiture Date Base SEPA");
        $this->langVarList[] = new LangVar("de", "template", "company", "company_unit_edit.html", "SEPAServiceDate", "SEPA Unterschrift Datum Basis SEPA");
        $this->langVarList[] = new LangVar("tr", "template", "company", "company_unit_edit.html", "SEPAServiceDate", "SEPA Unterschrift Datum Basis SEPA");

        $this->langVarList[] = new LangVar("en", "template", "company", "company_unit_edit.html", "SEPAVoucherDate", "SEPA Signiture date company SEPA");
        $this->langVarList[] = new LangVar("de", "template", "company", "company_unit_edit.html", "SEPAVoucherDate", "SEPA Unterschrift Datum Firma SEPA");
        $this->langVarList[] = new LangVar("tr", "template", "company", "company_unit_edit.html", "SEPAVoucherDate", "SEPA Unterschrift Datum Firma SEPA");
    }

    public function up()
    {
        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }

        $this->table("company_unit")
            ->addColumn("sepa_service_date", "timestamp", ["null" => false, "default" => "2020-01-01 00:00:00"])
            ->addColumn("sepa_voucher_date", "timestamp", ["null" => true, "default" => "2020-01-01 00:00:00"])
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
            ->removeColumn("sepa_service_date")
            ->removeColumn("sepa_voucher_date")
            ->save();
    }
}
