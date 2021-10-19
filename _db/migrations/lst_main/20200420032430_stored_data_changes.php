<?php

use Phinx\Migration\AbstractMigration;

class StoredDataChanges extends AbstractMigration
{
    private $langVarList = array();
    private $delLangVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-".OPTION__STORED_DATA__MAIN__INDIVIDUAL_FILES, "Individual files for employees");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-".OPTION__STORED_DATA__MAIN__INDIVIDUAL_FILES, "Einzelne Dateien für Mitarbeiter");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-".OPTION__STORED_DATA__MAIN__INDIVIDUAL_FILES, "Einzelne Dateien für Mitarbeiter");

        $this->langVarList[] = new LangVar("en", "template", "billing", "stored_data_list.html", "Employee", "Employee");
        $this->langVarList[] = new LangVar("de", "template", "billing", "stored_data_list.html", "Employee", "Mitarbeiter");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "stored_data_list.html", "Employee", "Mitarbeiter");

        $this->langVarList[] = new LangVar("en", "php", "billing", "common", "all", "All");
        $this->langVarList[] = new LangVar("de", "php", "billing", "common", "all", "Alle");
        $this->langVarList[] = new LangVar("tr", "php", "billing", "common", "all", "Alle");

        $this->delLangVarList[] = new LangVar("en", "php", "product", "common", "option-".OPTION__STORED_DATA__MAIN__SERVICES, "Services");
        $this->delLangVarList[] = new LangVar("de", "php", "product", "common", "option-".OPTION__STORED_DATA__MAIN__SERVICES, "Dienstleistungen");
        $this->delLangVarList[] = new LangVar("tr", "php", "product", "common", "option-".OPTION__STORED_DATA__MAIN__SERVICES, "Dienstleistungen");
    }

    public function up()
    {
        $this->table("stored_data")
            ->removeColumn("services")
            ->save();

        //clear history
        $options = $this->fetchRow("SELECT option_id FROM option 
                                        WHERE code IN (
                                        ".Connection::GetSQLString(OPTION__STORED_DATA__MAIN__SERVICES).",
                                        ".Connection::GetSQLString(OPTION__STORED_DATA__MAIN__EMPLOYEES).")");
        $stmt = GetStatement(DB_CONTROL);
        $query = "DELETE FROM option_value_history WHERE option_id IN (".implode(",", array_column($options, "option_id")).")";
        $stmt->Execute($query);

        //delete option services
        $this->execute("DELETE FROM option WHERE code=".Connection::GetSQLString(OPTION__STORED_DATA__MAIN__SERVICES));

        //update option employees
        $this->execute("UPDATE option SET type='flag' WHERE code=".Connection::GetSQLString(OPTION__STORED_DATA__MAIN__EMPLOYEES));

        //insert new option
        $product = $this->fetchRow("SELECT product_id FROM product WHERE code=".Connection::GetSQLString(PRODUCT__STORED_DATA__MAIN));
        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
                            VALUES (
                                nextval('\"option_key_KeyID_seq\"'::regclass),
                                'flag',
                                ".Connection::GetSQLString(OPTION__STORED_DATA__MAIN__INDIVIDUAL_FILES).",
                                'Individual files for employees',
                                '1',
                                '".$product["product_id"]."',
                                '3',
                                'Y','Y','Y'
                            )");

        //insert default value
        $option = $this->fetchRow("SELECT option_id FROM option WHERE code=".Connection::GetSQLString(OPTION__STORED_DATA__MAIN__EMPLOYEES));
        $query = "INSERT INTO option_value_history (level, entity_id, option_id, created, user_id, value) VALUES (
                            ".Connection::GetSQLString(OPTION_LEVEL_GLOBAL).",
                            0,
                ".intval($option["option_id"]).",
                ".Connection::GetSQLDate(GetCurrentDateTime()).",
                -1,
                'Y')";
        $stmt->Execute($query);
        $option = $this->fetchRow("SELECT option_id FROM option WHERE code=".Connection::GetSQLString(OPTION__STORED_DATA__MAIN__INDIVIDUAL_FILES));
        $query = "INSERT INTO option_value_history (level, entity_id, option_id, created, user_id, value) 
                    VALUES (
                            ".Connection::GetSQLString(OPTION_LEVEL_GLOBAL).",
                            0,
                            ".intval($option["option_id"]).",
                            ".Connection::GetSQLDate(GetCurrentDateTime()).",
                            -1,
                            'N'
                    )";
        $stmt->Execute($query);
        
        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }

        foreach($this->delLangVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
    }

    public function down()
    {
        $this->table("stored_data")
            ->addColumn("services", "string", ["null" => true])
            ->save();

        $options = $this->fetchRow("SELECT option_id FROM option 
                                        WHERE code IN(
                                        ".Connection::GetSQLString(OPTION__STORED_DATA__MAIN__SERVICES).",
                                        ".Connection::GetSQLString(OPTION__STORED_DATA__MAIN__EMPLOYEES).",
                                        ".Connection::GetSQLString(OPTION__STORED_DATA__MAIN__INDIVIDUAL_FILES).")");
        $stmt = GetStatement(DB_CONTROL);
        $query = "DELETE FROM option_value_history WHERE option_id IN (".implode(",", array_column($options, "option_id")).")";
        $stmt->Execute($query);

        $product = $this->fetchRow("SELECT product_id FROM product WHERE code=".Connection::GetSQLString(PRODUCT__STORED_DATA__MAIN));
        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
							nextval('\"option_key_KeyID_seq\"'::regclass),
                            'string',
                            ".Connection::GetSQLString(OPTION__STORED_DATA__MAIN__SERVICES).",
                            'Services',
                            '1',
                            '".$product["product_id"]."',
                            '3',
							'Y','Y','N'
						)");

        $this->execute("UPDATE option SET type='string' WHERE code=".Connection::GetSQLString(OPTION__STORED_DATA__MAIN__EMPLOYEES));

        $this->execute("DELETE FROM option WHERE code=".Connection::GetSQLString(OPTION__STORED_DATA__MAIN__INDIVIDUAL_FILES));

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
