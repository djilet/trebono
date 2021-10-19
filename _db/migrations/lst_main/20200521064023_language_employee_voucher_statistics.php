<?php

use Phinx\Migration\AbstractMigration;

class LanguageEmployeeVoucherStatistics extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("de", "template", "company", "employee_edit.html", "TabStatistics", "Statistiken");
        $this->langVarList[] = new LangVar("en", "template", "company", "employee_edit.html", "TabStatistics", "Statistics");
        $this->langVarList[] = new LangVar("tr", "template", "company", "employee_edit.html", "TabStatistics", "Statistiken");

        $this->langVarList[] = new LangVar("de", "template", "company", "employee_edit.html", "VoucherStatisticsShow", "Anzeigen");
        $this->langVarList[] = new LangVar("en", "template", "company", "employee_edit.html", "VoucherStatisticsShow", "Show");
        $this->langVarList[] = new LangVar("tr", "template", "company", "employee_edit.html", "VoucherStatisticsShow", "Anzeigen");

        $this->langVarList[] = new LangVar("de", "template", "company", "employee_edit.html", "StatisticAccessNote", "Mitarbeiter speichern, um auf Statistiken zuzugreifen");
        $this->langVarList[] = new LangVar("en", "template", "company", "employee_edit.html", "StatisticAccessNote", "Save employee to access statistics");
        $this->langVarList[] = new LangVar("tr", "template", "company", "employee_edit.html", "StatisticAccessNote", "Mitarbeiter speichern, um auf Statistiken zuzugreifen");
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
