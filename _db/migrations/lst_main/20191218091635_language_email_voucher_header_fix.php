<?php

use Phinx\Migration\AbstractMigration;

class LanguageEmailVoucherHeaderFix extends AbstractMigration
{
    private $langVarList = array();
    private $delLangVarList = array();

    public function init()
    {
        $this->delLangVarList[] = new LangVar("de", "template", "company", "voucher_edit.html", "send-email-from-name-bvs", "2KS Gutschein Handels GmbH");
        $this->delLangVarList[] = new LangVar("tr", "template", "company", "voucher_edit.html", "send-email-from-name-bvs", "2KS Gutschein Handels GmbH");
        $this->delLangVarList[] = new LangVar("en", "template", "company", "voucher_edit.html", "send-email-from-name-bvs", "2KS Gutschein Handels GmbH");

        $this->langVarList[] = new LangVar("de", "php", "company", "common", "send-email-from-name-bvs", "2KS Gutschein Handels GmbH");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "send-email-from-name-bvs", "2KS Gutschein Handels GmbH");
        $this->langVarList[] = new LangVar("en", "php", "company", "common", "send-email-from-name-bvs", "2KS Gutschein Handels GmbH");
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
