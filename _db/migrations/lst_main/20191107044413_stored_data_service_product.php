<?php

use Phinx\Migration\AbstractMigration;

class StoredDataServiceProduct extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "product", "common", "product-group-stored_data", "Stored Data");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "product-group-stored_data", "Gespeicherte Daten");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "product-group-stored_data", "Gespeicherte Daten");

        $this->langVarList[] = new LangVar("en", "php", "product", "common", "product-stored_data__main", "Stored Data");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "product-stored_data__main", "Gespeicherte Daten");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "product-stored_data__main", "Gespeicherte Daten");

        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-stored_data__main__monthly_price", "Monthly service price");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-stored_data__main__monthly_price", "Monatlicher Servicepreis");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-stored_data__main__monthly_price", "Monatlicher Servicepreis");

        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-stored_data__main__monthly_discount", "Discount for stored data service");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-stored_data__main__monthly_discount", "Rabatt für gespeicherten Datendienst");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-stored_data__main__monthly_discount", "Rabatt für gespeicherten Datendienst");

        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-stored_data__main__implementation_price", "Implementation fee");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-stored_data__main__implementation_price", "Einrichtungsgebühr");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-stored_data__main__implementation_price", "Einrichtungsgebühr");

        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-stored_data__main__implementation_discount", "Discount for implem. fee");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-stored_data__main__implementation_discount", "Rabatt für Einrichtungsgebühr");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-stored_data__main__implementation_discount", "Rabatt für Einrichtungsgebühr");

        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-stored_data__main__services", "Services");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-stored_data__main__services", "Dienstleistungen");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-stored_data__main__services", "Dienstleistungen");

        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-stored_data__main__employees", "Employees");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-stored_data__main__employees", "Angestellte");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-stored_data__main__employees", "Angestellte");

        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-stored_data__main__frequency", "Frequency");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-stored_data__main__frequency", "Frequenz");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-stored_data__main__frequency", "Frequenz");

        $this->langVarList[] = new LangVar("en", "php", "company", "common", "all", "All");
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "all", "Alle");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "all", "Alle");

        $this->langVarList[] = new LangVar("en", "php", "product", "common", "all", "All");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "all", "Alle");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "all", "Alle");
    }

    public function up()
    {
        $this->execute("INSERT INTO product_group (group_id,title,created,code,sort_order,receipts, need_check_image)
                        VALUES (
                            nextval('\"product_group_GroupID_seq\"'::regclass),
                            'Stored Data Service',
                            NOW(),
                            ".Connection::GetSQLString(PRODUCT_GROUP__STORED_DATA).",
                            '14',
                            'N',
                            'N'
                        )");

        $group = $this->fetchRow("SELECT group_id FROM product_group WHERE code=".Connection::GetSQLString(PRODUCT_GROUP__STORED_DATA));

        //main product
        $this->execute("INSERT INTO product (product_id,group_id,title,created,code,base_for_api)
                        VALUES (
                            nextval('\"product_ProductID_seq\"'::regclass),
                            '".$group["group_id"]."',
                            'Stored Data',
                            NOW(),
                            ".Connection::GetSQLString(PRODUCT__STORED_DATA__MAIN).",
                            'N'
                        )");
        $productMain = $this->fetchRow("SELECT product_id FROM product WHERE code=".Connection::GetSQLString(PRODUCT__STORED_DATA__MAIN));

        //options basic info
        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
                        VALUES (
                            nextval('\"option_key_KeyID_seq\"'::regclass),
                            'currency',
                            ".Connection::GetSQLString(OPTION__STORED_DATA__MAIN__IMPLEMENTATION_PRICE).",
                            'Implementation fee',
                            '3',
                            '".$productMain["product_id"]."',
                            '1',
                            'Y','Y','N'
                        )");
        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
                        VALUES (
                            nextval('\"option_key_KeyID_seq\"'::regclass),
                            'currency',
                            ".Connection::GetSQLString(OPTION__STORED_DATA__MAIN__IMPLEMENTATION_DISCOUNT).",
                            'Discount for implem. fee',
                            '4',
                            '".$productMain["product_id"]."',
                            '1',
                            'Y','Y','N'
                        )");

        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
                        VALUES (
                              nextval('\"option_key_KeyID_seq\"'::regclass),
                              'currency',
                              ".Connection::GetSQLString(OPTION__STORED_DATA__MAIN__MONTHLY_PRICE).",
                              'Monthly service price',
                              '1',
                              '".$productMain["product_id"]."',
                              '1',
                           'Y','Y','N'
                        )");
        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
                        VALUES (
                              nextval('\"option_key_KeyID_seq\"'::regclass),
                              'currency',
                              ".Connection::GetSQLString(OPTION__STORED_DATA__MAIN__MONTHLY_DISCOUNT).",
                              'Discount for stored data service',
                              '2',
                              '".$productMain["product_id"]."',
                              '1',
                            'Y','Y','N'
                        )");

        //options special values
        $this->execute("INSERT INTO option (
          option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
                        VALUES (
                              nextval('\"option_key_KeyID_seq\"'::regclass),
                              'string',
                              ".Connection::GetSQLString(OPTION__STORED_DATA__MAIN__SERVICES).",
                              'Services',
                              '1',
                              '".$productMain["product_id"]."',
                              '3',
                            'Y','Y','N'
                        )");
        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
                        VALUES (
                              nextval('\"option_key_KeyID_seq\"'::regclass),
                              'string',
                              ".Connection::GetSQLString(OPTION__STORED_DATA__MAIN__EMPLOYEES).",
                              'Employees',
                              '2',
                              '".$productMain["product_id"]."',
                              '3',
                            'Y','Y','N'
                        )");
        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
                        VALUES (
                              nextval('\"option_key_KeyID_seq\"'::regclass),
                              'string',
                              ".Connection::GetSQLString(OPTION__STORED_DATA__MAIN__FREQUENCY).",
                              'Frequency',
                              '3',
                              '".$productMain["product_id"]."',
                              '3',
                            'Y','Y','N'
                        )");
        //insert default global values for special values

        $optionID = $this->fetchRow("SELECT option_id FROM option WHERE code=".Connection::GetSQLString(OPTION__STORED_DATA__MAIN__SERVICES));
        $stmt = GetStatement(DB_CONTROL);
        $query = "INSERT INTO option_value_history (level, entity_id, option_id, created, user_id, value) VALUES (
                            ".Connection::GetSQLString(OPTION_LEVEL_GLOBAL).",
                            0,
                ".intval($optionID["option_id"]).",
                ".Connection::GetSQLDate(GetCurrentDateTime()).",
                -1,
                'all')";        
        $stmt->Execute($query);

        $optionID = $this->fetchRow("SELECT option_id FROM option WHERE code=".Connection::GetSQLString(OPTION__STORED_DATA__MAIN__EMPLOYEES));
        $stmt = GetStatement(DB_CONTROL);
        $query = "INSERT INTO option_value_history (level, entity_id, option_id, created, user_id, value) VALUES (
                            ".Connection::GetSQLString(OPTION_LEVEL_GLOBAL).",
                            0,
                ".intval($optionID["option_id"]).",
                ".Connection::GetSQLDate(GetCurrentDateTime()).",
                -1,
                'all')";        
        $stmt->Execute($query);

        $optionID = $this->fetchRow("SELECT option_id FROM option WHERE code=".Connection::GetSQLString(OPTION__STORED_DATA__MAIN__FREQUENCY));
        $stmt = GetStatement(DB_CONTROL);
        $query = "INSERT INTO option_value_history (level, entity_id, option_id, created, user_id, value) VALUES (
                            ".Connection::GetSQLString(OPTION_LEVEL_GLOBAL).",
                            0,
                ".intval($optionID["option_id"]).",
                ".Connection::GetSQLDate(GetCurrentDateTime()).",
                -1,
                'monthly')";        
        $stmt->Execute($query);

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }
    }

    public function down()
    {      
        $productMain = $this->fetchRow("SELECT product_id FROM product WHERE code=".Connection::GetSQLString(PRODUCT__STORED_DATA__MAIN));
        $optionList = $this->fetchAll("SELECT option_id FROM option WHERE product_id='".$productMain["product_id"]."'");

        $stmt = GetStatement(DB_CONTROL);
        foreach($optionList as $option)
        {
            $query = "DELETE FROM option_value_history WHERE option_id='".$option["option_id"]."'";
            $stmt->Execute($query);
        }
  
        $this->execute("DELETE FROM option WHERE product_id='".$productMain["product_id"]."'");
        $this->execute("DELETE FROM product WHERE code=".Connection::GetSQLString(PRODUCT__STORED_DATA__MAIN));
        $this->execute("DELETE FROM product_group WHERE code=".Connection::GetSQLString(PRODUCT_GROUP__STORED_DATA));

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
    }
}
