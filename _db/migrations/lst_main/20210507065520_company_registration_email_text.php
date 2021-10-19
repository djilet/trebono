<?php

use Phinx\Migration\AbstractMigration;

class CompanyRegistrationEmailText extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("de", "template", "company", "company_unit_edit.html", "RegistrationEmailText", "Anmeldung E-Mail-text");
        $this->langVarList[] = new LangVar("en", "template", "company", "company_unit_edit.html", "RegistrationEmailText", "Registration email text");
        $this->langVarList[] = new LangVar("tr", "template", "company", "company_unit_edit.html", "RegistrationEmailText", "Anmeldung E-Mail-text");
    }

    public function up()
    {
        $this->table("company_unit")
            ->addColumn("reg_email_text", "text", ["null" => true])
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
            ->removeColumn("reg_email_text")
            ->save();

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
    }
}
