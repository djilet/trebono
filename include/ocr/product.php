<?php

/*
 *  Just a structure to save results
 * */

class OCRProduct
{
    public $id;     //Article
    public $title;  //Product title
    public $price;  //Value in euro
    public $vat;    //VAT assignment
    public $qty;    //Quantity

    /**
     * Product constructor.
     *
     * @param $id
     *  Product's article
     * @param $title
     *  Product's title
     * @param $price
     *  Product's value in euro
     * @param $vat
     *  VAT assignment
     * @param $qty
     *  Quantity
     */
    public function __construct($id, $title, $price, $vat, $qty)
    {
        $this->id = $id;
        $this->title = $title;
        $this->price = $price;
        $this->vat = $vat;
        $this->qty = $qty;
    }
}
