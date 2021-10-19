<?php

use Phinx\Migration\AbstractMigration;

class BenefitVoucherAdvancedSecurity extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        //advanced security
        $this->langVarList[] = new LangVar("en", "php", "product", "common", "product-benefit_voucher__advanced_security", "Benefit Voucher Service Advanced Security");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "product-benefit_voucher__advanced_security", "Sachbezug Gutschein Erweiterte Sicherheit");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "product-benefit_voucher__advanced_security", "Sachbezug Gutschein Erweiterte Sicherheit");

        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-benefit_voucher__advanced_security__monthly_price", "Monthly service price");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-benefit_voucher__advanced_security__monthly_price", "Monatlicher Servicepreis");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-benefit_voucher__advanced_security__monthly_price", "Monatlicher Servicepreis");

        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-benefit_voucher__advanced_security__monthly_discount", "Discount for benefit voucher advanced security");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-benefit_voucher__advanced_security__monthly_discount", "Rabatt für monatlichen Servicepreis");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-benefit_voucher__advanced_security__monthly_discount", "Rabatt für monatlichen Servicepreis");

        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-benefit_voucher__advanced_security__implementation_price", "Implementation fee");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-benefit_voucher__advanced_security__implementation_price", "Einrichtungsgebühr");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-benefit_voucher__advanced_security__implementation_price", "Einrichtungsgebühr");

        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-benefit_voucher__advanced_security__implementation_discount", "Discount for implem. fee");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-benefit_voucher__advanced_security__implementation_discount", "Rabatt für Einrichtungsgebühr");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-benefit_voucher__advanced_security__implementation_discount", "Rabatt für Einrichtungsgebühr");

        //employee changes
        $this->langVarList[] = new LangVar("en", "template", "company", "employee_edit.html", "BankName", "Bank name");
        $this->langVarList[] = new LangVar("de", "template", "company", "employee_edit.html", "BankName", "Bankverbindung");
        $this->langVarList[] = new LangVar("tr", "template", "company", "employee_edit.html", "BankName", "Bankverbindung");

        $this->langVarList[] = new LangVar("en", "template", "company", "employee_edit.html", "IBAN", "IBAN");
        $this->langVarList[] = new LangVar("de", "template", "company", "employee_edit.html", "IBAN", "IBAN");
        $this->langVarList[] = new LangVar("tr", "template", "company", "employee_edit.html", "IBAN", "IBAN");

        $this->langVarList[] = new LangVar("en", "template", "company", "employee_edit.html", "BIC", "BIC");
        $this->langVarList[] = new LangVar("de", "template", "company", "employee_edit.html", "BIC", "BIC");
        $this->langVarList[] = new LangVar("tr", "template", "company", "employee_edit.html", "BIC", "BIC");

        $this->langVarList[] = new LangVar("en", "template", "company", "employee_edit.html", "CreditorNumber", "Creditor number");
        $this->langVarList[] = new LangVar("de", "template", "company", "employee_edit.html", "CreditorNumber", "Gläubigernummer");
        $this->langVarList[] = new LangVar("tr", "template", "company", "employee_edit.html", "CreditorNumber", "Gläubigernummer");
    }

    public function up()
    {
        $group = $this->fetchRow("SELECT group_id FROM product_group WHERE code=".Connection::GetSQLString(PRODUCT_GROUP__BENEFIT_VOUCHER));

        //advanced security
        $this->execute("INSERT INTO product (group_id, title, created, code, base_for_api, inheritable)
						VALUES (
                            '".$group["group_id"]."',
							'Benefit Voucher Advanced Security',
							NOW(),
							".Connection::GetSQLString(PRODUCT__BENEFIT_VOUCHER__ADVANCED_SECURITY).",
							'N',							
							'Y'
						)");

        $productAdvancedSecurity = $this->fetchRow("SELECT product_id FROM product WHERE code=".Connection::GetSQLString(PRODUCT__BENEFIT_VOUCHER__ADVANCED_SECURITY));

        $this->execute("INSERT INTO option (type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
                            'currency',
                            ".Connection::GetSQLString(OPTION__BENEFIT_VOUCHER__ADVANCED_SECURITY__MONTHLY_PRICE).",
                            'Monthly service price',
                            '1',
                            '".$productAdvancedSecurity["product_id"]."',
                            '1',
							'Y','Y','N'
						)");
        $this->execute("INSERT INTO option (type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
                            'currency',
                            ".Connection::GetSQLString(OPTION__BENEFIT_VOUCHER__ADVANCED_SECURITY__MONTHLY_DISCOUNT).",
                            'Discount for benefit voucher advanced security service',
                            '1',
                            '".$productAdvancedSecurity["product_id"]."',
                            '1',
							'Y','Y','N'
						)");
        $this->execute("INSERT INTO option (type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
                            'currency',
                            ".Connection::GetSQLString(OPTION__BENEFIT_VOUCHER__ADVANCED_SECURITY__IMPLEMENTATION_PRICE).",
                            'Implementation fee',
                            '3',
                            '".$productAdvancedSecurity["product_id"]."',
                            '1',
							'Y','Y','N'
						)");
        $this->execute("INSERT INTO option (type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
                            'currency',
                            ".Connection::GetSQLString(OPTION__BENEFIT_VOUCHER__ADVANCED_SECURITY__IMPLEMENTATION_DISCOUNT).",
                            'Discount for implem. fee',
                            '4',
                            '".$productAdvancedSecurity["product_id"]."',
                            '1',
							'Y','Y','N'
						)");

        $bookkeeping = $this->FetchRow("SELECT variable_id FROM language_variable WHERE tag_name='BookkeepingAccountInformation'");
        if (intval($bookkeeping) == 0)
        {
            $this->langVarList[] = new LangVar("de", "template", "company", "employee_edit.html", "BookkeepingAccountInformation", "Buchhaltungskontoinformationen");
            $this->langVarList[] = new LangVar("en", "template", "company", "employee_edit.html", "BookkeepingAccountInformation", "Bookkeeping Account Information");
            $this->langVarList[] = new LangVar("tr", "template", "company", "employee_edit.html", "BookkeepingAccountInformation", "Buchhaltungskontoinformationen");
        }

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }
    }

    public function down()
    {
        $productAdvancedSecurity = $this->fetchRow("SELECT product_id FROM product WHERE code=".Connection::GetSQLString(PRODUCT__BENEFIT_VOUCHER__ADVANCED_SECURITY));
        $optionList = $this->fetchAll("SELECT option_id FROM option WHERE product_id='".$productAdvancedSecurity["product_id"]."'");

        foreach($optionList as $option)
        {
            $this->execute("DELETE FROM option_value WHERE option_id='".$option["option_id"]."'");
        }

        $this->execute("DELETE FROM option WHERE product_id='".$productAdvancedSecurity["product_id"]."'");
        $this->execute("DELETE FROM product WHERE code=".Connection::GetSQLString(PRODUCT__BENEFIT_VOUCHER__ADVANCED_SECURITY));

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
    }
}
