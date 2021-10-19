<?php


use Phinx\Migration\AbstractMigration;

class CurrenciesTable extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("de", "template", "receipt", "receipt_edit.html", "Currency", "Währung");
        $this->langVarList[] = new LangVar("en", "template", "receipt", "receipt_edit.html", "Currency", "Currency");
        $this->langVarList[] = new LangVar("tr", "template", "receipt", "receipt_edit.html", "Currency", "Währung");
    }

    public function up()
    {
        $this->table("currency", ["id" => "currency_id"])
            ->addColumn("title", "text", ["null" => false])
            ->addColumn("digit", "string", ["length" => 3, "null" => false])
            ->save();

        $this->table("receipt")
            ->addColumn("currency_id", "integer", ["null" => true])
            ->save();

        $query = "INSERT INTO currency (title, digit) VALUES
            ('Euro', 'EUR'),
            ('Russian ruble', 'RUR'),
            ('U.S. dollar', 'USD');";
        $this->execute($query);

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }
    }

    public function down()
    {
        $this->dropTable("currency");

        $this->table("receipt")
            ->removeColumn("currency_id")
            ->save();

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
    }
}
