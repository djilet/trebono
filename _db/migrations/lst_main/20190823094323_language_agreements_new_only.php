<?php


use Phinx\Migration\AbstractMigration;
use Phinx\Util\Literal;

class LanguageAgreementsNewOnly extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "agreements", "contracts.html", "NewContractsOnly", "Apply only to new contracts");
        $this->langVarList[] = new LangVar("de", "template", "agreements", "contracts.html", "NewContractsOnly", "Nur auf neue Verträge anwenden");
        $this->langVarList[] = new LangVar("tr", "template", "agreements", "contracts.html", "NewContractsOnly", "Nur auf neue Verträge anwenden");

        $this->langVarList[] = new LangVar("en", "template", "agreements", "partial_agreements_history_list.html", "NewOnly", "New only");
        $this->langVarList[] = new LangVar("de", "template", "agreements", "partial_agreements_history_list.html", "NewOnly", "Nur neu");
        $this->langVarList[] = new LangVar("tr", "template", "agreements", "partial_agreements_history_list.html", "NewOnly", "Nur neu");
    }

    public function up()
    {
        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }

        $this->table("agreements")
            ->addColumn("new_only", Literal::from("flag"), ["null" => false, "default" => "N"])
            ->save();
    }

    public function down()
    {
        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }

        $this->table("agreements")
            ->removeColumn("new_only")
            ->save();
    }
}
