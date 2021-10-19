<?php


use Phinx\Migration\AbstractMigration;

class AdServiceOptionChanges extends AbstractMigration
{
    
    private $langVarList = array();
    
    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-ad__main__payment_month_qty", "Number of payment month");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-ad__main__payment_month_qty", "Dauer der Zahlung in Monaten");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-ad__main__payment_month_qty", "Dauer der Zahlung in Monaten");
        
        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-incorrect-value-greater-global", "'%option%' option value can't be geater than global value %global_value%");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-incorrect-value-greater-global", "'%option%' option value kann nicht als globaler Wert angegeben werden %global_value%");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-incorrect-value-greater-global", "'%option%' option value kann nicht als globaler Wert angegeben werden %global_value%");
    }
    
    public function up()
    {
        $specificProductGroup = SpecificProductGroupFactory::CreateByCode(PRODUCT_GROUP__AD);
        $productID = Product::GetProductIDByCode($specificProductGroup->GetMainProductCode());
        
        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
							nextval('\"option_key_KeyID_seq\"'::regclass),
                            'int',
                            'ad__main__payment_month_qty',
                            'Number of payment month',
                            '4',
                            '".$productID."',
                            '3',
							'Y','Y','Y'
						)");
        
        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }
    }
    
    public function down()
    {
        $this->execute("DELETE FROM option WHERE code='ad__main__payment_month_qty'");
        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
    }
}
