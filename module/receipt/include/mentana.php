<?php

class Mentana extends CommonObject
{
    private static $login = "serviceuser";
    private static $password = "9pWBHtS52aLjoXbAHLcz";
    //private static $signServiceURL = "https://185.80.184.67:8443/as-soap/SignService?wsdl";
    //private static $verificationServiceURL = "https://185.80.184.79:8443/av-soap/VerificationService?wsdl";
    private static $signServiceURL = "https://signatur.trebono.de:8443/as-soap/SignService?wsdl";
    private static $verificationServiceURL = "https://verify.trebono.de:8443/av-soap/VerificationService?wsdl";

    /**
     * Creates signature for receipt file
     */
    public static function Sign($receiptFileID)
    {
        $receiptFile = new ReceiptFile("receipt");
        $receiptFile->LoadByID($receiptFileID);

        $receipt = new Receipt("receipt");
        if (!$receipt->LoadByID($receiptFile->GetProperty("receipt_id"))) {
            return false;
        }

        $specificProductGrpup = SpecificProductGroupFactory::Create($receipt->GetProperty("group_id"));
        $container = $specificProductGrpup->GetContainer();

        $fileName = $receiptFile->GetProperty("file_image");
        $filePath = RECEIPT_IMAGE_DIR . "file/" . $fileName;
        ReceiptFile::WriteLog($receiptFileID, "--------", "info");
        ReceiptFile::WriteLog($receiptFileID, "starting mentana sign creation", "info");

        $client = self::GetSoapClient(self::$signServiceURL, $receiptFileID);
        if (!$client) {
            return false;
        }

        /*
        print_r($client);

        $functions = $client->__getFunctions();
        print_r($functions);

        $types = $client->__getTypes();
        print_r($types);
        */

        try {
            $fileStorage = GetFileStorage($container);
            $fileData = $fileStorage->GetFileContent($filePath);
            //create async sign task
            $requestSignatureParams = array(
                "SignRequest" => array(
                    "data" => $fileData,
                    "filename" => $fileName
                )
            );
            $requestSignatureResult = $client->requestSignature($requestSignatureParams);

            if (is_object($requestSignatureResult)) {
                if (
                    in_array(
                        $requestSignatureResult->return->status,
                        array(0, 21)
                    ) && $requestSignatureResult->return->taskid
                ) {
                    //ReceiptFile::WriteLog($receiptFileID, print_r($requestSignatureResult, true), "info");
                    ReceiptFile::WriteLog(
                        $receiptFileID,
                        "signature is requested successfully. tskid: " . $requestSignatureResult->return->taskid,
                        "info"
                    );

                    for ($i = 0; $i < 10; $i++) {
                        ReceiptFile::WriteLog($receiptFileID, "getting status of signature creation task", "info");

                        //get status of created signing task
                        $getStatusParams = array(
                            "SignRequestHandle" => array(
                                "status" => "",
                                "taskid" => $requestSignatureResult->return->taskid
                            )
                        );
                        $getStatusResult = $client->getStatus($getStatusParams);
                        //ReceiptFile::WriteLog($receiptFileID, print_r($getStatusResult, true), "info");

                        if (is_object($getStatusResult) && $getStatusResult->return == 0) {
                            //signing task is completed successfully - get sign
                            $getSignedDocumentParams = array(
                                "SignRequestHandle" => array(
                                    "status" => "",
                                    "taskid" => $requestSignatureResult->return->taskid
                                )
                            );
                            $getSignedDocumentResult = $client->getSignedDocument($getSignedDocumentParams);

                            if (is_object($getSignedDocumentResult) && $getSignedDocumentResult->return->resultcode == 0) {
                                ReceiptFile::WriteLog($receiptFileID, "file signature is gained successfully", "info");
                                //ReceiptFile::WriteLog($receiptFileID, print_r($getSignedDocumentResult, true), "info");
                                $fileStorage->PutFileContent(
                                    $filePath . ".p7s",
                                    $getSignedDocumentResult->return->signature
                                );

                                return true;
                            }

                            if (is_object($getSignedDocumentResult)) {
                                ReceiptFile::WriteLog(
                                    $receiptFileID,
                                    "incorrect response to getSignedDocument request",
                                    "error"
                                );
                            } else {
                                ReceiptFile::WriteLog(
                                    $receiptFileID,
                                    "cannot get signature. code:" . $getSignedDocumentResult->return->resultcode,
                                    "error"
                                );
                            }

                            break;
                        } elseif (is_object($getStatusResult) && $getStatusResult->return == 21) {
                            //signing task still in progress - wait and retry
                            ReceiptFile::WriteLog(
                                $receiptFileID,
                                "signing task is in progress. waiting for 5 seconds",
                                "info"
                            );
                            sleep(5);
                        } elseif (is_object($getStatusResult) && !in_array($getStatusResult->return, array(0, 21))) {
                            //signing task failed - exit with error
                            ReceiptFile::WriteLog(
                                $receiptFileID,
                                "signing task is completed unsuccessfully. code:" . $getStatusResult->return,
                                "error"
                            );
                            break;
                        } else {
                            //incorrect response from signing service - exit with error
                            ReceiptFile::WriteLog($receiptFileID, "incorrect response to getStatus request", "error");
                            break;
                        }
                    }
                    ReceiptFile::WriteLog($receiptFileID, "try limit exceeded", "error");

                    return false;
                }

                ReceiptFile::WriteLog(
                    $receiptFileID,
                    "cannot create requestSignature task. status:" . $requestSignatureResult->return->status,
                    "error"
                );

                return false;
            }

            ReceiptFile::WriteLog($receiptFileID, "incorrect response to requestSignature request", "error");

            return false;
        } catch (Exception $e) {
            ReceiptFile::WriteLog($receiptFileID, "exception: " . $e->getMessage(), "error");
            ReceiptFile::WriteLog($receiptFileID, "exception trace: " . $e->getTraceAsString(), "error");

            return false;
        }
    }

