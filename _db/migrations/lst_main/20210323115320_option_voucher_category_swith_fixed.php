<?php

use Phinx\Migration\AbstractMigration;

class OptionVoucherCategorySwithFixed extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-".OPTION__BENEFIT_VOUCHER__MAIN__DEFAULT_REASON_FIXED, "Fixed voucher category");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-".OPTION__BENEFIT_VOUCHER__MAIN__DEFAULT_REASON_FIXED, "Gutscheinkategorie behoben");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-".OPTION__BENEFIT_VOUCHER__MAIN__DEFAULT_REASON_FIXED, "Gutscheinkategorie behoben");

        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-group-voucher_category", "Voucher Category");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-group-voucher_category", "Gutscheinkategorie");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-group-voucher_category", "Gutscheinkategorie");
    }

    public function up()
    {
        $productMain = $this->fetchRow("SELECT product_id FROM product WHERE code=".Connection::GetSQLString(PRODUCT__BENEFIT_VOUCHER__MAIN));

        $this->execute("INSERT INTO option_group (group_id, code, title, sort_order)
                        VALUES (
                                4,
                                'voucher_category',
                                'Voucher category',
                                4
                        )");

        $this->execute("UPDATE option
                                SET sort_order = 1, group_id = 4
                                    WHERE code = ".Connection::GetSQLString(OPTION__BENEFIT_VOUCHER__MAIN__DEFAULT_REASON_ACTIVE));
        $this->execute("UPDATE option
                                SET sort_order = 2, group_id = 4
                                    WHERE code = ".Connection::GetSQLString(OPTION__BENEFIT_VOUCHER__MAIN__DEFAULT_REASON));

        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
							nextval('\"option_key_KeyID_seq\"'::regclass),
                            'flag',
                            ".Connection::GetSQLString(OPTION__BENEFIT_VOUCHER__MAIN__DEFAULT_REASON_FIXED).",
                            'Voucher preference fixed',
                            '3',
                            '".$productMain["product_id"]."',
                            '4',
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
        $this->execute("DELETE FROM option WHERE code=".Connection::GetSQLString(OPTION__BENEFIT_VOUCHER__MAIN__DEFAULT_REASON_FIXED));

        $this->execute("DELETE FROM option_group WHERE code='voucher_category'");

        $this->execute("UPDATE option
                                SET sort_order = 3, group_id = 3
                                    WHERE code = ".Connection::GetSQLString(OPTION__BENEFIT_VOUCHER__MAIN__DEFAULT_REASON_ACTIVE));
        $this->execute("UPDATE option
                                SET sort_order = 4, group_id = 3
                                    WHERE code = ".Connection::GetSQLString(OPTION__BENEFIT_VOUCHER__MAIN__DEFAULT_REASON));

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
    }
}
