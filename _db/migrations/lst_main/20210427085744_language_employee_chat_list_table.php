<?php

use Phinx\Migration\AbstractMigration;

class LanguageEmployeeChatListTable extends AbstractMigration
{
    private $langVarList = array();
    private $delangVarList = array();

    public function init()
    {
        $this->delangVarList[] = new LangVar("de", "template", "receipt", "block_employee_chat_list_table.html", "LastCommentContent", "Letzte Nachricht");
        $this->delangVarList[] = new LangVar("en", "template", "receipt", "block_employee_chat_list_table.html", "LastCommentContent", "Last message");
        $this->delangVarList[] = new LangVar("tr", "template", "receipt", "block_employee_chat_list_table.html", "LastCommentContent", "Letzte Nachricht");

        $this->langVarList[] = new LangVar("de", "template", "receipt", "block_employee_chat_list_table.html", "Messages", "Mitteilungen");
        $this->langVarList[] = new LangVar("en", "template", "receipt", "block_employee_chat_list_table.html", "Messages", "Messages");
        $this->langVarList[] = new LangVar("tr", "template", "receipt", "block_employee_chat_list_table.html", "Messages", "Mitteilungen");
    }

    public function up()
    {
        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }
        foreach($this->delangVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
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
        foreach($this->delangVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }
    }
}
