<?php

class Dictionary
{
    /**
     * Check whether any word from dict is in the $text
     *
     * @param $text
     *  Input text
     *
     * @return int
     *  Number of word entries in the $text
     */
    public function check($text)
    {
        $found = 0;

        $callback = static function ($str) {
            $str = trim($str);

            return $str;
        };

        $keywordList = Config::GetConfigValue("ocr_keywords");
        $keywordList = preg_split("/\r\n|\r|\n/", $keywordList);
        $keywordList = array_map($callback, $keywordList);

        foreach ($keywordList as $word) {
            if (stripos($text, $word) === false) {
                continue;
            }

            $found++;
        }

        return $found;
    }
}
