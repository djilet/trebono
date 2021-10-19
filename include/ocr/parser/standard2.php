<?php

require_once(dirname(__FILE__) . "/template.php");

class Standard2Parser extends TemplateParser
//For template Title - Euro - VAT
{
    /**
     * Standard2Parser constructor.
     */
    public function __construct()
    {
        parent::__construct(new OCRTemplate(
            "/(.*) [^-0-9]?([0-9]+)[,.]([0-9]{2})(\D*)([1ABCcD])/",
            null,
            "[1]",
            "[2].[3]",
            "[5]",
            null
        ));
    }
}
