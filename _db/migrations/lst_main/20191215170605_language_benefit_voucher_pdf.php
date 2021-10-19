<?php

use Phinx\Migration\AbstractMigration;

class LanguageBenefitVoucherPdf extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("de", "template", "company", "voucher_pdf.html", "Category", "Kategorie:");
        $this->langVarList[] = new LangVar("tr", "template", "company", "voucher_pdf.html", "Category", "Kategorie:");
        $this->langVarList[] = new LangVar("en", "template", "company", "voucher_pdf.html", "Category", "Category:");
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
