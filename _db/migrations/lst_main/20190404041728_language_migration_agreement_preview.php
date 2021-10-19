<?php


use Phinx\Migration\AbstractMigration;

class LanguageMigrationAgreementPreview extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "agreements", "contracts.html", "Preview", "Preview");
        $this->langVarList[] = new LangVar("de", "template", "agreements", "contracts.html", "Preview", "Vorschau");
        $this->langVarList[] = new LangVar("tr", "template", "agreements", "contracts.html", "Preview", "Vorschau");
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
