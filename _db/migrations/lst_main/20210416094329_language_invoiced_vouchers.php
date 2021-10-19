<?php

use Phinx\Migration\AbstractMigration;

class LanguageInvoicedVouchers extends AbstractMigration
{
    private $langVarList = [];

    public function init()
    {
        $this->langVarList = [
            new LangVar("en", "php", "core", "common", "voucher-already-invoiced", "Voucher already invoiced"),
            new LangVar("de", "php", "core", "common", "voucher-already-invoiced", "Gutschein bereits in Rechnung gestellt"),
            new LangVar("tr", "php", "core", "common", "voucher-already-invoiced", "Gutschein bereits in Rechnung gestellt"),

            new LangVar("en", "php", "core", "common", "cannot-remove-voucher", "Cannot remove voucher"),
            new LangVar("de", "php", "core", "common", "cannot-remove-voucher", "Gutschein kann nicht entfernt werden"),
            new LangVar("tr", "php", "core", "common", "cannot-remove-voucher", "Gutschein kann nicht entfernt werden"),

            new LangVar("en", "php", "core", "common", "cannot-activate-voucher", "Cannot activate voucher"),
            new LangVar("de", "php", "core", "common", "cannot-activate-voucher", "Gutschein kann nicht aktiviert werden"),
            new LangVar("tr", "php", "core", "common", "cannot-activate-voucher", "Gutschein kann nicht aktiviert werden"),
        ];
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
