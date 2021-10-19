<?php

use Phinx\Migration\AbstractMigration;

class OptionVoucherCategoryBovs extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-".OPTION__BONUS_VOUCHER__MAIN__DEFAULT_REASON, "Voucher preference");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-".OPTION__BONUS_VOUCHER__MAIN__DEFAULT_REASON, "Gutscheinpräferenz");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-".OPTION__BONUS_VOUCHER__MAIN__DEFAULT_REASON, "Gutscheinpräferenz");
    }

    public function up()
    {
        $productMain = $this->fetchRow("SELECT product_id FROM product WHERE code=".Connection::GetSQLString(PRODUCT__BONUS_VOUCHER__MAIN));

        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
							nextval('\"option_key_KeyID_seq\"'::regclass),
                            'int',
                            ".Connection::GetSQLString(OPTION__BONUS_VOUCHER__MAIN__DEFAULT_REASON).",
                            'Voucher preference',
                            '2',
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
        $this->execute("DELETE FROM option WHERE code=".Connection::GetSQLString(OPTION__BONUS_VOUCHER__MAIN__DEFAULT_REASON));

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
    }
}
