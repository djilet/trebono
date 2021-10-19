<?php

use Phinx\Migration\AbstractMigration;

class RecreationChangesService extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-recreation__main__confirmation_with_picture", "Confirmation with picture");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-recreation__main__confirmation_with_picture", "Bestätigung mit Bild");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-recreation__main__confirmation_with_picture", "Bestätigung mit Bild");

        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-recreation__main__confirmation_message", "Confirmation message");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-recreation__main__confirmation_message", "Bestätigungsmeldung");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-recreation__main__confirmation_message", "Bestätigungsmeldung");

        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-recreation__main__confirmation_transaction_message", "Confirmation message for transaction");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-recreation__main__confirmation_transaction_message", "Bestätigungsnachricht für die Transaktion");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-recreation__main__confirmation_transaction_message", "Bestätigungsnachricht für die Transaktion");

        $this->langVarList[] = new LangVar("en", "php", "receipt", "common", "recreation-fields-missing", "Fields material_status or child_count are missing");
        $this->langVarList[] = new LangVar("de", "php", "receipt", "common", "recreation-fields-missing", "Felder material_status oder child_count fehlen");
        $this->langVarList[] = new LangVar("tr", "php", "receipt", "common", "recreation-fields-missing", "Felder material_status oder child_count fehlen");

        $this->langVarList[] = new LangVar("en", "template", "receipt", "receipt_edit.html", "MaterialStatus", "Material status");
        $this->langVarList[] = new LangVar("de", "template", "receipt", "receipt_edit.html", "MaterialStatus", "Familienstand");
        $this->langVarList[] = new LangVar("tr", "template", "receipt", "receipt_edit.html", "MaterialStatus", "Familienstand");

        $this->langVarList[] = new LangVar("en", "template", "receipt", "receipt_edit.html", "ChildCount", "Child count");
        $this->langVarList[] = new LangVar("de", "template", "receipt", "receipt_edit.html", "ChildCount", "Anzahl Kinder im Haushalt");
        $this->langVarList[] = new LangVar("tr", "template", "receipt", "receipt_edit.html", "ChildCount", "Anzahl Kinder im Haushalt");
    }

    public function up()
    {
        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
							nextval('\"option_key_KeyID_seq\"'::regclass),
                            'flag',
                            ".Connection::GetSQLString(OPTION__RECREATION__MAIN__CONFIRMATION_WITH_PICTURE).",
                            'Recreation Service Confirmation with picture',
                            '6',
                            ".Product::GetProductIDByCode(PRODUCT__RECREATION__MAIN).",
                            '3',
							'Y','Y','Y'
						)");
        $optionID = $this->fetchRow("SELECT option_id FROM option WHERE code=".Connection::GetSQLString(OPTION__RECREATION__MAIN__CONFIRMATION_WITH_PICTURE));
        $stmt = GetStatement(DB_CONTROL);
        $query = "INSERT INTO option_value_history (level, entity_id, option_id, created, user_id, value) VALUES (
                            ".Connection::GetSQLString(OPTION_LEVEL_GLOBAL).",
                            0,
    						".$optionID["option_id"].",
    						".Connection::GetSQLDate(GetCurrentDate()).",
    						1,
    						'N')";
        $stmt->Execute($query);

        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
							nextval('\"option_key_KeyID_seq\"'::regclass),
                            'string',
                            ".Connection::GetSQLString(OPTION__RECREATION__MAIN__CONFIRMATION_MESSAGE).",
                            'Recreation Service Confirmation message',
                            '7',
                            ".Product::GetProductIDByCode(PRODUCT__RECREATION__MAIN).",
                            '3',
							'Y','Y','N'
						)");
        $optionID = $this->fetchRow("SELECT option_id FROM option WHERE code=".Connection::GetSQLString(OPTION__RECREATION__MAIN__CONFIRMATION_MESSAGE));
        $query = "INSERT INTO option_value_history (level, entity_id, option_id, created, user_id, value) VALUES (
                            ".Connection::GetSQLString(OPTION_LEVEL_GLOBAL).",
                            0,
    						".$optionID["option_id"].",
    						".Connection::GetSQLDate(GetCurrentDate()).",
    						1,
    						'Do you want to create confirmation entry?')";
        $stmt->Execute($query);

        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
							nextval('\"option_key_KeyID_seq\"'::regclass),
                            'string',
                            ".Connection::GetSQLString(OPTION__RECREATION__MAIN__CONFIRMATION_TRANSACTION_MESSAGE).",
                            'Recreation Service Confirmation message',
                            '8',
                            ".Product::GetProductIDByCode(PRODUCT__RECREATION__MAIN).",
                            '3',
							'Y','Y','N'
						)");
        $optionID = $this->fetchRow("SELECT option_id FROM option WHERE code=".Connection::GetSQLString(OPTION__RECREATION__MAIN__CONFIRMATION_TRANSACTION_MESSAGE));
        $query = "INSERT INTO option_value_history (level, entity_id, option_id, created, user_id, value) VALUES (
                            ".Connection::GetSQLString(OPTION_LEVEL_GLOBAL).",
                            0,
    						".$optionID["option_id"].",
    						".Connection::GetSQLDate(GetCurrentDate()).",
    						1,
    						'Is everything correct?')";
        $stmt->Execute($query);

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }
    }

    public function down()
    {
        $stmt = GetStatement(DB_CONTROL);
        $stmt->Execute("DELETE FROM option_value_history WHERE option_id=".Option::GetOptionIDByCode(OPTION__RECREATION__MAIN__CONFIRMATION_WITH_PICTURE));

        $this->execute("DELETE FROM option WHERE code=".Connection::GetSQLString(OPTION__RECREATION__MAIN__CONFIRMATION_WITH_PICTURE));

        $stmt = GetStatement(DB_CONTROL);
        $stmt->Execute("DELETE FROM option_value_history WHERE option_id=".Option::GetOptionIDByCode(OPTION__RECREATION__MAIN__CONFIRMATION_MESSAGE));

        $this->execute("DELETE FROM option WHERE code=".Connection::GetSQLString(OPTION__RECREATION__MAIN__CONFIRMATION_MESSAGE));

        $stmt = GetStatement(DB_CONTROL);
        $stmt->Execute("DELETE FROM option_value_history WHERE option_id=".Option::GetOptionIDByCode(OPTION__RECREATION__MAIN__CONFIRMATION_TRANSACTION_MESSAGE));

        $this->execute("DELETE FROM option WHERE code=".Connection::GetSQLString(OPTION__RECREATION__MAIN__CONFIRMATION_TRANSACTION_MESSAGE));

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
    }
}
