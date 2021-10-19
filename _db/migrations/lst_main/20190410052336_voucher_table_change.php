<?php


use Phinx\Migration\AbstractMigration;

class VoucherTableChange extends AbstractMigration
{
    
    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "company", "common", "voucher-list-title", "Employee %product_group% voucher list");
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "voucher-list-title", "Ausgestellte Gutscheine für %product_group%");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "voucher-list-title", "Ausgestellte Gutscheine für %product_group%");
        
        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-gift__main__amount_per_voucher", "Max amount of voucher");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-gift__main__amount_per_voucher", "Max. Gutschein");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-gift__main__amount_per_voucher", "Max. Gutschein");
        
        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-gift__main__qty_per_year", "Max number of vouchers per year");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-gift__main__qty_per_year", "Max. Anzahl an Gutscheinen pro Jahr");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-gift__main__qty_per_year", "Max. Anzahl an Gutscheinen pro Jahr");
        
        $this->langVarList[] = new LangVar("en", "php", "company", "common", "voucher-yearly-qty-limit-exceeded", "'Gift service' yearly number limit exceeded");
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "voucher-yearly-qty-limit-exceeded", "Die jährliche Anzahl der Geschenke wurde überschritten");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "voucher-yearly-qty-limit-exceeded", "Die jährliche Anzahl der Geschenke wurde überschritten");
        
        $this->langVarList[] = new LangVar("en", "php", "company", "common", "voucher-amount-limit-exceeded", "Voucher amount greater than max amount for Gift service");
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "voucher-amount-limit-exceeded", "Gutscheinbetrag größer als der maximale Betrag für den Geschenke");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "voucher-amount-limit-exceeded", "Gutscheinbetrag größer als der maximale Betrag für den Geschenke");
        
        $this->langVarList[] = new LangVar("en", "php", "company", "common", "voucher-add", "Add %product_group% voucher");
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "voucher-add", "Gutschein hinzufügen für %product_group%");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "voucher-add", "Gutschein hinzufügen für %product_group%");
        
        $this->langVarList[] = new LangVar("en", "php", "company", "common", "voucher-edit", "Edit %product_group% voucher");
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "voucher-edit", "Gutschein bearbeite für %product_group%");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "voucher-edit", "Gutschein bearbeite für %product_group%");
        
        
        $this->delLangVarList[] = new LangVar("en", "template", "company", "employee_edit.html", "VoucherSection", "");
        $this->delLangVarList[] = new LangVar("de", "template", "company", "employee_edit.html", "VoucherSection", "");
        $this->delLangVarList[] = new LangVar("tr", "template", "company", "employee_edit.html", "VoucherSection", "");
        
        $this->delLangVarList[] = new LangVar("en", "template", "company", "voucher_edit.html", "AddVoucher", "");
        $this->delLangVarList[] = new LangVar("de", "template", "company", "voucher_edit.html", "AddVoucher", "");
        $this->delLangVarList[] = new LangVar("tr", "template", "company", "voucher_edit.html", "AddVoucher", "");
        
        $this->delLangVarList[] = new LangVar("en", "template", "company", "voucher_edit.html", "EditVoucher", "");
        $this->delLangVarList[] = new LangVar("de", "template", "company", "voucher_edit.html", "EditVoucher", "");
        $this->delLangVarList[] = new LangVar("tr", "template", "company", "voucher_edit.html", "EditVoucher", "");
        
        $this->delLangVarList[] = new LangVar("en", "php", "product", "common", "option-gift__main__units_per_year", "");
        $this->delLangVarList[] = new LangVar("de", "php", "product", "common", "option-gift__main__units_per_year", "");
        $this->delLangVarList[] = new LangVar("tr", "php", "product", "common", "option-gift__main__units_per_year", "");
        
        $this->delLangVarList[] = new LangVar("en", "php", "product", "common", "option-gift__main__employer_grant", "");
        $this->delLangVarList[] = new LangVar("de", "php", "product", "common", "option-gift__main__employer_grant", "");
        $this->delLangVarList[] = new LangVar("tr", "php", "product", "common", "option-gift__main__employer_grant", "");
    }
    
    public function up()
    {
        $this->table("voucher")
        ->addColumn("group_id", "integer", ["null" => true])
        ->save();
        
        $bonusGroupID = ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__BONUS);
        
        $this->execute("UPDATE voucher SET group_id=".$bonusGroupID); 
        
        $this->table("voucher")
        ->changeColumn("group_id", "integer", ["null" => true])
        ->save();
        
        $specificProductGroup = SpecificProductGroupFactory::CreateByCode(PRODUCT_GROUP__GIFT);
        $productID = Product::GetProductIDByCode($specificProductGroup->GetMainProductCode());
        
        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
							nextval('\"option_key_KeyID_seq\"'::regclass),
                            'int',
                            'gift__main__amount_per_voucher',
                            'Max amount per voucher',
                            '1',
                            '".$productID."',
                            '3',
							'Y','Y','N'
						)");
        
        
        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
							nextval('\"option_key_KeyID_seq\"'::regclass),
                            'int',
                            'gift__main__qty_per_year',
                            'Max qty of vouchers per year',
                            '2',
                            '".$productID."',
                            '3',
							'Y','Y','N'
						)");
        
        $optionID = $this->fetchRow("SELECT * FROM option WHERE code='gift__main__employer_grant'")["option_id"];
        
        $this->execute("DELETE FROM option_value WHERE option_id=".$optionID);
        $this->execute("DELETE FROM option WHERE code='gift__main__employer_grant'");
        
        
        $optionID = $this->fetchRow("SELECT * FROM option WHERE code='gift__main__units_per_year'")["option_id"];
        
        $this->execute("DELETE FROM option_value WHERE option_id=".$optionID);
        $this->execute("DELETE FROM option WHERE code='gift__main__units_per_year'");
        
        
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
        $this->table("voucher")
        ->removeColumn("group_id")
        ->save();
        
        $specificProductGroup = SpecificProductGroupFactory::CreateByCode(PRODUCT_GROUP__GIFT);
        $productID = Product::GetProductIDByCode($specificProductGroup->GetMainProductCode());
        
        $optionID = $this->fetchRow("SELECT * FROM option WHERE code='gift__main__amount_per_voucher'")["option_id"];
        
        $this->execute("DELETE FROM option_value WHERE option_id=".$optionID);
        $this->execute("DELETE FROM option WHERE code='gift__main__amount_per_voucher'");
        
        
        $optionID = $this->fetchRow("SELECT * FROM option WHERE code='gift__main__qty_per_year'")["option_id"];
        
        $this->execute("DELETE FROM option_value WHERE option_id=".$optionID);
        $this->execute("DELETE FROM option WHERE code='gift__main__qty_per_year'");
        
        
        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
							nextval('\"option_key_KeyID_seq\"'::regclass),
                            'int',
                            'gift__main__employer_grant',
                            'Max. Value (Employer Grant) per case',
                            '1',
                            '".$productID."',
                            '3',
							'Y','Y','Y'
						)");
        
        
        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
							nextval('\"option_key_KeyID_seq\"'::regclass),
                            'int',
                            'gift__main__units_per_year',
                            'Max. number of Gifts per Year',
                            '2',
                            '".$productID."',
                            '3',
							'Y','Y','Y'
						)");
        
        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
        
        foreach($this->delLangVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            //$this->execute($query);
        }
    }
}