    /**
     * Verify signature for receipt file and create verify report
     */
    public static function Verify($receiptFileID)
    {
        $receiptFile = new ReceiptFile("receipt");
        $receiptFile->LoadByID($receiptFileID);

        $receipt = new Receipt("receipt");
        if (!$receipt->LoadByID($receiptFile->GetProperty("receipt_id"))) {
            return false;
        }

        $specificProductGrpup = SpecificProductGroupFactory::Create($receipt->GetProperty("group_id"));
        $container = $specificProductGrpup->GetContainer();

        $fileName = $receiptFile->GetProperty("file_image");
        $filePath = RECEIPT_IMAGE_DIR . "file/" . $fileName;
        $fileHash = $receiptFile->GetProperty("hash");

        ReceiptFile::WriteLog($receiptFileID, "--------", "info");
        ReceiptFile::WriteLog($receiptFileID, "starting mentana signature verification", "info");

        $client = self::GetSoapClient(self::$verificationServiceURL, $receiptFileID);
        if (!$client) {
            return false;
        }

        /*
        print_r($client);

        $functions = $client->__getFunctions();
        print_r($functions);

        $types = $client->__getTypes();
        print_r($types);
        */

        try {
            $fileStorage = GetFileStorage($container);
            $fileData = $fileStorage->GetFileContent($filePath);
            $signatureFileData = $fileStorage->GetFileContent($filePath . ".p7s");

            //create async verification task
            $requestVerificationParams = array(
                "VerificationRequest" => array(
                    "document" => $fileData,
                    "filename" => $fileName,
                    "signature" => $signatureFileData
                )
            );
            $requestVerificationResult = $client->requestVerification($requestVerificationParams);

            if (is_object($requestVerificationResult)) {
                if (
                    in_array(
                        $requestVerificationResult->return->status,
                        array(0, 21)
                    ) && $requestVerificationResult->return->taskid
                ) {
                    ReceiptFile::WriteLog(
                        $receiptFileID,
                        "verification is requested successfully. tskid: " . $requestVerificationResult->return->taskid,
                        "info"
                    );

                    for ($i = 0; $i < 10; $i++) {
                        ReceiptFile::WriteLog(
                            $receiptFileID,
                            "getting status of verification report creation task",
                            "info"
                        );

                        //get status of created verification task
                        $getStatusParams = array(
                            "RequestHandle" => array(
                                "status" => "",
                                "taskid" => $requestVerificationResult->return->taskid
                            )
                        );
                        $getStatusResult = $client->getStatus($getStatusParams);

                        if (is_object($getStatusResult) && $getStatusResult->return == 0) {
                            //verification task is completed successfully - get verification report
                            $getVerificationReportParams = array(
                                "RequestHandle" => array(
                                    "status" => "",
                                    "taskid" => $requestVerificationResult->return->taskid
                                ),
                                "pdfformat" => false
                            );
                            $getVerificationReportResult = $client->getVerificationReport($getVerificationReportParams);

                            if (is_object($getVerificationReportResult) && $getVerificationReportResult->return->resultcode == 0) {
                                $reportContent = $getVerificationReportResult->return->report;
                                $reportXml = simplexml_load_string($reportContent);
                                ReceiptFile::WriteLog(
                                    $receiptFileID,
                                    "verification report gained successfully",
                                    "info"
                                );
                                $fileStorage->PutFileContent(
                                    $filePath . ".xml",
                                    $getVerificationReportResult->return->report
                                );
                                if (!isset($reportXml->signatures->signature->status->checkresult) || !isset($reportXml->document->hash)) {
                                    ReceiptFile::WriteLog($receiptFileID, "verification report wrong format", "info");

                                    return "failed";
                                }

                                if ($reportXml->signatures->signature->status->checkresult != "SUCCESS") {
                                    ReceiptFile::WriteLog(
                                        $receiptFileID,
                                        "verification report status not success",
                                        "info"
                                    );

                                    $receipt = new Receipt("receipt");
                                    $receipt->LoadByID($receiptFile->GetProperty("receipt_id"));
                                    $receipt->IntegrityCheckDeny();

                                    return "failed";
                                }

                                if ($reportXml->document->hash != strtoupper($fileHash)) {
                                    ReceiptFile::WriteLog(
                                        $receiptFileID,
                                        "verification report hash not equals" . $fileHash,
                                        "info"
                                    );

                                    $receipt = new Receipt("receipt");
                                    $receipt->LoadByID($receiptFile->GetProperty("receipt_id"));
                                    $receipt->IntegrityCheckDeny();

                                    return "failed";
                                }
                                ReceiptFile::WriteLog($receiptFileID, "verification report status success", "info");

                                return "success";
                            }

                            if (is_object($getVerificationReportResult)) {
                                ReceiptFile::WriteLog(
                                    $receiptFileID,
                                    "incorrect response to getVerificationReport request",
                                    "error"
                                );
                            } else {
                                ReceiptFile::WriteLog(
                                    $receiptFileID,
                                    "cannot get verification report. code:" . $getVerificationReportResult->return->resultcode,
                                    "error"
                                );
                            }

                            break;
                        } elseif (is_object($getStatusResult) && $getStatusResult->return == 21) {
                            //ReceiptFile::WriteLog($receiptFileID, print_r($getStatusResult, true), "info");
                            //ReceiptFile::WriteLog($receiptFileID, print_r($requestSignatureResult, true), "info");
                            //verification task still in progress - wait and retry
                            ReceiptFile::WriteLog(
                                $receiptFileID,
                                "verification task is in progress. waiting for 5 seconds",
                                "info"
                            );
                            sleep(5);
                        } elseif (is_object($getStatusResult) && !in_array($getStatusResult->return, array(0, 21))) {
                            //verification task failed - exit with error
                            ReceiptFile::WriteLog(
                                $receiptFileID,
                                "verification task is completed unsuccessfully. code:" . $getStatusResult->return,
                                "error"
                            );
                            break;
                        } else {
                            //incorrect response from verification service - exit with error
                            ReceiptFile::WriteLog($receiptFileID, "incorrect response to getStatus request", "error");
                            break;
                        }
                    }

                    return false;
                }

                ReceiptFile::WriteLog(
                    $receiptFileID,
                    "cannot create requestVerification task. status:" . $requestVerificationResult->return->status,
                    "error"
                );

                return false;
            }

            ReceiptFile::WriteLog($receiptFileID, "incorrect response to requestVerification request", "error");

            return false;
        } catch (Exception $e) {
            ReceiptFile::WriteLog($receiptFileID, "exception: " . $e->getMessage(), "error");
            ReceiptFile::WriteLog($receiptFileID, "exception trace: " . $e->getTraceAsString(), "error");

            return false;
        }
    }

    private static function GetSoapClient($url, $receiptFileID)
    {
        ReceiptFile::WriteLog($receiptFileID, "getting soap client for url " . $url, "info");

        $options = array(
            "login" => self::$login,
            "password" => self::$password,
            "cache_wsdl" => WSDL_CACHE_NONE,
            "trace" => true,
            /*"stream_context" => stream_context_create(array(
                "ssl" => array(
                    "verify_peer" => false
                )
            ))*/
        );

        try {
            $client = new SoapClient($url, $options);
            ReceiptFile::WriteLog($receiptFileID, "soap client created", "info");

            return $client;
        } catch (Exception $e) {
            ReceiptFile::WriteLog($receiptFileID, "cannot get soap client: " . $e->getMessage(), "error");

            return null;
        }
    }
}
