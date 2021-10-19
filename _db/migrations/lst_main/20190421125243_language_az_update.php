<?php


use Phinx\Migration\AbstractMigration;

class LanguageAzUpdate extends AbstractMigration
{
    private $langVarList = array();
    
    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "company", "common", "contact-save-errors", "Errors occurred while save contact #%contact_xml_id%:");
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "contact-save-errors", "Beim Sichern des Kontakts #% contact_xml_id% sind Fehler aufgetreten:");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "contact-save-errors", "Beim Sichern des Kontakts #% contact_xml_id% sind Fehler aufgetreten:");
    
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "contact-type-empty", "Fügen Sie die Ansprechpartnerrolle hinzu");
        $this->langVarList[] = new LangVar("en", "php", "company", "common", "contact-type-empty", "Add contact person role");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "contact-type-empty", "Fügen Sie die Ansprechpartnerrolle hinzu");
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
