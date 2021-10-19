<?php

use Phinx\Migration\AbstractMigration;

class LanguageEmailFromNameForBvs extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("de", "template", "company", "voucher_edit.html", "send-email-from-name-bvs", "2KS Gutschein Handels GmbH");
        $this->langVarList[] = new LangVar("tr", "template", "company", "voucher_edit.html", "send-email-from-name-bvs", "2KS Gutschein Handels GmbH");
        $this->langVarList[] = new LangVar("en", "template", "company", "voucher_edit.html", "send-email-from-name-bvs", "2KS Gutschein Handels GmbH");

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
