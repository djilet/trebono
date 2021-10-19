<?php

use Phinx\Migration\AbstractMigration;

class BenefitVoucherNewTextFields extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-benefit_voucher__main__short_text_for_PDF", "Text field for PDF (short)");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-benefit_voucher__main__short_text_for_PDF", "Textfeld für PDF (kurz)");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-benefit_voucher__main__short_text_for_PDF", "Textfeld für PDF (kurz)");

        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-benefit_voucher__main__long_text_for_PDF", "Text field for PDF (long)");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-benefit_voucher__main__long_text_for_PDF", "Textfeld für PDF (lang)");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-benefit_voucher__main__long_text_for_PDF", "Textfeld für PDF (lang)");
    }

    public function up()
    {
        $productMain = $this->fetchRow("SELECT product_id FROM product WHERE code=".Connection::GetSQLString(PRODUCT__BENEFIT_VOUCHER__MAIN));

        $this->execute("INSERT INTO option (type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
                        VALUES (
                            'string',
                            ".Connection::GetSQLString(OPTION__BENEFIT_VOUCHER__MAIN__SHORT_TEXT_FOR_PDF).",
                            'Short text for PDF',
                            '5',
                            '".$productMain["product_id"]."',
                            '3',
                            'Y','Y','Y'
                        )");

        $this->execute("INSERT INTO option (type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
                VALUES (
                    'string',
                    ".Connection::GetSQLString(OPTION__BENEFIT_VOUCHER__MAIN__LONG_TEXT_FOR_PDF).",
                    'Long text for PDF',
                    '6',
                    '".$productMain["product_id"]."',
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
        $this->execute("DELETE FROM option WHERE code=".Connection::GetSQLString(OPTION__BENEFIT_VOUCHER__MAIN__SHORT_TEXT_FOR_PDF));
        $this->execute("DELETE FROM option WHERE code=".Connection::GetSQLString(OPTION__BENEFIT_VOUCHER__MAIN__LONG_TEXT_FOR_PDF));

        $stmt = GetStatement(DB_CONTROL);
        $stmt->execute("DELETE FROM option_value_history WHERE option_id=".Option::GetOptionIDByCode(OPTION__BENEFIT_VOUCHER__MAIN__SHORT_TEXT_FOR_PDF));
        $stmt->execute("DELETE FROM option_value_history WHERE option_id=".Option::GetOptionIDByCode(OPTION__BENEFIT_VOUCHER__MAIN__LONG_TEXT_FOR_PDF));

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
    }
}
