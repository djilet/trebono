<?php


use Phinx\Migration\AbstractMigration;

class LanguageOperationVars extends AbstractMigration
{
    private $langVarList = array();
    
    public function init()
    {
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "operation-invoice_id", "Rechnung anzeigen");
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "operation-invoice_id", "View invoice");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "operation-invoice_id", "Rechnung anzeigen");
        
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "operation-invoice_export", "Rechnungsexport");
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "operation-invoice_export", "Invoice export");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "operation-invoice_export", "Rechnungsexport");
        
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "operation-payroll_id", "Lohnliste anzeigen");
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "operation-payroll_id", "View payroll");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "operation-payroll_id", "Lohnliste anzeigen"); 
        
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "operation-payroll_list", "Lohnliste anzeigen");
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "operation-payroll_list", "View payroll list");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "operation-payroll_list", "Lohnliste anzeigen");  
        
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "operation-agreement_id_view_version", "Vertragsversion anzeigen");
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "operation-agreement_id_view_version", "View agreement version");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "operation-agreement_id_view_version", "Vertragsversion anzeigen");
        
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "operation-agreement_id", "Vereinbarung anzeigen");
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "operation-agreement_id", "View agreement");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "operation-agreement_id", "Vereinbarung anzeigen");
        
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "operation-agreement_id_save", "Vereinbarung speichern");
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "operation-agreement_id_save", "Save agreement");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "operation-agreement_id_save", "Vereinbarung speichern");
        
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "operation-agreement_list", "Vertragsliste anzeigen");
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "operation-agreement_list", "View agreement list");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "operation-agreement_list", "Vertragsliste anzeigen");
        
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "operation-employee_agreement_id", "Mitarbeitervereinbarung anzeigen");
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "operation-employee_agreement_id", "View employee agreement");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "operation-employee_agreement_id", "Mitarbeitervereinbarung anzeigen");
        
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "operation-employee_agreement_list", "Liste der Mitarbeitervereinbarungen anzeigen");
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "operation-employee_agreement_list", "View employee agreement list");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "operation-employee_agreement_list", "Liste der Mitarbeitervereinbarungen anzeigen");
        
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "operation-section-agreements", "Vereinbarungen");
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "operation-section-agreements", "Agreements");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "operation-section-agreements", "Vereinbarungen");
        
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "operation-section-product_group", "Products Settings");
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "operation-section-product_group", "Products Settings");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "operation-section-product_group", "Products Settings");
        
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "operation-product_group_list", "Mobile App-Einstellung");
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "operation-product_group_list", "Mobile Strings");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "operation-product_group_list", "Mobile App-Einstellung");
        
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "operation-price_list", "Preisliste");
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "operation-price_list", "Price list");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "operation-price_list", "Preisliste");
        
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "operation-section-logging_cron", "Hintergrundprozesse");
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "operation-section-logging_cron", "Batch jobs");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "operation-section-logging_cron", "Hintergrundprozesse");
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
