<?php

use Phinx\Migration\AbstractMigration;

class LanguageStatisticsYearlyApprove extends AbstractMigration
{
    private $langVarList = array();
    private $delLangVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "statistics", "block_statistics.html", "Approved", "Approved / Not exported");
        $this->langVarList[] = new LangVar("de", "template", "statistics", "block_statistics.html", "Approved", "Genehmigt / Nicht exportiert");
        $this->langVarList[] = new LangVar("tr", "template", "statistics", "block_statistics.html", "Approved", "Genehmigt / Nicht exportiert");

        $this->langVarList[] = new LangVar("en", "template", "statistics", "block_statistics.html", "ApprovedYearly", "Approved / Exported");
        $this->langVarList[] = new LangVar("de", "template", "statistics", "block_statistics.html", "ApprovedYearly", "Genehmigt / Exportiert");
        $this->langVarList[] = new LangVar("tr", "template", "statistics", "block_statistics.html", "ApprovedYearly", "Genehmigt / Exportiert");

        $this->delLangVarList[] = new LangVar("en", "template", "statistics", "block_statistics.html", "Approved", "Approved");
        $this->delLangVarList[] = new LangVar("de", "template", "statistics", "block_statistics.html", "Approved", "Genehmigt");
        $this->delLangVarList[] = new LangVar("tr", "template", "statistics", "block_statistics.html", "Approved", "Genehmigt");
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
