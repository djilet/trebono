<?php

use Phinx\Migration\AbstractMigration;

class LanguageUploadPicturesFromGallery extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("de", "php", "receipt", "common", "api-add-picture-from-gallery-button-description", "Schaltfläche zum Hinzufügen von Bildern aus der Galerie");
        $this->langVarList[] = new LangVar("en", "php", "receipt", "common", "api-add-picture-from-gallery-button-description", "Button for adding pictures from the gallery");
        $this->langVarList[] = new LangVar("tr", "php", "receipt", "common", "api-add-picture-from-gallery-button-description", "Schaltfläche zum Hinzufügen von Bildern aus der Galerie");
    }

    public function up()
    {
        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }
    }

    public function down()
    {
        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
    }
}
