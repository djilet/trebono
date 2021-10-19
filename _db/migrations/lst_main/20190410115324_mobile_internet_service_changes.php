<?php


use Phinx\Migration\AbstractMigration;

class MobileInternetServiceChanges extends AbstractMigration
{   
    private $langVarList = array();
    
    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-internet__main__payment_month_qty", "Number of payment month");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-internet__main__payment_month_qty", "Dauer der Zahlung in Monaten");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-internet__main__payment_month_qty", "Dauer der Zahlung in Monaten");
        
        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-mobile__main__payment_month_qty", "Number of payment month");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-mobile__main__payment_month_qty", "Dauer der Zahlung in Monaten");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-mobile__main__payment_month_qty", "Dauer der Zahlung in Monaten");
        
        $this->delLangVarList[] = new LangVar("en", "php", "product", "common", "option-mobile__main__age_deduction", "");
        $this->delLangVarList[] = new LangVar("de", "php", "product", "common", "option-mobile__main__age_deduction", "");
        $this->delLangVarList[] = new LangVar("tr", "php", "product", "common", "option-mobile__main__age_deduction", "");
    }
    
    public function up()
    {
        $specificProductGroup = SpecificProductGroupFactory::CreateByCode(PRODUCT_GROUP__MOBILE);
        $productID = Product::GetProductIDByCode($specificProductGroup->GetMainProductCode());
        
        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
							nextval('\"option_key_KeyID_seq\"'::regclass),
                            'int',
                            'mobile__main__payment_month_qty',
                            'Number of payment month',
                            '4',
                            '".$productID."',
                            '3',
							'Y','Y','Y'
						)");
        
        $specificProductGroup = SpecificProductGroupFactory::CreateByCode(PRODUCT_GROUP__INTERNET);
        $productID = Product::GetProductIDByCode($specificProductGroup->GetMainProductCode());
        
        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
							nextval('\"option_key_KeyID_seq\"'::regclass),
                            'int',
                            'internet__main__payment_month_qty',
                            'Number of payment month',
                            '4',
                            '".$productID."',
                            '3',
							'Y','Y','Y'
						)");
        
        $optionID = $this->fetchRow("SELECT * FROM option WHERE code='mobile__main__age_deduction'")["option_id"];
        
        $this->execute("DELETE FROM option_value WHERE option_id=".$optionID);
        $this->execute("DELETE FROM option WHERE code='mobile__main__age_deduction'");
        
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
        $this->execute("DELETE FROM option WHERE code='mobile__main__payment_month_qty'");
        $this->execute("DELETE FROM option WHERE code='internet__main__payment_month_qty'");
        
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
