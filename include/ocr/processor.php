<?php

require_once(dirname(__FILE__) . "/dictionary.php");
require_once(dirname(__FILE__) . "/result.php");
foreach (glob(dirname(__FILE__) . "/parser/*.php") as $file) {
    require_once $file;
}

class OCRProcesor
{
    //General options - params of tesseract.
    //Look tesseract --help-extra for details
    protected $type;

    private $base_url;

    private $parser = null;
    private $quality = 0;

    protected $tmpFolder = "/tmp/";

    function OCRProcesor()
    {
        $isKubernetes = false;
        if (!empty(getenv("APP_ENV"))) {
            $isKubernetes = true;
        }

        if ($isKubernetes && !IsTestEnvironment()) {
            $this->base_url = GetFromConfig("UrlK8", "ocr");
        } elseif ($isKubernetes && IsTestEnvironment()) {
            $this->base_url = GetFromConfig("UrlK8Test", "ocr");
        } elseif (!$isKubernetes && !IsTestEnvironment() && !IsLocalEnvironment()) {
            $this->base_url = GetFromConfig("Url", "ocr");
        } else {
            $this->base_url = GetFromConfig("UrlTest", "ocr");
        }
    }

    public function CheckUrl()
    {
        return $this->base_url;
    }

    private function recognize($imgGlobalPath, $type = "simple")
    {
        $headerContentType = 'Content-Type: multipart/form-data';
        $url = $this->base_url . $type;
        $errorMessage = null;

        $cfile = makeCurlFile($imgGlobalPath);
        $post = array('file_image' => $cfile, 'max_time' => microtime(true) - SCRIPT_START_MICROTIME + 30 - 5 - 3);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, array($headerContentType));
        curl_setopt($ch, CURLOPT_USERPWD, GetFromConfig("User", "ocr") . ":" . GetFromConfig("Password", "ocr"));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);

        $created = GetCurrentDateTime();
        $output = curl_exec($ch);
        if ($output === false) {
            $errorMessage = curl_error($ch);
        }

        $info = curl_getinfo($ch);
        $responseTime = $info["total_time"] * 1000;

        $output = json_decode($output, true);

        curl_close($ch);

        if (isset($output["Data"]["result"])) {
            return array(
                "status" => "success",
                "text" => $output["Data"]["result"],
                "request_data" => [
                    "created" => $created,
                    "url" => $this->base_url,
                    "response_time" => $responseTime,
                    "is_successful" => "Y",
                ]
            );
        }

        if (isset($output["Data"]["error_code"])) {
            return array(
                "status" => "error",
                "error_code" => $output["Data"]["error_code"],
                "request_data" => [
                    "created" => $created,
                    "url" => $this->base_url,
                    "response_time" => $responseTime,
                    "is_successful" => "Y",
                ]
            );
        }

        return array(
            "status" => "fail",
            "request_data" => [
                "created" => $created,
                "url" => $this->base_url,
                "response_time" => $responseTime,
                "is_successful" => "N",
                "error_message" => $errorMessage,
            ]
        );
    }

    /**
     * Checking whether image is a receipt
     *
     * @param $imgGlobalPath
     *  global path of input image
     */
    public function check($imgGlobalPath)
    {
        $result = new Object();
        $output = $this->recognize($imgGlobalPath, "simple");

        if ($output["status"] == "success") {
            $a = preg_replace("/\r|\n/", " ", $output["text"]); //Implode in 1 line
            $a = strtolower($a);

            $dict = new Dictionary();
            $result->status = "success";
            $result->isReceipt = $dict->check($a) > 0;
            $result->requestData = $output["request_data"];
        } elseif ($output["status"] == "error") {
            $result->status = "error";
            $result->errorCode = $output["error_code"];
            $result->requestData = $output["request_data"];
        } else {
            $result->status = "fail";
            $result->requestData = $output["request_data"];
        }

        return $result;
    }

    /**
     * Main method of class
     *
     * @param $imgGlobalPath
     *  global path of input image
     *
     * @return OCRResult|object
     *  Structure with full information about input image
     */
    public function process($imgGlobalPath)
    {
        $output = $this->recognize($imgGlobalPath, "advanced");

        if ($output["status"] == "success") {
            $result = new OCRResult($output["text"]);
            $result->status = "success";
            $this->parse($output["text"]);
            $result->requestData = $output["request_data"];
        } elseif ($output["status"] == "error") {
            $result = new Object();
            $result->status = "error";
            $result->errorCode = $output["error_code"];
            $result->requestData = $output["request_data"];
            $result->requestDataBin = false;

            return $result;
        } else {
            $result = new Object();
            $result->status = "fail";
            $result->requestData = $output["request_data"];
            $result->requestDataBin = false;

            return $result;
        }

        $outputBin = $this->recognize($imgGlobalPath, "advanced-bin");

        if ($outputBin["status"] == "success") {
            if ($this->parse($outputBin["text"])) { // If the second source is better, built the result on it.
                $result = new OCRResult($textBin);
                $result->status = "success";
                $result->requestData = $output["request_data"];
            }
        }

        $result->requestDataBin = $outputBin["request_data"];

        if ($this->parser != null) { //If the parser is parsing anything
            $result->shopTitle = $this->parser->getShopTitle();
            $result->dateTime = $this->parser->getDateTime();
            $result->products = $this->parser->getProducts();
            $result->VAT = $this->parser->getVAT();
        }

        return $result;
    }

    protected function parse($text)
    {
        $parserList = array();      //All of working templates
        $parserList[] = new Standard1Parser();
        $parserList[] = new Standard2Parser();
        $parserList[] = new McDonaldsParser();
        $parserList[] = new StandardQtyParser();

        $res = false;
        foreach ($parserList as $parser) { //Parsing by every template
            $parser->parse($text);
            $quality = $parser->getQuality();
            if ($quality <= $this->quality) {
                continue;
            }

            $res = true;
            $this->quality = $quality;  //Choosing the most suitable template
            $this->parser = $parser;
        }

        return $res;
    }

    /**
     * Save request to history table ocr_request
     *
     * @param datetime $created time of receipt create
     * @param string $url ocr server Url
     * @param response $responseTime time of ocr server
     * @param string $type type of request (ocr_1/ocr_2)
     * @param int $receipt_id
     * @param int $user_id
     * @param bool $isReceipt is picture contain a receipt
     */
    public static function SaveRequest(
        $created,
        $url,
        $responseTime,
        $type,
        $isSuccessful,
        $receiptId,
        $userId,
        $isReceipt = null
    ) {
        if ($type != "ocr_1") {
            $type = "ocr_2";
        }

        if ($isSuccessful != "Y") {
            $isSuccessful = "N";
        }

        if ($isReceipt != "Y") {
            $isReceipt = "N";
        }

        $stmt = GetStatement(DB_CONTROL);
        $query = $query = "INSERT INTO ocr_request (created, user_id, url, response_time, type, is_receipt, is_successful, receipt_id) VALUES (
                    " . Connection::GetSQLString($created) . ",
                    " . intval($userId) . ",
					" . Connection::GetSQLString($url) . ",
					" . intval($responseTime) . ",
					" . Connection::GetSQLString($type) . ",
					" . ($isReceipt ? Connection::GetSQLString($isReceipt) . "," : "'',") . "
                    " . Connection::GetSQLString($isSuccessful) . ",
                    " . intval($receiptId) . ")";
        $stmt->execute($query);
    }
}
