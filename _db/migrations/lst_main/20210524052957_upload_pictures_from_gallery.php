<?php

use Phinx\Migration\AbstractMigration;

class UploadPicturesFromGallery extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-".OPTION__BASE__MAIN__UPLOAD_PICTURES_FROM_GALLERY, "Bilder aus der Galerie hochladen");
        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-".OPTION__BASE__MAIN__UPLOAD_PICTURES_FROM_GALLERY, "Upload pictures from gallery");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-".OPTION__BASE__MAIN__UPLOAD_PICTURES_FROM_GALLERY, "Bilder aus der Galerie hochladen");

        $this->langVarList[] = new LangVar("de", "php", "receipt", "common", "api-add-picture-from-gallery-button", "Bilder aus der Galerie hinzufügen");
        $this->langVarList[] = new LangVar("en", "php", "receipt", "common", "api-add-picture-from-gallery-button", "Add pictures from gallery");
        $this->langVarList[] = new LangVar("tr", "php", "receipt", "common", "api-add-picture-from-gallery-button", "Bilder aus der Galerie hinzufügen");
    }

    public function up()
    {
        $productMain = $this->fetchRow("SELECT product_id FROM product WHERE code=".Connection::GetSQLString(PRODUCT__BASE__MAIN));

        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
            VALUES (
                nextval('\"option_key_KeyID_seq\"'::regclass),
                'flag',
                ".Connection::GetSQLString(OPTION__BASE__MAIN__UPLOAD_PICTURES_FROM_GALLERY).",
                'Upload pictures from gallery',
                '1',
                '".$productMain["product_id"]."',
                '3',
                'Y','Y','Y'
        )");

        $stmt = GetStatement(DB_CONTROL);
        $option = $this->fetchRow("SELECT option_id FROM option WHERE code=".Connection::GetSQLString(OPTION__BASE__MAIN__UPLOAD_PICTURES_FROM_GALLERY));
        $stmt->Execute("INSERT INTO option_value_history (level, entity_id, option_id, created, date_from, user_id, value) 
            VALUES (
                ".Connection::GetSQLString(OPTION_LEVEL_GLOBAL).",
                0,
                ".intval($option["option_id"]).",
                ".Connection::GetSQLDate(GetCurrentDateTime()).",
                ".Connection::GetSQLDate(GetCurrentDateTime()).",
                -1,
                'N'
        )");

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }
    }

    public function down()
    {
        $this->execute("DELETE FROM option WHERE code=".Connection::GetSQLString(OPTION__BASE__MAIN__UPLOAD_PICTURES_FROM_GALLERY));

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }

        $stmt = GetStatement(DB_CONTROL);
        $stmt->execute("DELETE FROM option_value_history WHERE option_id=".Option::GetOptionIDByCode(OPTION__BASE__MAIN__UPLOAD_PICTURES_FROM_GALLERY));

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
    }
}
