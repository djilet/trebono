<?php

error_reporting(E_ALL);
echo "line_recognize echo";
require_once(dirname(__FILE__) . "/../../../include/init.php");

use PhpAmqpLib\Connection\AMQPStreamConnection;

$credentials = RabbitMQ::GetCredentials();
$connection = new AMQPStreamConnection(
    $credentials["Host"],
    $credentials["Port"],
    $credentials["User"],
    $credentials["Password"],
    $credentials["Vhost"],
    $insist = false,
    $login_method = 'AMQPLAIN',
    $login_response = null,
    $locale = 'en_US',
    $connection_timeout = 300,
    $read_write_timeout = 300,
    $context = null,
    $keepalive = true,
    $heartbeat = 120
);

$channel = $connection->channel();
$channel->queue_declare('line_recognize', false, true, false, false);

$callback = static function ($msg) {

    $module = "receipt";

    $receiptFile = new ReceiptFile($module);
    $message = json_decode($msg->body, true);

    if (isset($message["die"])) {
        die();
    }

    if ($receiptFile->LoadByID($message["receipt_file_id"])) {
        $countLines = 0;

        $tmpFilePath = PROJECT_DIR . "var/log/" . $receiptFile->GetProperty("file_image");

        $receipt = new Receipt("receipt");
        $receipt->LoadByID($receiptFile->GetProperty("receipt_id"));

        $specificProductGroup = SpecificProductGroupFactory::Create($receipt->GetProperty("group_id"));
        if ($specificProductGroup) {
            $fileStorage = GetFileStorage($specificProductGroup->GetContainer());

            //make temporary local file for receipt recognizing
            $fileSys = new FileSys();
            $fileSys->PutFileContent(
                $tmpFilePath,
                $fileStorage->GetFileContent(RECEIPT_IMAGE_DIR . "file/" . $receiptFile->GetProperty("file_image"))
            );

            //process image to fetch receipt data and line list
            $processor = new OCRProcesor();
            $processResult = $processor->process($tmpFilePath);

            $requestData = $processResult->requestData;
            $isSuccessful = ($processResult->status == "fail" ? "N" : "Y");
            OCRProcesor::SaveRequest(
                $requestData["created"],
                $requestData["url"],
                $requestData["response_time"],
                "ocr_2",
                $isSuccessful,
                $receiptFile->GetProperty("receipt_id"),
                SERVICE_USER_ID
            );
            $requestData = $processResult->requestDataBin;
            if ($requestData) {
                $isSuccessful = ($processResult->status == "fail" ? "N" : "Y");
                OCRProcesor::SaveRequest(
                    $requestData["created"],
                    $requestData["url"],
                    $requestData["response_time"],
                    "ocr_2",
                    $isSuccessful,
                    $receiptFile->GetProperty("receipt_id"),
                    SERVICE_USER_ID
                );
            }

            if ($processResult->status == "fail") {
                ReceiptFile::WriteLog($message["receipt_file_id"], "--------", "error");
                ReceiptFile::WriteLog($message["receipt_file_id"], "ocr server request fail", "error");
            } elseif ($processResult->status == "success") {
                @unlink($tmpFilePath);

                $receipt->LoadByID($receiptFile->GetProperty("receipt_id"));

                if (!$receipt->GetProperty("store_name") && $processResult->shopTitle) {
                    Receipt::UpdateField(
                        $receipt->GetIntProperty("receipt_id"),
                        "store_name",
                        $processResult->shopTitle
                    );
                }
                if (
                    !$receipt->GetProperty("document_date") && $processResult->dateTime &&
                    strtotime($processResult->dateTime->format('Y-m-d')) <= strtotime(GetCurrentDate()) &&
                    strtotime($processResult->dateTime->format('Y-m-d')) >= strtotime(date(
                        "Y-m-01",
                        strtotime("- 6 month")
                    ))
                ) {
                    Receipt::UpdateField(
                        $receipt->GetIntProperty("receipt_id"),
                        "document_date",
                        $processResult->dateTime->format('Y-m-d H:i:s')
                    );
                }


                foreach ($processResult->products as $product) {
                    $data = array(
                        "receipt_id" => $receiptFile->GetProperty("receipt_id"),
                        "sku" => $product->id,
                        "title" => $product->title,
                        "quantity" => $product->qty ?: 1,
                        "price" => $product->price
                    );
                    foreach ($processResult->VAT as $key => $value) {
                        if ($key != $product->vat) {
                            continue;
                        }

                        $data["vat"] = $value;
                    }

                    $receiptLine = new ReceiptLine($module, $data);
                    if (!$receiptLine->Save()) {
                        continue;
                    }

                    $countLines++;
                }

                $receiptLineList = new ReceiptLineList($module);
                $receiptLineList->LoadLineList($receiptFile->GetIntProperty('receipt_id'));

                $lineIDs = array();
                $amountApproved = 0;
                foreach ($receiptLineList->GetItems() as $receiptLine) {
                    if (!isset($receiptLine['approvable']) || $receiptLine['approvable'] != "Y") {
                        continue;
                    }

                    $lineIDs[] = $receiptLine['line_id'];
                    $amountApproved += $receiptLine['cost'];
                }

                if ($lineIDs) {
                    $stmt = GetStatement(DB_MAIN);
                    $query = "UPDATE receipt_line SET approved='Y' WHERE line_id IN (" . implode(",", $lineIDs) . ")";
                    $stmt->Execute($query);

                    if (!$receipt->GetProperty("amount_approved")) {
                        Receipt::UpdateField(
                            $receipt->GetIntProperty("receipt_id"),
                            "amount_approved",
                            $amountApproved
                        );
                    }
                }

                ReceiptFile::WriteLog($message["receipt_file_id"], "--------", "info");
                ReceiptFile::WriteLog(
                    $message["receipt_file_id"],
                    "line recognization completed, " . $countLines . " saved",
                    "info"
                );
            } else {
                ReceiptFile::WriteLog($message["receipt_file_id"], "--------", "info");
                ReceiptFile::WriteLog(
                    $message["receipt_file_id"],
                    "ocr server request fail, message: " . GetTranslation($processResult->errorCode, "receipt"),
                    "error"
                );
            }
        }
    }

    $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
};

$channel->basic_qos(null, 1, null);
$channel->basic_consume('line_recognize', '', false, false, false, false, $callback);
while (count($channel->callbacks)) {
    $channel->wait();
}

$channel->close();
$connection->close();
