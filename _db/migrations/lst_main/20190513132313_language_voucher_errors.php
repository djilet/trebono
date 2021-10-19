<?php


use Phinx\Migration\AbstractMigration;

class LanguageVoucherErrors extends AbstractMigration
{
    private $langVarList = array();
    
    public function init()
    {
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "voucher-amount-empty", "Geben Sie den Gutschein-Wert");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "voucher-amount-empty", "Geben Sie den Gutschein-Wert");
        
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "voucher-amount-incorrect", "Gutschein Wert falsch");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "voucher-amount-incorrect", "Gutschein Wert falsch");
        
        $this->delLangVarList[] = new LangVar("de", "php", "company", "common", "voucher-value-empty", "Gutscheinwert eingeben");
        $this->delLangVarList[] = new LangVar("tr", "php", "company", "common", "voucher-value-empty", "Gutscheinwert eingeben");
        $this->delLangVarList[] = new LangVar("de", "php", "company", "common", "voucher-value-incorrect", "Der Gutscheinwert ist falsch");
        $this->delLangVarList[] = new LangVar("tr", "php", "company", "common", "voucher-value-incorrect", "Der Gutscheinwert ist falsch");
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
