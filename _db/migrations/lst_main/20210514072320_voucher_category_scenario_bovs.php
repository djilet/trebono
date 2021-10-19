<?php

use Phinx\Migration\AbstractMigration;

class VoucherCategoryScenarioBovs extends AbstractMigration
{
    private $langVarList = array();
    private $delLangVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-".OPTION__BONUS_VOUCHER__MAIN__DEFAULT_REASON_SCENARIO, "Individual voucher preference scenario");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-".OPTION__BONUS_VOUCHER__MAIN__DEFAULT_REASON_SCENARIO, "Individuelle Gutscheinpräferenz Szenario");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-".OPTION__BONUS_VOUCHER__MAIN__DEFAULT_REASON_SCENARIO, "Individuelle Gutscheinpräferenz Szenario");

        $this->delLangVarList[] = new LangVar("en", "php", "product", "common", "option-bonus_voucher__main__default_reason_active", "Individual voucher preference active");
        $this->delLangVarList[] = new LangVar("de", "php", "product", "common", "option-bonus_voucher__main__default_reason_active", "Individuelle Gutscheinpräferenz aktiv");
        $this->delLangVarList[] = new LangVar("tr", "php", "product", "common", "option-bonus_voucher__main__default_reason_active", "Individuelle Gutscheinpräferenz aktiv");

        $this->delLangVarList[] = new LangVar("en", "php", "product", "common", "option-bonus_voucher__main__default_reason_fixed", "Fixed voucher category");
        $this->delLangVarList[] = new LangVar("de", "php", "product", "common", "option-bonus_voucher__main__default_reason_fixed", "Gutscheinkategorie behoben");
        $this->delLangVarList[] = new LangVar("tr", "php", "product", "common", "option-bonus_voucher__main__default_reason_fixed", "Gutscheinkategorie behoben");
    }

    public function up()
    {
        $productMain = $this->fetchRow("SELECT product_id FROM product WHERE code=".Connection::GetSQLString(PRODUCT__BONUS_VOUCHER__MAIN));
        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
							nextval('\"option_key_KeyID_seq\"'::regclass),
                            'string',
                            ".Connection::GetSQLString(OPTION__BONUS_VOUCHER__MAIN__DEFAULT_REASON_SCENARIO).",
                            'Voucher preference scenario',
                            '1',
                            '".$productMain["product_id"]."',
                            '4',
							'Y','Y','Y'
						)");

        $this->execute("DELETE FROM option WHERE code='bonus_voucher__main__default_reason_active'");
        $this->execute("DELETE FROM option WHERE code='bonus_voucher__main__default_reason_fixed'");

        foreach($this->delLangVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }
    }

    public function down()
    {
        $this->execute("DELETE FROM option WHERE code=".Connection::GetSQLString(OPTION__BONUS_VOUCHER__MAIN__DEFAULT_REASON_SCENARIO));

        $productMain = $this->fetchRow("SELECT product_id FROM product WHERE code=".Connection::GetSQLString(PRODUCT__BONUS_VOUCHER__MAIN));
        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
							nextval('\"option_key_KeyID_seq\"'::regclass),
                            'flag',
                            'bonus_voucher__main__default_reason_active',
                            'Voucher preference fixed',
                            '1',
                            '".$productMain["product_id"]."',
                            '4',
							'Y','Y','Y'
						)");
        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
							nextval('\"option_key_KeyID_seq\"'::regclass),
                            'flag',
                            'bonus_voucher__main__default_reason_fixed',
                            'Voucher preference fixed',
                            '3',
                            '".$productMain["product_id"]."',
                            '4',
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
            $this->execute($query);
        }
    }
}
