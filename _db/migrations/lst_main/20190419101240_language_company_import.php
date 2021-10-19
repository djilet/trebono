<?php


use Phinx\Migration\AbstractMigration;

class LanguageCompanyImport extends AbstractMigration
{
    private $langVarList = array();
    
    public function init()
    {        
        $this->langVarList[] = new LangVar("en", "template", "company", "block_import_message.html", "ImportEnd", "Import сompleted");
        $this->langVarList[] = new LangVar("de", "template", "company", "block_import_message.html", "ImportEnd", "Import abgeschlossen");
        $this->langVarList[] = new LangVar("tr", "template", "company", "block_import_message.html", "ImportEnd", "Import abgeschlossen");
        
        $this->langVarList[] = new LangVar("en", "php", "company", "common", "company-unit-not-exist", "Company is not exist yet");
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "company-unit-not-exist", "Firma existiert noch nicht");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "company-unit-not-exist", "Firma existiert noch nicht");
        
        $this->langVarList[] = new LangVar("en", "php", "company", "common", "parent-company-unit-not-exist", "Parent company is not exist yet");
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "parent-company-unit-not-exist", "Muttergesellschaft existiert noch nicht");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "parent-company-unit-not-exist", "Muttergesellschaft existiert noch nicht");
        
        $this->langVarList[] = new LangVar("en", "php", "company", "common", "company-unit-save-errors", "Errors occurred while save company:");
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "company-unit-save-errors", "Fehler beim Speichern der Firma aufgetreten:");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "company-unit-save-errors", "Fehler beim Speichern der Firma aufgetreten:");
        
        $this->langVarList[] = new LangVar("en", "php", "company", "common", "employee-save-errors", "Errors occurred while save employee #%employee_xml_id%:");
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "employee-save-errors", "Beim Speichern des Angestellten #%employee_xml_id% sind Fehler aufgetreten:");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "employee-save-errors", "Beim Speichern des Angestellten #%employee_xml_id% sind Fehler aufgetreten:");
        
        $this->langVarList[] = new LangVar("en", "php", "company", "common", "employee-voucher-save-errors", "Errors occurred while save employee #%employee_xml_id% voucher:");
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "employee-voucher-save-errors", "Fehler beim Speichern des Belegs #%employee_xml_id% des Angestellten");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "employee-voucher-save-errors", "Fehler beim Speichern des Belegs #%employee_xml_id% des Angestellten");
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
