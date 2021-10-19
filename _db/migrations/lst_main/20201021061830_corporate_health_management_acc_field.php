<?php

use Phinx\Migration\AbstractMigration;

class CorporateHealthManagementAccField extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("de", "template", "company", "company_unit_edit.html", "AccCorporateHealthManagement", "LAN Betriebliches Gesundheitsmanagement");
        $this->langVarList[] = new LangVar("en", "template", "company", "company_unit_edit.html", "AccCorporateHealthManagement", "ACC Corporate Health Management");
        $this->langVarList[] = new LangVar("tr", "template", "company", "company_unit_edit.html", "AccCorporateHealthManagement", "LAN Betriebliches Gesundheitsmanagement");

        $this->langVarList[] = new LangVar("de", "template", "company", "employee_edit.html", "AccCorporateHealthManagement", "LAN Betriebliches Gesundheitsmanagement");
        $this->langVarList[] = new LangVar("en", "template", "company", "employee_edit.html", "AccCorporateHealthManagement", "ACC Corporate Health Management");
        $this->langVarList[] = new LangVar("tr", "template", "company", "employee_edit.html", "AccCorporateHealthManagement", "LAN Betriebliches Gesundheitsmanagement");
    }

    public function up()
    {
        $this->table("company_unit")
            ->addColumn("acc_corporate_health_management", "string", ["length" => 255, "null" => true, "after" => "acc_gift"])
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
            ->removeColumn("acc_corporate_health_management")
            ->save();

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
    }
}
