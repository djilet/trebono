<?php

require_once(dirname(__FILE__) . "/template.php");

class Standard1Parser extends TemplateParser
//For template Article-Title-Euro-VAT
{
    /**
     * Standard1Parser constructor.
     */
    public function __construct()
    {
        parent::__construct(new OCRTemplate(
            "/(\d*) (.*) ([0-9]*),([0-9]*) ([ABCD])/",
            "[1]",
            "[2]",
            "[3].[4]",
            "[5]",
            null
        ));
        //parent::__construct(new OCRTemplate("/(\d+)\s+(.*) [^-0-9]?([0-9]+)[,.]([0-9]{2})\D*([1ABCcD])/", "[1]", "[2]", "[3].[4]", "[5]", null));
    }

    /*
     *Override of findInLine method.
     * Mean the first line of receipt a shopTitle
     */
    protected function findInLine($i, $line)
    {
        if ($i == 0) {
            $this->shopTitle = trim($line);
        }
        parent::findInLine($i, $line);
        /*  if(preg_match("/(\d{2}).(\d{2}).(\d{2}) (\d{2}):(\d{2})/", $line, $matches, PREG_OFFSET_CAPTURE))
          {
              $this->dateTime = new DateTime('20'.$matches[3][0].'-'.$matches[2][0].'-'.$matches[1][0].'T'.$matches[4][0].':'.$matches[5][0].':00.000000Z');
          }*/
    }

    /**
     * Override qetQuality method
     * Set the high rate 'cause this template is very important
     *
     * @return float|int|mixed
     */
    public function getQuality()
    {
        return count($this->products) * 6;
    }
}
