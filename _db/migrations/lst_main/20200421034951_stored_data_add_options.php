<?php

use Phinx\Migration\AbstractMigration;

class StoredDataAddOptions extends AbstractMigration
{
    private $langVarList = array();
    private $delLangVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-".OPTION__STORED_DATA__MAIN__QUARTERLY_PRICE, "Quarterly service price");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-".OPTION__STORED_DATA__MAIN__QUARTERLY_PRICE, "Vierteljährlich Servicepreis");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-".OPTION__STORED_DATA__MAIN__QUARTERLY_PRICE, "Vierteljährlich Servicepreis");

        $this->langVarList[] = new LangVar("en", "php", "billing", "common", "product-".PRODUCT__STORED_DATA__MAIN, "Stored Data");
        $this->langVarList[] = new LangVar("de", "php", "billing", "common", "product-".PRODUCT__STORED_DATA__MAIN, "Gespeicherte Daten");
        $this->langVarList[] = new LangVar("tr", "php", "billing", "common", "product-".PRODUCT__STORED_DATA__MAIN, "Gespeicherte Daten");
    }

    public function up()
    {
        //insert new option
        $product = $this->fetchRow("SELECT product_id FROM product WHERE code=".Connection::GetSQLString(PRODUCT__STORED_DATA__MAIN));
        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
                            VALUES (
                                nextval('\"option_key_KeyID_seq\"'::regclass),
                                'currency',
                                ".Connection::GetSQLString(OPTION__STORED_DATA__MAIN__QUARTERLY_PRICE).",
                                'Quarterly service price',
                                '2',
                                '".$product["product_id"]."',
                                '1',
                                'Y','Y','N'
                            )");

        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
                            VALUES (
                                nextval('\"option_key_KeyID_seq\"'::regclass),
                                'currency',
                                ".Connection::GetSQLString(OPTION__STORED_DATA__MAIN__YEARLY_PRICE).",
                                'Yearly service price',
                                '3',
                                '".$product["product_id"]."',
                                '1',
                                'Y','Y','N'
                            )");

        $this->execute("UPDATE option SET sort_order='4' WHERE code=".Connection::GetSQLString(OPTION__STORED_DATA__MAIN__IMPLEMENTATION_PRICE));
        $this->execute("UPDATE option SET sort_order='5' WHERE code=".Connection::GetSQLString(OPTION__STORED_DATA__MAIN__MONTHLY_DISCOUNT));
        $this->execute("UPDATE option SET sort_order='6' WHERE code=".Connection::GetSQLString(OPTION__STORED_DATA__MAIN__IMPLEMENTATION_DISCOUNT));

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }
    }

    public function down()
    {
        $options = $this->fetchRow("SELECT option_id FROM option 
                                        WHERE code IN(
                                        ".Connection::GetSQLString(OPTION__STORED_DATA__MAIN__QUARTERLY_PRICE).",
                                        ".Connection::GetSQLString(OPTION__STORED_DATA__MAIN__YEARLY_PRICE).")");
        $stmt = GetStatement(DB_CONTROL);
        $query = "DELETE FROM option_value_history WHERE option_id IN (".implode(",", array_column($options, "option_id")).")";
        $stmt->Execute($query);

        $this->execute("DELETE FROM option WHERE code IN(
                                        ".Connection::GetSQLString(OPTION__STORED_DATA__MAIN__QUARTERLY_PRICE).",
                                        ".Connection::GetSQLString(OPTION__STORED_DATA__MAIN__YEARLY_PRICE).")");

        $this->execute("UPDATE option SET sort_order='3' WHERE code=".Connection::GetSQLString(OPTION__STORED_DATA__MAIN__IMPLEMENTATION_PRICE));
        $this->execute("UPDATE option SET sort_order='2' WHERE code=".Connection::GetSQLString(OPTION__STORED_DATA__MAIN__MONTHLY_DISCOUNT));
        $this->execute("UPDATE option SET sort_order='4' WHERE code=".Connection::GetSQLString(OPTION__STORED_DATA__MAIN__IMPLEMENTATION_DISCOUNT));

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
    }
}
