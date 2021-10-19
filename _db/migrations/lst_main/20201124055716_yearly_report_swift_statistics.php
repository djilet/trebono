<?php

use Phinx\Migration\AbstractMigration;

class YearlyReportSwiftStatistics extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "company_yearly_report", "company_yearly_report");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "company_yearly_report", "company_yearly_report");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "company_yearly_report", "company_yearly_report");
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
