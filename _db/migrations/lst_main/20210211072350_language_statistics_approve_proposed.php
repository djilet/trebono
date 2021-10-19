<?php

use Phinx\Migration\AbstractMigration;

class LanguageStatisticsApproveProposed extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "statistics", "block_statistics.html", "ApproveProposed", "Approve proposed");
        $this->langVarList[] = new LangVar("de", "template", "statistics", "block_statistics.html", "ApproveProposed", "Beleg bestätigen");
        $this->langVarList[] = new LangVar("tr", "template", "statistics", "block_statistics.html", "ApproveProposed", "Beleg bestätigen");
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
