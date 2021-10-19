<?php

use Phinx\Migration\AbstractMigration;

class CreateNewLangVarForReceipt extends AbstractMigration
{
    private $langVarList = [];

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "receipt", "receipt_edit.html", "IsWebUpload", "Is web upload:");
        $this->langVarList[] = new LangVar("de", "template", "receipt", "receipt_edit.html", "IsWebUpload", "Ist Web-Upload:");
        $this->langVarList[] = new LangVar("tr", "template", "receipt", "receipt_edit.html", "IsWebUpload", "Web yüklemesi:");
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
