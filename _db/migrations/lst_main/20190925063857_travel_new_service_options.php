<?php

use Phinx\Migration\AbstractMigration;

class TravelNewServiceOptions extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-travel__main__creditor_booking", "Creditor Booking");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-travel__main__creditor_booking", "Gläubiger Buchung");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-travel__main__creditor_booking", "Gläubiger Buchung");

        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-travel__main__fixed_daily_allowance", "Fixed Daily Allowance");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-travel__main__fixed_daily_allowance", "Verpflegungsmehraufwand Pauschal");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-travel__main__fixed_daily_allowance", "Verpflegungsmehraufwand Pauschal");

        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-travel__main__hours_under", "> 8 < 16 Hours");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-travel__main__hours_under", "> 8 < 16 Stunden");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-travel__main__hours_under", "> 8 < 16 Stunden");

        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-travel__main__hours_over", ">= 16 Hours");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-travel__main__hours_over", ">= 16 Stunden");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-travel__main__hours_over", ">= 16 Stunden");
    }

    public function up()
    {
        $specificProductGroup = SpecificProductGroupFactory::CreateByCode(PRODUCT_GROUP__TRAVEL);
        $productID = Product::GetProductIDByCode($specificProductGroup->GetMainProductCode());

        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
							nextval('\"option_key_KeyID_seq\"'::regclass),
                            'flag',
                            'travel__main__creditor_booking',
                            'Creditor Booking',
                            '1',
                            '".$productID."',
                            '3',
							'N','Y','N'
						)");

        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
							nextval('\"option_key_KeyID_seq\"'::regclass),
                            'flag',
                            'travel__main__fixed_daily_allowance',
                            'Fixed Daily allowance',
                            '2',
                            '".$productID."',
                            '3',
							'Y','Y','Y'
						)");

        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
							nextval('\"option_key_KeyID_seq\"'::regclass),
                            'currency',
                            'travel__main__hours_under',
                            '> 8 < 16 Hours',
                            '3',
                            '".$productID."',
                            '3',
							'Y','Y','Y'
						)");

        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
							nextval('\"option_key_KeyID_seq\"'::regclass),
                            'currency',
                            'travel__main__hours_over',
                            '>= 16 Hours',
                            '4',
                            '".$productID."',
                            '3',
							'Y','Y','Y'
						)");

        $this->execute("UPDATE option SET sort_order='5' WHERE code = ".Connection::GetSQLString(OPTION__TRAVEL__MAIN__AMOUNT_PER_MONTH));
        $this->execute("UPDATE option SET sort_order='6' WHERE code = ".Connection::GetSQLString(OPTION__TRAVEL__MAIN__AMOUNT_PER_YEAR));
        $this->execute("UPDATE option SET sort_order='7' WHERE code = ".Connection::GetSQLString(OPTION__TRAVEL__MAIN__SALARY_OPTION));

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }
    }

    public function down()
    {
        $this->execute("DELETE FROM option WHERE code='travel__main__creditor_booking'");
        $this->execute("DELETE FROM option WHERE code='travel__main__fixed_daily_allowance'");
        $this->execute("DELETE FROM option WHERE code='travel__main__hours_under'");
        $this->execute("DELETE FROM option WHERE code='travel__main__hours_over'");

        $this->execute("UPDATE option SET sort_order='1' WHERE code = ".Connection::GetSQLString(OPTION__TRAVEL__MAIN__AMOUNT_PER_MONTH));
        $this->execute("UPDATE option SET sort_order='2' WHERE code = ".Connection::GetSQLString(OPTION__TRAVEL__MAIN__AMOUNT_PER_YEAR));
        $this->execute("UPDATE option SET sort_order='5' WHERE code = ".Connection::GetSQLString(OPTION__TRAVEL__MAIN__SALARY_OPTION));

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
    }
}
