<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Util\Literal;

class CheckOcrLaterDate extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "receipt", "common", "ocr-error-proceed-check-later", "Due to technical issues we couldn't check if image you uploaded is a receipt. It will be saved and checked later.");
        $this->langVarList[] = new LangVar("de", "php", "receipt", "common", "ocr-error-proceed-check-later", "Aus technischen Gründen konnten wir nicht überprüfen, ob das von Ihnen hochgeladene Bild eine Quittung ist. Es wird gespeichert und später überprüft.");
        $this->langVarList[] = new LangVar("tr", "php", "receipt", "common", "ocr-error-proceed-check-later", "Aus technischen Gründen konnten wir nicht überprüfen, ob das von Ihnen hochgeladene Bild eine Quittung ist. Es wird gespeichert und später überprüft.");
    }

    public function up()
    {
        $this->table("receipt_file")
            ->addColumn("needs_check", Literal::from("flag"), ["null" => false, "default" => "N"])
            ->save();

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }
    }

    public function down()
    {
        $this->table("receipt_file")
            ->removeColumn("needs_check")
            ->save();

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
    }
}