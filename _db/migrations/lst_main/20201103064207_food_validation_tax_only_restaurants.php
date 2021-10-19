<?php

use Phinx\Migration\AbstractMigration;

class FoodValidationTaxOnlyRestaurants extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "receipt", "common", "flat-rate-taxation-not-restaurant", "You can only approve restaurant receipts with flat rate taxation");
        $this->langVarList[] = new LangVar("de", "php", "receipt", "common", "flat-rate-taxation-not-restaurant", "Sie können Restaurantbelege nur mit Pauschalbesteuerung genehmigen");
        $this->langVarList[] = new LangVar("tr", "php", "receipt", "common", "flat-rate-taxation-not-restaurant", "Sie können Restaurantbelege nur mit Pauschalbesteuerung genehmigen");
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
