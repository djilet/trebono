<?php

use Phinx\Migration\AbstractMigration;

class LanguageResendRegistrationEmail extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "company", "employee_edit.html", "ResendForEmployeeAdmin", "Resend registration e-mail");
        $this->langVarList[] = new LangVar("de", "template", "company", "employee_edit.html", "ResendForEmployeeAdmin", "Senden Sie die Registrierungs-E-Mail erneut");
        $this->langVarList[] = new LangVar("tr", "template", "company", "employee_edit.html", "ResendForEmployeeAdmin", "Senden Sie die Registrierungs-E-Mail erneut");
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
