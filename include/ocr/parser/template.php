<?php

require_once(dirname(__FILE__) . "/../parser.php");
require_once(dirname(__FILE__) . "/../product.php");

/*
 * Base class for parsers.
 * Defines data structure and base methods
 */

class TemplateParser implements Parser
    /*
     * Enough flexible.
     * Children can override template, Quality and findInLine rules, or use from here.
     *
     * */
{
    protected $template;    //Preg-template for input processing
    protected $products;    //Output - rows of products
    protected $shopTitle;
    protected $dateTime;    //Some data we try to extract
    protected $VAT;         // Info about VAT - extracted or predefined
    protected $mwstFlag;    // Auxiliary - info about MwSt rows in receipt

    /**
     * TemplateParser constructor.
     * Example usage:
     *
     * @param $template
     *  Preg-template for input processing
     *
     * @code
     * $parser = new TemplateParser(new OCRTemplate("/(\d+) (.*) ([0-9]*)[.,]([0-9]*)/", null, "[2]", "[3].[4]", null, null));
     */
    public function __construct($template)
    {
        $this->template = $template;    //Generally, Parser is defined by its template
        $this->mwstFlag = false;
    }

    /**
     * Main method of class.
     * Advice not to override
     * Try to extract as much data as possible from input text
     * Example usage:
     *
     * @param $text
     *  Input text. Any in general.
     *
     * @return mixed|void
     *  Void as usual. Just modify inner fields.
     *
     * @code
     *  $parser = new TemplateParser(new OCRTemplate($_POST["Regexp"], $_POST["IdRule"], $_POST["TitleRule"], $_POST["PriceRule"], $_POST["VatRule"], null));
     * $parser->parse($_POST["Text"]);
     */
    public function parse($text)
    {
        $this->products = array();
        $this->VAT = array();
        $this->shopTitle = null;
        $this->dateTime = null;
        /*Pre-processing*/
        $text = preg_replace("/—/u", "-", $text); //Some specials chars
        $lines = preg_split("/((\r?\n)|(\r\n?))/", $text);  //Split input into lines

        /*Processing*/
        for ($i = 0; $i < count($lines); $i++) { //Process every line
            $line = $lines[$i];
            if (preg_match($this->template->regexp, $line, $matches, PREG_OFFSET_CAPTURE)) {
                $id = $this->template->idRule;
                $title = $this->template->titleRule;
                $price = $this->template->priceRule;
                $vat = $this->template->vatRule;
                $qty = $this->template->qtyRule;    //Just variables, easy to read, easy to prepare
                for ($j = 0; $j < count($matches); $j++) {   //If the field is found by the rule, save to corresponding variable
                    if ($id) {
                        $id = str_replace("[" . $j . "]", $matches[$j][0], $id);
                    }
                    if ($title) {
                        $title = str_replace("[" . $j . "]", $matches[$j][0], $title);
                    }
                    if ($price) {
                        $price = str_replace("[" . $j . "]", $matches[$j][0], $price);
                    }
                    if ($vat) {
                        $vat = str_replace("[" . $j . "]", $matches[$j][0], $vat);
                    }
                    if (!$qty) {
                        continue;
                    }

                    $qty = str_replace("[" . $j . "]", $matches[$j][0], $qty);
                }
                $this->products[] = new OCRProduct($id, $title, floatval($price), $vat, $qty); //Save data to result row
            }
            $this->findInLine($i, $line); //Try to extract more data
        }
    }

    /**
     * We need a measure to compare results of different Templates.
     * Name it "Quality"
     *
     * @return float|int|mixed
     *  Number as usual. As greater, as parser is "better"
     */
    public function getQuality()
    {
        //return count($this->products);
        $res = 0;
        if ($this->products) {
            foreach ($this->products[0] as $field) {
                $res += !is_null($field);
            }
        }

        return $res * count($this->products);
    }

    /**
     * Getter of products field
     * Example of usage:
     *
     * @return mixed
     *  Array of Product objects.
     *
     * @code
     *  $parser = new TemplateParser(new OCRTemplate($_POST["Regexp"], $_POST["IdRule"], $_POST["TitleRule"], $_POST["PriceRule"], $_POST["VatRule"], null));
     * $parser->parse($_POST["Text"]);
     * $result = print_r($parser->getProducts(), true);
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * Getter of shopTitle field
     *
     * @return mixed
     *  String as usual
     */
    public function getShopTitle()
    {
        return $this->shopTitle;
    }

    /**
     * Getter of dateTime fiald
     *
     * @return mixed
     *  String as usual
     */
    public function getDateTime()
    {
        return $this->dateTime;
    }

    /**
     * Getter of VAT field
     *
     * @return mixed
     *  Associative array ['Name of VAT'] => Value in percents
     */
    public function getVAT()
    {
        return $this->VAT;
    }

    /**
     * Some general preg-templates to extract as much data as possible
     *
     * @param $i
     *  Index of input line
     * @param $line
     *  Input test line
     */
    protected function findInLine($i, $line)
    {
        try {
            /*Trying to extract VAT-data*/
            if (preg_match("/(\S*)(\S)\s+(\d{0,2}[,.]?\d{1,2})%.*(Nett|MwSt)/", $line, $vat)) {
                $key = $vat[1] ?: $vat[2];
                $this->VAT[$key] = floatval(str_replace(",", ".", $vat[3]));
            } else {
                if (preg_match("/(Nett|MwSt)/", $line, $vat)) {
                    $this->mwstFlag = true;
                } else {
                    if ($this->mwstFlag && preg_match("/(\S*)\s?(\S)\s+(\d{0,2}[,.]?\d{1,2})\s?%/", $line, $vat)) {
                        $key = $vat[1] ?: $vat[2];
                        $this->VAT[$key] = floatval(str_replace(",", ".", $vat[3]));
                    } else {
                        $this->mwstFlag = false;
                    }
                }
            }

            /*Trying to extract date*/
            $date = array();

            if (preg_match("/(\d{2})\.(\d{2})\.(\d{2})\s+/", $line, $date, PREG_OFFSET_CAPTURE)) {
                $date[3][0] = "20" . $date[3][0];
            } else {
                preg_match("/(\d{2})\.(\d{2})\.(\d{4})/", $line, $date, PREG_OFFSET_CAPTURE);
            }
            preg_match("/(\d{2}):(\d{2})(:\d{2})?/", $line, $time, PREG_OFFSET_CAPTURE);

            if (!$date && !$this->dateTime) {
                return;
            }

            if (!$this->dateTime) {
                $this->dateTime = new DateTime($date[3][0] . '-' . $date[2][0] . '-' . $date[1][0] . 'T00:00:00.000000Z');
            }

            if ($time) {
                $this->dateTime->setTime(
                    $time[1][0],
                    $time[2][0],
                    isset($time[3][0]) ? intval(str_replace(":", "", $time[3][0])) : 0
                );
            }
        } catch (Exception $e) {
        }
    }
}


/*Structure for preg-templates*/

class OCRTemplate
{
    public $regexp;     //General preg-template
    // ..and indices of it's parts
    public $idRule;
    public $titleRule;
    public $priceRule;
    public $vatRule;
    public $qtyRule;

    /**
     * OCRTemplate constructor.
     * Example usage:
     *
     * @param $regexp
     *  General preg template
     * @param $idRule
     *  Index of id in match array
     * @param $titleRule
     *  Index of title in match array
     * @param $priceRule
     *  Index of price in match array
     * @param $vatRule
     *  Index of VAT in match array
     * @param $qtyRule
     *  Index of quantity in match array
     *
     * @code
     *  $parser = new TemplateParser(new OCRTemplate($_POST["Regexp"], $_POST["IdRule"], $_POST["TitleRule"], $_POST["PriceRule"], $_POST["VatRule"], null));
     */
    public function __construct($regexp, $idRule, $titleRule, $priceRule, $vatRule, $qtyRule)
    {
        $this->regexp = $regexp;
        $this->idRule = $idRule;
        $this->titleRule = $titleRule;
        $this->priceRule = $priceRule;
        $this->vatRule = $vatRule;
        $this->qtyRule = $qtyRule;
    }
}
