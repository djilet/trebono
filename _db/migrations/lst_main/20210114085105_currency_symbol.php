<?php

use Phinx\Migration\AbstractMigration;

class CurrencySymbol extends AbstractMigration
{
    public function up()
    {
        $this->table("currency")
            ->addColumn("symbol", "string", ["null" => true])
            ->save();

        $this->execute("UPDATE currency SET symbol='€' WHERE digit='EUR'");
        $this->execute("UPDATE currency SET symbol='$' WHERE digit='USD'");
        $this->execute("UPDATE currency SET symbol='₣' WHERE digit='CHF'");
        $this->execute("UPDATE currency SET symbol='£' WHERE digit='GBP'");
        $this->execute("UPDATE currency SET symbol='¥' WHERE digit='CNY'");
        $this->execute("UPDATE currency SET symbol='kr' WHERE digit='DKK'");
        $this->execute("UPDATE currency SET symbol='Kč' WHERE digit='CZK'");
        $this->execute("UPDATE currency SET symbol='Ft' WHERE digit='HUF'");
        $this->execute("UPDATE currency SET symbol='SCR' WHERE digit='SCR'");
        $this->execute("UPDATE currency SET symbol='Rf' WHERE digit='MVR'");
        $this->execute("UPDATE currency SET symbol='kr' WHERE digit='NOK'");
        $this->execute("UPDATE currency SET symbol='zł' WHERE digit='PLN'");
        $this->execute("UPDATE currency SET symbol='₴' WHERE digit='UAH'");
        $this->execute("UPDATE currency SET symbol='₽' WHERE digit='RUB'");
    }

    public function down()
    {
        $this->table("currency")
            ->removeColumn("symbol")
            ->save();
    }
}
