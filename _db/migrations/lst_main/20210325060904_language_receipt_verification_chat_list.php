<?php

use Phinx\Migration\AbstractMigration;

class LanguageReceiptVerificationChatList extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "receipt", "receipt_edit.html", "CommentListAllChats", "See all chats");
        $this->langVarList[] = new LangVar("de", "template", "receipt", "receipt_edit.html", "CommentListAllChats", "Alle Chats anzeigen");
        $this->langVarList[] = new LangVar("tr", "template", "receipt", "receipt_edit.html", "CommentListAllChats", "Alle Chats anzeigen");

        $this->langVarList[] = new LangVar("en", "template", "receipt", "block_employee_chat_list.html", "ChatList", "Chat List of");
        $this->langVarList[] = new LangVar("de", "template", "receipt", "block_employee_chat_list.html", "ChatList", "Chat Liste von");
        $this->langVarList[] = new LangVar("tr", "template", "receipt", "block_employee_chat_list.html", "ChatList", "Chat Liste von");

        $this->langVarList[] = new LangVar("en", "template", "receipt", "block_employee_chat_list.html", "Service", "Service");
        $this->langVarList[] = new LangVar("de", "template", "receipt", "block_employee_chat_list.html", "Service", "Service");
        $this->langVarList[] = new LangVar("tr", "template", "receipt", "block_employee_chat_list.html", "Service", "Service");

        $this->langVarList[] = new LangVar("en", "template", "receipt", "block_employee_chat_list.html", "FilterCreatedRangeChat", "Filter by message date");
        $this->langVarList[] = new LangVar("de", "template", "receipt", "block_employee_chat_list.html", "FilterCreatedRangeChat", "Nach Nachrichtendatum filtern");
        $this->langVarList[] = new LangVar("tr", "template", "receipt", "block_employee_chat_list.html", "FilterCreatedRangeChat", "Nach Nachrichtendatum filtern");

        $this->langVarList[] = new LangVar("en", "template", "receipt", "block_employee_chat_list_table.html", "ChatsOnPage", "Chats on page");
        $this->langVarList[] = new LangVar("de", "template", "receipt", "block_employee_chat_list_table.html", "ChatsOnPage", "Chats auf Seite");
        $this->langVarList[] = new LangVar("tr", "template", "receipt", "block_employee_chat_list_table.html", "ChatsOnPage", "Chats auf Seite");

        $this->langVarList[] = new LangVar("en", "template", "receipt", "block_employee_chat_list_table.html", "ReceiptID", "Receipt ID");
        $this->langVarList[] = new LangVar("de", "template", "receipt", "block_employee_chat_list_table.html", "ReceiptID", "Beleg ID");
        $this->langVarList[] = new LangVar("tr", "template", "receipt", "block_employee_chat_list_table.html", "ReceiptID", "Beleg ID");

        $this->langVarList[] = new LangVar("en", "template", "receipt", "block_employee_chat_list_table.html", "LastCommentCreated", "Last message date");
        $this->langVarList[] = new LangVar("de", "template", "receipt", "block_employee_chat_list_table.html", "LastCommentCreated", "Datum der letzten Nachricht");
        $this->langVarList[] = new LangVar("tr", "template", "receipt", "block_employee_chat_list_table.html", "LastCommentCreated", "Datum der letzten Nachricht");

        $this->langVarList[] = new LangVar("en", "template", "receipt", "block_employee_chat_list_table.html", "ReceiptDate", "Receipt Date");
        $this->langVarList[] = new LangVar("de", "template", "receipt", "block_employee_chat_list_table.html", "ReceiptDate", "Datum des Beleges");
        $this->langVarList[] = new LangVar("tr", "template", "receipt", "block_employee_chat_list_table.html", "ReceiptDate", "Datum des Beleges");

        $this->langVarList[] = new LangVar("en", "template", "receipt", "block_employee_chat_list_table.html", "Service", "Service");
        $this->langVarList[] = new LangVar("de", "template", "receipt", "block_employee_chat_list_table.html", "Service", "Service");
        $this->langVarList[] = new LangVar("tr", "template", "receipt", "block_employee_chat_list_table.html", "Service", "Service");

        $this->langVarList[] = new LangVar("en", "template", "receipt", "block_employee_chat_list_table.html", "LastCommentContent", "Last message");
        $this->langVarList[] = new LangVar("de", "template", "receipt", "block_employee_chat_list_table.html", "LastCommentContent", "Letzte Nachricht");
        $this->langVarList[] = new LangVar("tr", "template", "receipt", "block_employee_chat_list_table.html", "LastCommentContent", "Letzte Nachricht");
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
