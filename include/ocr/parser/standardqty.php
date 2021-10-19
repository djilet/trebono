<?php

require_once(dirname(__FILE__) . "/template.php");

class StandardQtyParser extends TemplateParser
//For template 1х -Title - Euro - TotalEuro
{
    /**
     * StandardQtyParser constructor.
     */
    public function __construct()
    {
        parent::__construct(new OCRTemplate(
            "/(.*\s)?([0-9]+)\s?x? (.*[A-Za-z].*)\s+([0-9]+)[,.]([0-9]{2})\D*([0-9]+)[,.]\s*([0-9]{2})/",
            null,
            "[3]",
            "[4].[5]",
            null,
            "[2]"
        ));
    }

    /**
     * Override of getQuality method
     * Count the number of extracted fields
     *
     * @return float|int|mixed
     */
    public function getQuality()
    {
        $res = 0;
        if ($this->products) {
            foreach ($this->products[0] as $field) {
                $res += !is_null($field);
            }
        }

        return $res * count($this->products);
    }
}
