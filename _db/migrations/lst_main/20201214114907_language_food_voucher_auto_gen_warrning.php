<?php

use Phinx\Migration\AbstractMigration;

class LanguageFoodVoucherAutoGenWarrning extends AbstractMigration
{
    private $langVarList = array();
    private $delLangVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "product", "common", "fvs-auto-generation-recurring-warning", "You turned on the auto generation, but some employees have recurring vouchers.");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "fvs-auto-generation-recurring-warning", "Sie haben die automatische Generierung aktiviert, aber einige Mitarbeiter haben wiederkehrende Gutscheine.");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "fvs-auto-generation-recurring-warning", "Sie haben die automatische Generierung aktiviert, aber einige Mitarbeiter haben wiederkehrende Gutscheine.");

        $this->langVarList[] = new LangVar("en", "php", "product", "common", "fvs-auto-generation-recurring-warning-employee", "The voucher limit for employee %employee_name% will be exceeded in %month% by %count% vouchers.");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "fvs-auto-generation-recurring-warning-employee", "Das Gutscheinlimit für Mitarbeiter %employee_name% wird in %month% um %count% Gutscheine überschritten.");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "fvs-auto-generation-recurring-warning-employee", "Das Gutscheinlimit für Mitarbeiter %employee_name% wird in %month% um %count% Gutscheine überschritten.");
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
