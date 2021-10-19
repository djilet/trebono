<?php

use Phinx\Migration\AbstractMigration;

class LanguageReceiptsOnPopup extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "receipt", "block_employee_receipt_list_table.html", "ReceiptsOnPage", "Receipts on page");
        $this->langVarList[] = new LangVar("de", "template", "receipt", "block_employee_receipt_list_table.html", "ReceiptsOnPage", "Anzeige Belege pro Seite");
        $this->langVarList[] = new LangVar("tr", "template", "receipt", "block_employee_receipt_list_table.html", "ReceiptsOnPage", "Anzahl Belege pro Seite");
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
