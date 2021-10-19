<?php

/*
 * Interface to know what methods we should implement
 *
 */

interface Parser
{
    /**
     * Main method of lib
     * Implement to parse the textdata
     *
     * @param $text
     *  Input textdata
     *
     * @return mixed
     *  Nothing as usual
     */
    public function parse($text);

    /**
     * Use to measure the result of parse
     *
     * @return mixed
     *  Number as usual
     */
    public function getQuality();

    /**
     * Getter of products field
     *
     * @return mixed
     *  Array of Product objects
     */
    public function getProducts();

    /**
     * Getter of shopTitle field
     *
     * @return mixed
     *  String as usual
     */
    public function getShopTitle();

    /**
     * Getter of dateTime field
     *
     * @return mixed
     *  String as usual
     */
    public function getDateTime();
}
