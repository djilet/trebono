<?php

/*
 * Data structure for results
 */

class OCRResult
{
    public $text;   // Prime result - text at all
    public $shopTitle;
    public $dateTime;   // Some data we tried to extract
    public $products = array(); //Main data - rows of products
    public $VAT = array();  //Info about VAT

    /**
     * OCRResult constructor.
     * Example usage:
     *
     * @param $text
     *  Input text. Any in general.
     *
     * @code
     *  $text = tesseract($image);
     *  $result = new OCRResult($text);
     */
    public function __construct($text)
    {
        $this->text = $text;
    }
}

/*
 * Auxiliary class for pre-processing
 *
 */

class OCRBooleanResult
{
    public $text;
    public $isReceipt;  //Save info about is image a receipt

    /**
     * OCRBooleanResult constructor.
     * Example usage:
     *
     * @param $text
     *  Input text. Any in general.
     *
     * @code
     *  $text = tesseract($image);
     *  $result = new OCRBooleanResult($text);
     */
    public function __construct($text)
    {
        $this->text = $text;
    }
}
