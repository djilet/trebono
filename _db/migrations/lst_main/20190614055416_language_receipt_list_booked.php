<?php


use Phinx\Migration\AbstractMigration;

class LanguageReceiptListBooked extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "receipt", "block_receipt_list.html", "Booked", "Booked");
        $this->langVarList[] = new LangVar("de", "template", "receipt", "block_receipt_list.html", "Booked", "Gebucht");
        $this->langVarList[] = new LangVar("tr", "template", "receipt", "block_receipt_list.html", "Booked", "Gebucht");
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
