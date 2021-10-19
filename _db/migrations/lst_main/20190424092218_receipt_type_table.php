<?php


use Phinx\Migration\AbstractMigration;
use Phinx\Util\Literal;

class ReceiptTypeTable extends AbstractMigration
{
    private $langVarList = array();
    
    public function init()
    {
        $this->langVarList[] = new LangVar("de", "template", "product", "product_group_edit.html", "ReceiptTypes", "Eingang Typen");
        $this->langVarList[] = new LangVar("en", "template", "product", "product_group_edit.html", "ReceiptTypes", "Receipt types");
        $this->langVarList[] = new LangVar("tr", "template", "product", "product_group_edit.html", "ReceiptTypes", "Eingang Typen");
        $this->langVarList[] = new LangVar("de", "template", "product", "receipt_type_edit.html", "Code", "Code");
        $this->langVarList[] = new LangVar("en", "template", "product", "receipt_type_edit.html", "Code", "Code");
        $this->langVarList[] = new LangVar("tr", "template", "product", "receipt_type_edit.html", "Code", "Code");
        $this->langVarList[] = new LangVar("de", "template", "product", "receipt_type_edit.html", "Icon", "Symbol");
        $this->langVarList[] = new LangVar("en", "template", "product", "receipt_type_edit.html", "Icon", "Icon");
        $this->langVarList[] = new LangVar("tr", "template", "product", "receipt_type_edit.html", "Icon", "Symbol");
        $this->langVarList[] = new LangVar("de", "template", "product", "receipt_type_edit.html", "AddReceiptType", "Fügen Sie den Eingang Typ");
        $this->langVarList[] = new LangVar("en", "template", "product", "receipt_type_edit.html", "AddReceiptType", "Add receipt type");
        $this->langVarList[] = new LangVar("tr", "template", "product", "receipt_type_edit.html", "AddReceiptType", "Fügen Sie den Eingang Typ");
        $this->langVarList[] = new LangVar("de", "template", "product", "receipt_type_edit.html", "EditReceiptType", "Bearbeiten Eingang Typ");
        $this->langVarList[] = new LangVar("en", "template", "product", "receipt_type_edit.html", "EditReceiptType", "Edit receipt type");
        $this->langVarList[] = new LangVar("tr", "template", "product", "receipt_type_edit.html", "EditReceiptType", "Bearbeiten Eingang Typ");
        $this->langVarList[] = new LangVar("de", "template", "product", "product_group_list.html", "AddReceiptType", "Fügen Sie den Eingang Typ");
        $this->langVarList[] = new LangVar("en", "template", "product", "product_group_list.html", "AddReceiptType", "Add receipt type");
        $this->langVarList[] = new LangVar("tr", "template", "product", "product_group_list.html", "AddReceiptType", "Fügen Sie den Eingang Typ");
        $this->langVarList[] = new LangVar("de", "template", "product", "product_group_list.html", "ReceiptTypeList", "Eingang Typen");
        $this->langVarList[] = new LangVar("en", "template", "product", "product_group_list.html", "ReceiptTypeList", "Receipt types");
        $this->langVarList[] = new LangVar("tr", "template", "product", "product_group_list.html", "ReceiptTypeList", "Eingang Typen");
        $this->langVarList[] = new LangVar("de", "template", "product", "receipt_type_edit.html", "Translation", "Übersetzung von Liste");
        $this->langVarList[] = new LangVar("en", "template", "product", "receipt_type_edit.html", "Translation", "Translation list");
        $this->langVarList[] = new LangVar("tr", "template", "product", "receipt_type_edit.html", "Translation", "Übersetzung von Liste");
        
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "title-receipt_type", "Eingang Typ");
        $this->langVarList[] = new LangVar("en", "php", "product", "common", "title-receipt_type", "Receipt type");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "title-receipt_type", "Eingang Typ");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "code-required", "Geben Sie Eingangs-Typ-code");
        $this->langVarList[] = new LangVar("en", "php", "product", "common", "code-required", "Enter receipt type code");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "code-required", "Geben Sie Eingangs-Typ-code");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "code-is-not-unique", "Eingang Typ-code muss eindeutig sein");
        $this->langVarList[] = new LangVar("en", "php", "product", "common", "code-is-not-unique", "Receipt type code must be unique");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "code-is-not-unique", "Eingang Typ-code muss eindeutig sein");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "translation-required", "%language% übersetzung erforderlich");
        $this->langVarList[] = new LangVar("en", "php", "product", "common", "translation-required", "%language% translation required");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "translation-required", "%language% übersetzung erforderlich");
        
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "receipt-type-shop", "Lebensmittelkauf");
        $this->langVarList[] = new LangVar("en", "php", "product", "common", "receipt-type-shop", "Shop");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "receipt-type-shop", "Lebensmittelkauf");
        
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "receipt-type-restaurant", "Restaurant");
        $this->langVarList[] = new LangVar("en", "php", "product", "common", "receipt-type-restaurant", "Restaurant");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "receipt-type-restaurant", "Restaurant");
        
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "receipt-type-ticket", "Fahrkarte");
        $this->langVarList[] = new LangVar("en", "php", "product", "common", "receipt-type-ticket", "Ticket");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "receipt-type-ticket", "Fahrkarte");
        
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "receipt-type-accommodation", "Unterkunft");
        $this->langVarList[] = new LangVar("en", "php", "product", "common", "receipt-type-accommodation", "Accommodation");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "receipt-type-accommodation", "Unterkunft");
        
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "receipt-type-hospitality", "Bewirtung");
        $this->langVarList[] = new LangVar("en", "php", "product", "common", "receipt-type-hospitality", "Hospitality");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "receipt-type-hospitality", "Bewirtung");
        
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "receipt-type-parking", "Parken/Maut");
        $this->langVarList[] = new LangVar("en", "php", "product", "common", "receipt-type-parking", "Parking/Toll");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "receipt-type-parking", "Parken/Maut");
        
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "receipt-type-meal", "Verpflegung");
        $this->langVarList[] = new LangVar("en", "php", "product", "common", "receipt-type-meal", "Meals");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "receipt-type-meal", "Verpflegung");
        
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "receipt-type-other", "Sonstiges");
        $this->langVarList[] = new LangVar("en", "php", "product", "common", "receipt-type-other", "Others");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "receipt-type-other", "Sonstiges");
        
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "receipt-type-doc", "Dokumentation");
        $this->langVarList[] = new LangVar("en", "php", "product", "common", "receipt-type-doc", "Documentation");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "receipt-type-doc", "Dokumentation");
        
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "receipt-type-confirm", "Formular");
        $this->langVarList[] = new LangVar("en", "php", "product", "common", "receipt-type-confirm", "Confirmation");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "receipt-type-confirm", "Formular");
        
        $this->delLangVarList[] = new LangVar("de", "template", "receipt", "receipt_edit.html", "TypeAccommodation", "Unterkunft");
        $this->delLangVarList[] = new LangVar("en", "template", "receipt", "receipt_edit.html", "TypeAccommodation", "Accommodation");
        $this->delLangVarList[] = new LangVar("tr", "template", "receipt", "receipt_edit.html", "TypeAccommodation", "Unterkunft");
        $this->delLangVarList[] = new LangVar("de", "template", "receipt", "receipt_edit.html", "TypeConfirm", "Formular");
        $this->delLangVarList[] = new LangVar("en", "template", "receipt", "receipt_edit.html", "TypeConfirm", "Confirmation");
        $this->delLangVarList[] = new LangVar("tr", "template", "receipt", "receipt_edit.html", "TypeConfirm", "Formular");
        $this->delLangVarList[] = new LangVar("de", "template", "receipt", "receipt_edit.html", "TypeDoc", "Dokumentation");
        $this->delLangVarList[] = new LangVar("en", "template", "receipt", "receipt_edit.html", "TypeDoc", "Documentation");
        $this->delLangVarList[] = new LangVar("tr", "template", "receipt", "receipt_edit.html", "TypeDoc", "Dokumentation");
        $this->delLangVarList[] = new LangVar("de", "template", "receipt", "receipt_edit.html", "TypeHospitality", "Bewirtung");
        $this->delLangVarList[] = new LangVar("en", "template", "receipt", "receipt_edit.html", "TypeHospitality", "Hospitality");
        $this->delLangVarList[] = new LangVar("tr", "template", "receipt", "receipt_edit.html", "TypeHospitality", "Bewirtung");
        $this->delLangVarList[] = new LangVar("de", "template", "receipt", "receipt_edit.html", "TypeMeal", "Verpflegung");
        $this->delLangVarList[] = new LangVar("en", "template", "receipt", "receipt_edit.html", "TypeMeal", "Meals");
        $this->delLangVarList[] = new LangVar("tr", "template", "receipt", "receipt_edit.html", "TypeMeal", "Verpflegung");
        $this->delLangVarList[] = new LangVar("de", "template", "receipt", "receipt_edit.html", "TypeOther", "Sonstiges");
        $this->delLangVarList[] = new LangVar("en", "template", "receipt", "receipt_edit.html", "TypeOther", "Others");
        $this->delLangVarList[] = new LangVar("tr", "template", "receipt", "receipt_edit.html", "TypeOther", "Sonstiges");
        $this->delLangVarList[] = new LangVar("de", "template", "receipt", "receipt_edit.html", "TypeParking", "Parken/Maut");
        $this->delLangVarList[] = new LangVar("en", "template", "receipt", "receipt_edit.html", "TypeParking", "Parking/Toll");
        $this->delLangVarList[] = new LangVar("tr", "template", "receipt", "receipt_edit.html", "TypeParking", "Parken/Maut");
        $this->delLangVarList[] = new LangVar("de", "template", "receipt", "receipt_edit.html", "TypeRestaurant", "Restaurant");
        $this->delLangVarList[] = new LangVar("en", "template", "receipt", "receipt_edit.html", "TypeRestaurant", "Restaurant");
        $this->delLangVarList[] = new LangVar("tr", "template", "receipt", "receipt_edit.html", "TypeRestaurant", "Restaurant");
        $this->delLangVarList[] = new LangVar("de", "template", "receipt", "receipt_edit.html", "TypeShop", "Lebensmittelkauf");
        $this->delLangVarList[] = new LangVar("en", "template", "receipt", "receipt_edit.html", "TypeShop", "Shop");
        $this->delLangVarList[] = new LangVar("tr", "template", "receipt", "receipt_edit.html", "TypeShop", "Lebensmittelkauf");
        $this->delLangVarList[] = new LangVar("de", "template", "receipt", "receipt_edit.html", "TypeTicket", "Fahrkarte");
        $this->delLangVarList[] = new LangVar("en", "template", "receipt", "receipt_edit.html", "TypeTicket", "Ticket");
        $this->delLangVarList[] = new LangVar("tr", "template", "receipt", "receipt_edit.html", "TypeTicket", "Fahrkarte");
    }
    
    public function up()
    {
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
        
        $this->table("receipt_type", ["id" => "receipt_type_id"])
        ->addColumn("code", "string", ["null" => false, "length" => 255])
        ->addColumn("receipt_type_image", "string", ["null" => true, "length" => 255])
        ->addColumn("receipt_type_image_config", "text", ["null" => true])
        ->addColumn("created", "timestamp", ["null" => false])
        ->addColumn("created_by", "integer", ["null" => false])
        ->addColumn("archive", Literal::from("flag"), ["null" => false, "default" => "N"])
        ->addIndex(['code'], ['unique' => true])
        ->save();
        
        $this->table("product_group_2_receipt_type", ["id" => "product_group_receipt_type_id"])
        ->addColumn("group_id", "integer", ["null" => false])
        ->addColumn("code", "string", ["null" => false, "length" => 255])
        ->addForeignKey('group_id', 'product_group', ['group_id'])
        ->addForeignKey('code', 'receipt_type', ['code'])
        ->save();
        
        $created = Connection::GetSQLString(GetCurrentDateTime());
        $this->execute("INSERT INTO receipt_type (receipt_type_id, code, created, created_by) VALUES 
                        (nextval('\"receipt_type_receipt_type_id_seq\"'::regclass), 'shop', ".$created.", ".SERVICE_USER_ID."),
                        (nextval('\"receipt_type_receipt_type_id_seq\"'::regclass), 'restaurant', ".$created.", ".SERVICE_USER_ID."),
                        (nextval('\"receipt_type_receipt_type_id_seq\"'::regclass), 'ticket', ".$created.", ".SERVICE_USER_ID."),
                        (nextval('\"receipt_type_receipt_type_id_seq\"'::regclass), 'accommodation', ".$created.", ".SERVICE_USER_ID."),
                        (nextval('\"receipt_type_receipt_type_id_seq\"'::regclass), 'hospitality', ".$created.", ".SERVICE_USER_ID."),
                        (nextval('\"receipt_type_receipt_type_id_seq\"'::regclass), 'parking', ".$created.", ".SERVICE_USER_ID."),
                        (nextval('\"receipt_type_receipt_type_id_seq\"'::regclass), 'meal', ".$created.", ".SERVICE_USER_ID."),
                        (nextval('\"receipt_type_receipt_type_id_seq\"'::regclass), 'other', ".$created.", ".SERVICE_USER_ID."),
                        (nextval('\"receipt_type_receipt_type_id_seq\"'::regclass), 'doc', ".$created.", ".SERVICE_USER_ID."),
                        (nextval('\"receipt_type_receipt_type_id_seq\"'::regclass), 'confirm', ".$created.", ".SERVICE_USER_ID.")");
        
        $this->execute("INSERT INTO product_group_2_receipt_type (product_group_receipt_type_id, code, group_id) VALUES
                        (nextval('\"product_group_2_receipt_type_product_group_receipt_type_id_seq\"'::regclass), 'shop', ".ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__FOOD)."),
                        (nextval('\"product_group_2_receipt_type_product_group_receipt_type_id_seq\"'::regclass), 'restaurant', ".ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__FOOD)."),
                        (nextval('\"product_group_2_receipt_type_product_group_receipt_type_id_seq\"'::regclass), 'ticket', ".ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__TRAVEL)."),
                        (nextval('\"product_group_2_receipt_type_product_group_receipt_type_id_seq\"'::regclass), 'accommodation', ".ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__TRAVEL)."),
                        (nextval('\"product_group_2_receipt_type_product_group_receipt_type_id_seq\"'::regclass), 'hospitality', ".ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__TRAVEL)."),
                        (nextval('\"product_group_2_receipt_type_product_group_receipt_type_id_seq\"'::regclass), 'parking', ".ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__TRAVEL)."),
                        (nextval('\"product_group_2_receipt_type_product_group_receipt_type_id_seq\"'::regclass), 'meal', ".ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__TRAVEL)."),
                        (nextval('\"product_group_2_receipt_type_product_group_receipt_type_id_seq\"'::regclass), 'other', ".ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__TRAVEL)."),
                        (nextval('\"product_group_2_receipt_type_product_group_receipt_type_id_seq\"'::regclass), 'doc', ".ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__RECREATION)."),
                        (nextval('\"product_group_2_receipt_type_product_group_receipt_type_id_seq\"'::regclass), 'confirm', ".ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__RECREATION).")");
    
    }
    
    public function down()
    {
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
        
        $this->dropTable("product_group_2_receipt_type");
        $this->dropTable("receipt_type");
    }
}
