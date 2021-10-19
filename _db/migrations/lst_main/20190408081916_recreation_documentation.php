<?php


use Phinx\Migration\AbstractMigration;

class RecreationDocumentation extends AbstractMigration
{
    private $langVarList = array();
    
    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-recreation__main__max_doc_receipt_file_count", "Max. number of documentation photos");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-recreation__main__max_doc_receipt_file_count", "Max. Anzahl der Dokumentationsfotos");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-recreation__main__max_doc_receipt_file_count", "Max. Anzahl der Dokumentationsfotos");
 
        $this->langVarList[] = new LangVar("en", "template", "receipt", "receipt_edit.html", "TypeDoc", "Documentation");
        $this->langVarList[] = new LangVar("de", "template", "receipt", "receipt_edit.html", "TypeDoc", "Dokumentation");
        $this->langVarList[] = new LangVar("tr", "template", "receipt", "receipt_edit.html", "TypeDoc", "Dokumentation");
        
        $this->langVarList[] = new LangVar("en", "template", "receipt", "receipt_edit.html", "TypeConfirm", "Confirmation");
        $this->langVarList[] = new LangVar("de", "template", "receipt", "receipt_edit.html", "TypeConfirm", "Formular");
        $this->langVarList[] = new LangVar("tr", "template", "receipt", "receipt_edit.html", "TypeConfirm", "Formular");
    }
    
    public function up()
    {
        $specificProductGroup = SpecificProductGroupFactory::CreateByCode(PRODUCT_GROUP__RECREATION);
        $productID = Product::GetProductIDByCode($specificProductGroup->GetMainProductCode());
        
        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
							nextval('\"option_key_KeyID_seq\"'::regclass),
                            'int',
                            'recreation__main__max_doc_receipt_file_count',
                            'Max. number of documentation photos',
                            '4',
                            '".$productID."',
                            '3',
							'Y','N','N'
						)");
        
        $option = $this->fetchRow("SELECT option_id FROM option WHERE code='recreation__main__max_doc_receipt_file_count'");
        
        $stmt = GetStatement(DB_CONTROL);
        $query = "INSERT INTO option_value_history (level, entity_id, option_id, created, user_id, value) VALUES (
                            ".Connection::GetSQLString(OPTION_LEVEL_GLOBAL).",
                            0,
    						".intval($option["option_id"]).",
    						".Connection::GetSQLDate(GetCurrentDateTime()).",
    						-2,
    						'3')";
        
        $stmt->Execute($query);
        
        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }
    }
    
    public function down()
    {
        $this->execute("DELETE FROM option WHERE code='recreation__main__max_doc_receipt_file_count'");
        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
    }
}
