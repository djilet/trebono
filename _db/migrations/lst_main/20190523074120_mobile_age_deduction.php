<?php


use Phinx\Migration\AbstractMigration;

class MobileAgeDeduction extends AbstractMigration
{
    private $langVarList = array();
    
    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-mobile__main__age_deduction", "% age deduction Device financing");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-mobile__main__age_deduction", "%uale Reduktion Handy Finanzierung");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-mobile__main__age_deduction", "%uale Reduktion Handy Finanzierung");
    }
    
    public function up()
    {
        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }
        
        $specificProductGroup = SpecificProductGroupFactory::CreateByCode(PRODUCT_GROUP__MOBILE);
        $productID = Product::GetProductIDByCode($specificProductGroup->GetMainProductCode());
        
        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
							nextval('\"option_key_KeyID_seq\"'::regclass),
                            'int',
                            'mobile__main__age_deduction',
                            '%age deduction Device financing',
                            '2',
                            '".$productID."',
                            '3',
							'Y','Y','Y'
						)");
        
    }
    
    public function down()
    {
        $optionID = $this->fetchRow("SELECT * FROM option WHERE code='mobile__main__age_deduction'")["option_id"];
        
        $this->execute("DELETE FROM option_value WHERE option_id=".$optionID);
        $this->execute("DELETE FROM option WHERE code='mobile__main__age_deduction'");
        
        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
    }
}
