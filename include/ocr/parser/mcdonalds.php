<?php

require_once(dirname(__FILE__) . "/template.php");

class McDonaldsParser extends TemplateParser
{
    protected $isMcDonalds = false;

    /**
     * McDonaldsParser constructor.
     */
    public function __construct()
    {
        parent::__construct(new OCRTemplate("/(\d+) (.*) ([0-9]*)[.,]([0-9]*)/", null, "[2]", "[3].[4]", null, null));
    }

    /**
     * Overrides findInLine method
     * Works for a special type of receipts (McDonalds)
     *
     * @param $i
     *  Index of line
     * @param $line
     *  Text line
     */
    protected function findInLine($i, $line)
    {
        if ($i > 2 && $i < 6) {
            if (strpos(strtolower($line), 'mcdonald') !== false) {
                $this->shopTitle = trim($line);
                $this->isMcDonalds = true;
            }
        }
        if (
            !preg_match(
                "/([0-3]\d)\/([01]\d)\/(\d{4}) (\d{2}):(\d{2}):(\d{2})/",
                $line,
                $matches,
                PREG_OFFSET_CAPTURE
            )
        ) {
            return;
        }

        try {
            $this->dateTime = new DateTime($matches[3][0] . '-' . $matches[2][0] . '-' . $matches[1][0] . 'T' . $matches[4][0] . ':' . $matches[5][0] . ':' . $matches[6][0] . '.000000Z');
        } catch (Exception $e) {
        }
    }

    /**
     * Overrides getQuality method
     * Returns 0 if input receipt is not by McDonalds
     *
     * @return float|int|mixed
     */
    public function getQuality()
    {
        if ($this->isMcDonalds) {
            return parent::getQuality();
        }

        return 0;
    }

    /**
     * Overrides getVAT method
     * VAT value for McDonalds is 19.00% always
     *
     * @return array|mixed
     */
    public function getVAT()
    {
        return array(19.00);
    }
}
