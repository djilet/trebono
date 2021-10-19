<?php

use Phinx\Migration\AbstractMigration;

class GiftNewAcc extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("de", "template", "company", "company_unit_edit.html", "AccGift", "Konto Geschenk");
        $this->langVarList[] = new LangVar("en", "template", "company", "company_unit_edit.html", "AccGift", "ACC Gift");
        $this->langVarList[] = new LangVar("tr", "template", "company", "company_unit_edit.html", "AccGift", "Konto Geschenk");

        $this->langVarList[] = new LangVar("de", "template", "company", "employee_edit.html", "AccGift", "Konto Geschenk");
        $this->langVarList[] = new LangVar("en", "template", "company", "employee_edit.html", "AccGift", "ACC Gift");
        $this->langVarList[] = new LangVar("tr", "template", "company", "employee_edit.html", "AccGift", "Konto Geschenk");
    }

    public function up()
    {
        $this->table("company_unit")
            ->addColumn("acc_gift", "string", ["length" => 255, "null" => true])
            ->save();

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }
    }

    public function down()
    {
        $this->table("company_unit")
            ->removeColumn("acc_gift")
            ->save();

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
    }
}
