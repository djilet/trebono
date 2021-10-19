<?php

use Phinx\Migration\AbstractMigration;

class BenefitVoucherApprovedReceiptPayment extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-benefit__main__payment_approved_by_customer", "Approved receipt payment by customer");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-benefit__main__payment_approved_by_customer", "Genehmigte Quittungszahlung durch den Kunden");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-benefit__main__payment_approved_by_customer", "Genehmigte Quittungszahlung durch den Kunden");
    }

    public function up()
    {
        $productMain = $this->fetchRow("SELECT product_id FROM product WHERE code=".Connection::GetSQLString(PRODUCT__BENEFIT_VOUCHER__MAIN));

        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
							nextval('\"option_key_KeyID_seq\"'::regclass),
                            'flag',
                            ".Connection::GetSQLString(OPTION__BENEFIT_VOUCHER__MAIN__PAYMENT_APPROVED).",
                            'Payment approved by customer',
                            '4',
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
        $this->execute("DELETE FROM option WHERE code=".Connection::GetSQLString(OPTION__BENEFIT_VOUCHER__MAIN__PAYMENT_APPROVED));

        $stmt = GetStatement(DB_CONTROL);
        $stmt->execute("DELETE FROM option_value_history WHERE option_id=".Option::GetOptionIDByCode(OPTION__BENEFIT_VOUCHER__MAIN__PAYMENT_APPROVED));

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
    }
}
