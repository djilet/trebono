<?php


use Phinx\Migration\AbstractMigration;

class TravelAcc extends AbstractMigration
{
    private $langVarList = array();
    
    public function init()
    {
        $this->langVarList[] = new LangVar("de", "template", "company", "company_unit_edit.html", "AccTravel", "Konto Reisekosten");
        $this->langVarList[] = new LangVar("en", "template", "company", "company_unit_edit.html", "AccTravel", "ACC Travel Costs");
        $this->langVarList[] = new LangVar("tr", "template", "company", "company_unit_edit.html", "AccTravel", "Konto Reisekosten");
        
        $this->langVarList[] = new LangVar("de", "template", "company", "employee_edit.html", "AccTravel", "Konto Reisekosten");
        $this->langVarList[] = new LangVar("en", "template", "company", "employee_edit.html", "AccTravel", "ACC Travel Costs");
        $this->langVarList[] = new LangVar("tr", "template", "company", "employee_edit.html", "AccTravel", "Konto Reisekosten");
        
        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-travel__main__salary_option", "Salary option");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-travel__main__salary_option", "Gehaltsoption");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-travel__main__salary_option", "Gehaltsoption");
        
        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-travel__main__max_monthly", "Max. Monthly Value");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-travel__main__max_monthly", "Max. monatlicher Wert");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-travel__main__max_monthly", "Max. monatlicher Wert");
        
        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-travel__main__max_yearly", "Max. Yearly Value");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-travel__main__max_yearly", "Max. jährlicher Wert");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-travel__main__max_yearly", "Max. jährlicher Wert");
    }
    
    public function up()
    {
        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }
        
        $this->table("company_unit")
        ->addColumn("acc_travel_tax_free", "string", ["length" => 255, "null" => true])
        ->save();
        
        $productMain = $this->fetchRow("SELECT product_id FROM product WHERE code=".Connection::GetSQLString(PRODUCT__TRAVEL__MAIN));
        
        $this->execute("INSERT INTO option (type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
                            'currency',
                            ".Connection::GetSQLString(OPTION__TRAVEL__MAIN__AMOUNT_PER_MONTH).",
                            'Max. Value Per Month',
                            '1',
                            '".$productMain["product_id"]."',
                            '3',
							'Y','Y','Y'
						)");
        
        $this->execute("INSERT INTO option (type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
                            'currency',
                            ".Connection::GetSQLString(OPTION__TRAVEL__MAIN__AMOUNT_PER_YEAR).",
                            'Max. Yearly Value',
                            '2',
                            '".$productMain["product_id"]."',
                            '3',
							'Y','Y','Y'
						)");
        
        $this->execute("INSERT INTO option (type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
                            'string',
                            ".Connection::GetSQLString(OPTION__TRAVEL__MAIN__SALARY_OPTION).",
                            'Salary option',
                            '5',
                            '".$productMain["product_id"]."',
                            '3',
							'N','Y','N'
						)");
    }
    
    public function down()
    {
        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
        
        $this->table("company_unit")
        ->removeColumn("acc_travel_tax_free")
        ->save();
        
        $this->execute("DELETE FROM option WHERE code=".Connection::GetSQLString(OPTION__TRAVEL__MAIN__AMOUNT_PER_MONTH));
        $this->execute("DELETE FROM option WHERE code=".Connection::GetSQLString(OPTION__TRAVEL__MAIN__AMOUNT_PER_YEAR));
        $this->execute("DELETE FROM option WHERE code=".Connection::GetSQLString(OPTION__TRAVEL__MAIN__SALARY_OPTION));
    }
}
