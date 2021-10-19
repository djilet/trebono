<?php

use Phinx\Migration\AbstractMigration;

class LanguageVoucherCategoryButton extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "preferred-voucher-category-select-description", "Schaltfläche zur Auswahl der bevorzugten Gutscheinkategorie");
        $this->langVarList[] = new LangVar("en", "php", "product", "common", "preferred-voucher-category-select-description", "Button for select of preferred voucher category");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "preferred-voucher-category-select-description", "Schaltfläche zur Auswahl der bevorzugten Gutscheinkategorie");

        $this->langVarList[] = new LangVar("de", "php", "company", "common", "preferred-voucher-category-select", "Kategorie für nächsten Gutschein: %category%");
        $this->langVarList[] = new LangVar("en", "php", "company", "common", "preferred-voucher-category-select", "Category for voucher: %category%");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "preferred-voucher-category-select", "Kategorie für nächsten Gutschein: %category%");
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
