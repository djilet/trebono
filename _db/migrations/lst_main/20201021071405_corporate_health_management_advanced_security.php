<?php

use Phinx\Migration\AbstractMigration;

class CorporateHealthManagementAdvancedSecurity extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("de", "template", "company", "company_unit_edit.html", "ContractListAccessNote", "Speichern Sie das Unternehmen / die Abteilung, um auf Vertragsdokumente zuzugreifen");
        $this->langVarList[] = new LangVar("en", "template", "company", "company_unit_edit.html", "ContractListAccessNote", "Save company/department to access contract documents");
        $this->langVarList[] = new LangVar("tr", "template", "company", "company_unit_edit.html", "ContractListAccessNote", "Speichern Sie das Unternehmen / die Abteilung, um auf Vertragsdokumente zuzugreifen");

        $this->langVarList[] = new LangVar("en", "php", "product", "common", "product-".PRODUCT__CORPORATE_HEALTH_MANAGEMENT__ADVANCED_SECURITY, "Corporate Health Management Advanced Security");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "product-".PRODUCT__CORPORATE_HEALTH_MANAGEMENT__ADVANCED_SECURITY, "Betriebliches Gesundheitsmanagement Erweiterte Sicherheit");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "product-".PRODUCT__CORPORATE_HEALTH_MANAGEMENT__ADVANCED_SECURITY, "Betriebliches Gesundheitsmanagement Erweiterte Sicherheit");

        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-".OPTION__CORPORATE_HEALTH_MANAGEMENT__ADVANCED_SECURITY__MONTHLY_PRICE, "Monthly service price");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-".OPTION__CORPORATE_HEALTH_MANAGEMENT__ADVANCED_SECURITY__MONTHLY_PRICE, "Monatlicher Servicepreis");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-".OPTION__CORPORATE_HEALTH_MANAGEMENT__ADVANCED_SECURITY__MONTHLY_PRICE, "Monatlicher Servicepreis");

        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-".OPTION__CORPORATE_HEALTH_MANAGEMENT__ADVANCED_SECURITY__MONTHLY_DISCOUNT, "Discount for CHM advanced security");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-".OPTION__CORPORATE_HEALTH_MANAGEMENT__ADVANCED_SECURITY__MONTHLY_DISCOUNT, "Rabatt für monatlichen Servicepreis");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-".OPTION__CORPORATE_HEALTH_MANAGEMENT__ADVANCED_SECURITY__MONTHLY_DISCOUNT, "Rabatt für monatlichen Servicepreis");

        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-".OPTION__CORPORATE_HEALTH_MANAGEMENT__ADVANCED_SECURITY__IMPLEMENTATION_PRICE, "Implementation fee");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-".OPTION__CORPORATE_HEALTH_MANAGEMENT__ADVANCED_SECURITY__IMPLEMENTATION_PRICE, "Einrichtungsgebühr");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-".OPTION__CORPORATE_HEALTH_MANAGEMENT__ADVANCED_SECURITY__IMPLEMENTATION_PRICE, "Einrichtungsgebühr");

        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-".OPTION__CORPORATE_HEALTH_MANAGEMENT__ADVANCED_SECURITY__IMPLEMENTATION_DISCOUNT, "Discount for implem. fee");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-".OPTION__CORPORATE_HEALTH_MANAGEMENT__ADVANCED_SECURITY__IMPLEMENTATION_DISCOUNT, "Rabatt für Einrichtungsgebühr");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-".OPTION__CORPORATE_HEALTH_MANAGEMENT__ADVANCED_SECURITY__IMPLEMENTATION_DISCOUNT, "Rabatt für Einrichtungsgebühr");

        $this->langVarList[] = new LangVar("en", "php", "billing", "common", "product-".PRODUCT__CORPORATE_HEALTH_MANAGEMENT__ADVANCED_SECURITY, "Betriebliches Gesundheitsmanagement Erweiterte Sicherheit");
        $this->langVarList[] = new LangVar("de", "php", "billing", "common", "product-".PRODUCT__CORPORATE_HEALTH_MANAGEMENT__ADVANCED_SECURITY, "Betriebliches Gesundheitsmanagement Erweiterte Sicherheit");
        $this->langVarList[] = new LangVar("tr", "php", "billing", "common", "product-".PRODUCT__CORPORATE_HEALTH_MANAGEMENT__ADVANCED_SECURITY, "Betriebliches Gesundheitsmanagement Erweiterte Sicherheit");
    }

    public function up()
    {
        $group = $this->fetchRow("SELECT group_id FROM product_group WHERE code=".Connection::GetSQLString(PRODUCT_GROUP__CORPORATE_HEALTH_MANAGEMENT));

        //advanced security
        $this->execute("INSERT INTO product (group_id, title, created, code, base_for_api, inheritable)
						VALUES (
                            '".$group["group_id"]."',
							'Food Voucher Advanced Security',
							NOW(),
							".Connection::GetSQLString(PRODUCT__CORPORATE_HEALTH_MANAGEMENT__ADVANCED_SECURITY).",
							'N',							
							'Y'
						)");

        $productAdvancedSecurity = $this->fetchRow("SELECT product_id FROM product WHERE code=".Connection::GetSQLString(PRODUCT__CORPORATE_HEALTH_MANAGEMENT__ADVANCED_SECURITY));

        $this->execute("INSERT INTO option (type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
                            'currency',
                            ".Connection::GetSQLString(OPTION__CORPORATE_HEALTH_MANAGEMENT__ADVANCED_SECURITY__MONTHLY_PRICE).",
                            'Monthly service price',
                            '1',
                            '".$productAdvancedSecurity["product_id"]."',
                            '1',
							'Y','Y','N'
						)");
        $this->execute("INSERT INTO option (type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
                            'currency',
                            ".Connection::GetSQLString(OPTION__CORPORATE_HEALTH_MANAGEMENT__ADVANCED_SECURITY__MONTHLY_DISCOUNT).",
                            'Discount for CHM advanced security service',
                            '1',
                            '".$productAdvancedSecurity["product_id"]."',
                            '1',
							'Y','Y','N'
						)");
        $this->execute("INSERT INTO option (type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
                            'currency',
                            ".Connection::GetSQLString(OPTION__CORPORATE_HEALTH_MANAGEMENT__ADVANCED_SECURITY__IMPLEMENTATION_PRICE).",
                            'Implementation fee',
                            '3',
                            '".$productAdvancedSecurity["product_id"]."',
                            '1',
							'Y','Y','N'
						)");
        $this->execute("INSERT INTO option (type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
                            'currency',
                            ".Connection::GetSQLString(OPTION__CORPORATE_HEALTH_MANAGEMENT__ADVANCED_SECURITY__IMPLEMENTATION_DISCOUNT).",
                            'Discount for implem. fee',
                            '4',
                            '".$productAdvancedSecurity["product_id"]."',
                            '1',
							'Y','Y','N'
						)");

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }
    }

    public function down()
    {
        $productAdvancedSecurity = $this->fetchRow("SELECT product_id FROM product WHERE code=".Connection::GetSQLString(PRODUCT__CORPORATE_HEALTH_MANAGEMENT__ADVANCED_SECURITY));
        $optionList = $this->fetchAll("SELECT option_id FROM option WHERE product_id='".$productAdvancedSecurity["product_id"]."'");

        foreach($optionList as $option)
        {
            $this->execute("DELETE FROM option_value WHERE option_id='".$option["option_id"]."'");
        }

        $this->execute("DELETE FROM option WHERE product_id='".$productAdvancedSecurity["product_id"]."'");
        $this->execute("DELETE FROM product WHERE code=".Connection::GetSQLString(PRODUCT__CORPORATE_HEALTH_MANAGEMENT__ADVANCED_SECURITY));

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
    }
}
