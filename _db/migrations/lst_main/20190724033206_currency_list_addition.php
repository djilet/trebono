<?php


use Phinx\Migration\AbstractMigration;

class CurrencyListAddition extends AbstractMigration
{
    private $currencyList = array();

    public function init()
    {
        $this->currencyList[] = array ("title" => "Swiss franc", "digit" => "CHF");
        $this->currencyList[] = array ("title" => "Pound sterling", "digit" => "GBP");
        $this->currencyList[] = array ("title" => "Chinese yuan", "digit" => "CNY");
        $this->currencyList[] = array ("title" => "Danish krone", "digit" => "DKK");
        $this->currencyList[] = array ("title" => "Czech koruna", "digit" => "CZK");
        $this->currencyList[] = array ("title" => "Hungarian forint", "digit" => "HUF");
        $this->currencyList[] = array ("title" => "Seychellois rupee", "digit" => "SCR");
        $this->currencyList[] = array ("title" => "Maldivian rufiyaa", "digit" => "MVR");
        $this->currencyList[] = array ("title" => "Norwegian krone", "digit" => "NOK");
        $this->currencyList[] = array ("title" => "Polish złoty", "digit" => "PLN");
        $this->currencyList[] = array ("title" => "Ukrainian hryvnia", "digit" => "UAH");
    }

    public function up()
    {
        foreach($this->currencyList as $currency)
        {
            $query = "INSERT INTO currency (title, digit) VALUES
            ('".$currency["title"]."', '".$currency["digit"]."');";
            $this->execute($query);
        }

        $query = "UPDATE currency SET digit='RUB' WHERE digit='RUR'";
        $this->execute($query);
    }

    public function down()
    {
        foreach($this->currencyList as $currency)
        {
            $query = "DELETE FROM currency WHERE digit = '".$currency["digit"]."';";
            $this->execute($query);
        }

        $query = "UPDATE currency SET digit='RUR' WHERE digit='RUB'";
        $this->execute($query);
    }
}
