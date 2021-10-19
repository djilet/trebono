<?php

use Phinx\Migration\AbstractMigration;

class OptionVoucherCategorySwitch extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-".OPTION__BENEFIT_VOUCHER__MAIN__DEFAULT_REASON_ACTIVE, "Individual voucher preference active");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-".OPTION__BENEFIT_VOUCHER__MAIN__DEFAULT_REASON_ACTIVE, "Individuelle Gutscheinpräferenz aktiv");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-".OPTION__BENEFIT_VOUCHER__MAIN__DEFAULT_REASON_ACTIVE, "Individuelle Gutscheinpräferenz aktiv");
    }

    public function up()
    {
        $productMain = $this->fetchRow("SELECT product_id FROM product WHERE code=".Connection::GetSQLString(PRODUCT__BENEFIT_VOUCHER__MAIN));

        $this->execute("UPDATE option SET sort_order = sort_order+1 WHERE sort_order >= 3");
        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
							nextval('\"option_key_KeyID_seq\"'::regclass),
                            'flag',
                            ".Connection::GetSQLString(OPTION__BENEFIT_VOUCHER__MAIN__DEFAULT_REASON_ACTIVE).",
                            'Voucher preference',
                            '3',
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
        $this->execute("DELETE FROM option WHERE code=".Connection::GetSQLString(OPTION__BENEFIT_VOUCHER__MAIN__DEFAULT_REASON));
        $this->execute("UPDATE option SET sort_order = sort_order-1 WHERE sort_order > 3");

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
    }
}
