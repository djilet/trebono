<?php


use Phinx\Migration\AbstractMigration;

class LanguageServiceConfirmationMessages extends AbstractMigration
{
    private $langVarList = array();
    
    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "product", "product_group_list.html", "MobileLangStrings", "Mobile strings (from language)");
        $this->langVarList[] = new LangVar("de", "template", "product", "product_group_list.html", "MobileLangStrings", "Mobile Zeichenfolgen (aus der Sprache)");
        $this->langVarList[] = new LangVar("tr", "template", "product", "product_group_list.html", "MobileLangStrings", "Mobile Zeichenfolgen (aus der Sprache)");
        
        $this->langVarList[] = new LangVar("en", "php", "product", "common", "-api-confirmation_description", "Receipt confirmation description");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "-api-confirmation_description", "Text auf Bestätigungsfeld für Beleg");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "-api-confirmation_description", "Text auf Bestätigungsfeld für Beleg");
        
        $this->langVarList[] = new LangVar("en", "php", "product", "common", "-api-receipt_approve_by_employee_success", "Receipt confirmation message");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "-api-receipt_approve_by_employee_success", "Text für Bestätigungsnachricht für Beleg");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "-api-receipt_approve_by_employee_success", "Text für Bestätigungsnachricht für Beleg");
        
        $productGroupList = new ProductGroupList($module);
        $productGroupList->LoadProductGroupListForAdmin();
        foreach($productGroupList->GetItems() as $product)
        {
            $this->langVarList[] = new LangVar("en", "php", "product", "common", $product["code"]."-api-confirmation_description", "The document was recorded according to the organizational instructions. If another amount should be included in the receipt, please do not confirm, but send us a message here in voucher chat.");
            $this->langVarList[] = new LangVar("de", "php", "product", "common", $product["code"]."-api-confirmation_description", "Der Beleg wurde entsprechend der Organisationsanweisung aufgenommen. Sollte ein anderer Betrag im Beleg enthalten sein, bestätigen Sie bitte nicht, sondern schreiben uns hier im Beleg-Chat eine Nachricht.");
            $this->langVarList[] = new LangVar("tr", "php", "product", "common", $product["code"]."-api-confirmation_description", "Der Beleg wurde entsprechend der Organisationsanweisung aufgenommen. Sollte ein anderer Betrag im Beleg enthalten sein, bestätigen Sie bitte nicht, sondern schreiben uns hier im Beleg-Chat eine Nachricht.");
        
            $this->langVarList[] = new LangVar("en", "php", "product", "common", $product["code"]."-api-receipt_approve_by_employee_success", "Please destroy your receipt now");
            $this->langVarList[] = new LangVar("de", "php", "product", "common", $product["code"]."-api-receipt_approve_by_employee_success", "Bitte vernichten Sie den Beleg oder schreiben 'Kopie' darauf! Sie dürfen diesen Beleg nun nicht weiter verwenden (z.B. bei der Steuer einreichen, etc.) Vielen Dank dafür, Ihr trebono Team.");
            $this->langVarList[] = new LangVar("tr", "php", "product", "common", $product["code"]."-api-receipt_approve_by_employee_success", "Bitte vernichten Sie den Beleg oder schreiben 'Kopie' darauf! Sie dürfen diesen Beleg nun nicht weiter verwenden (z.B. bei der Steuer einreichen, etc.) Vielen Dank dafür, Ihr trebono Team.");
        }
        
        $this->delLangVarList[] = new LangVar("en", "php", "receipt", "common", "api-receipt-approve-by-employee-success", "");
        $this->delLangVarList[] = new LangVar("de", "php", "receipt", "common", "api-receipt-approve-by-employee-success", "");
        $this->delLangVarList[] = new LangVar("tr", "php", "receipt", "common", "api-receipt-approve-by-employee-success", "");
    }
    
    public function up()
    {     
        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }
        
        foreach($this->delLangVarList as $langVar)
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
        
        foreach($this->delLangVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }
    }
}
