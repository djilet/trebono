<?php


use Phinx\Migration\AbstractMigration;

class LanguageMigrationContractEndDate extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "product", "block_contract_history.html", "EndDateCreated", "End date created");
        $this->langVarList[] = new LangVar("de", "template", "product", "block_contract_history.html", "EndDateCreated", "Enddatum erstellt");
        $this->langVarList[] = new LangVar("tr", "template", "product", "block_contract_history.html", "EndDateCreated", "Enddatum erstellt");
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
