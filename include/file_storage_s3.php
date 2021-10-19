<?php

es_include("file_storage_interface.php");


class FileStorageS3 extends LocalObject implements FileStorageInterface
{
    static $s3;
    static $containerPrefix;
    private $container;
    private $bucket;

    private function GetS3()
    {
        if (is_null(self::$s3)) {
            //the first priority is to try to find s3 credentials in environment variables
            $vcapServices = json_decode(getenv("VCAP_SERVICES"), true);
            if ($vcapServices) {
                if (isset($vcapServices["user-provided"])) {
                    foreach ($vcapServices["user-provided"] as $userProvidedService) {
                        if ($userProvidedService["name"] != "swift") {
                            continue;
                        }

                        $credentials = $userProvidedService["credentials"];
                    }
                }
            }

            //the second priority is to get credentials from config if project is local or located not in meshcloud
            if (!isset($credentials)) {
                $credentials = array(
                    "key" => GetFromConfig("aws_access_key_id", "file_storage_s3"),
                    "secret" => GetFromConfig("aws_secret_access_key", "file_storage_s3")
                );
            }

            try {
                self::$s3 = new Aws\S3\S3Client([
                    'version' => GetFromConfig("aws_version", "file_storage_s3"),
                    'region' => GetFromConfig("aws_region", "file_storage_s3"),
                    'endpoint' => GetFromConfig("aws_endpoint", "file_storage_s3"),
                    'use_path_style_endpoint' => true,
                    'credentials' => $credentials,
                    'debug' => true,
                ]);
                self::$containerPrefix = GetFromConfig("aws_container_prefix", "file_storage_s3");
                //var_dump(self::$containerPrefix); exit();

                //self::$containerPrefix = $credentials["OS_CONTAINER"];
            } catch (Exception $e) {
                echo "trouble";
                /*var_dump($e);
                die;*/
            }

            /*$bucket = 'trebono';
            $file_Path = PROJECT_DIR."test_2.png";
            $key = "test/".basename($file_Path);
            try {
                $result = self::$s3->putObject([
                    'Bucket' => $bucket,
                    'Key' => $key,
                    'SourceFile' => $file_Path,
                ]);
                $result = self::$s3->doesObjectExist($bucket, $key);
                var_dump($result);


            } catch (Aws\Exception\AwsException $e) {
                echo "trouble2";
                echo $e->getMessage() . "\n";
            }
            die;*/
        }

        if (is_null(self::$s3)) {
            $this->AddError("swift-cannot-be-initialized");
        }

        return self::$s3;
    }

    public function GetContainerListInfo()
    {
        $serviceListInfo = array();
        $typesObjectsInfo = array();

        $typesObjects = [
            "error_log" => "/^var\/log\/error/",
            "config" => "/^website\/lst\/var\/config/",
            "reciept" => "/^website\/lst\/var\/receipt/",
            "voucher_pdf" => "/^website\/lst\/var\/company\/voucher/",
            "agreements_pdf" => "/^website\/lst\/var\/agreements/",
            "invoice_pdf" => "/^website\/lst\/var\/billing\/invoice/",
            "payroll_bookkeeping_csv" => "/bookkeeping\.csv$/",
            "payroll_LOGGA_csv" => "/LOGGA\.csv$/",
            "payroll_pdf" => "/^website\/lst\/var\/company\/payroll/",
            "payroll_topas_csv" => "/topas\.csv$/",
            "payroll_Lodas_txt" => "/^website\/lst\/var\/company\/payroll\/Lodas/",
            "payroll_Lug_txt" => "/^website\/lst\/var\/company\/payroll\/Lug/",
            "mail" => "/^website\/lst\/var\/mail/",
            "partner_report_xlsx" => "/^website\/lst\/var\/partner/",
            "product_img" => "/^website\/lst\/var\/product/",
            "company_apps_img" => "/^website\/lst\/var\/company\/apps/",
            "user_img" => "/^website\/lst\/var\/user/",
        ];

        $s3 = self::GetS3();

        if ($s3) {
            $buckets = $s3->listBuckets([]);
            $i = 0;
            foreach ($buckets["Buckets"] as $bucket) {
                foreach ($typesObjects as $key => $value) {
                    $typesObjectsInfo[$key]['count'] = 0;
                    $typesObjectsInfo[$key]['size'] = 0;
                }

                $typesObjectsInfo['other']['count'] = 0;
                $typesObjectsInfo['other']['size'] = 0;

                $listObjects = $s3->listObjectsV2([
                    'Bucket' => $bucket['Name'],
                ]);

                $bytesUsed = 0;
                foreach ($listObjects["Contents"] as $key => $object) {
                    $bytesUsed += $object['Size'];
                }

                $serviceListInfo[] = array(
                    "name" => $bucket['Name'],
                    "total_count" => $listObjects["KeyCount"],
                    "total_size" => self::translateByte($bytesUsed),
                );

                $isFound = false;
                foreach ($listObjects["Contents"] as $object) {
                    foreach ($typesObjects as $key => $value) {
                        if (!preg_match($value, $object['Key'])) {
                            continue;
                        }

                        $typesObjectsInfo[$key]['count']++;
                        $typesObjectsInfo[$key]['size'] += $object['Size'];
                        $isFound = true;
                    }
                    if ($isFound) {
                        continue;
                    }

                    $typesObjectsInfo['other']['count']++;
                    $typesObjectsInfo['other']['size'] += $object['Size'];
                    $isFound = false;
                }

                foreach ($typesObjects as $key => $value) {
                    $size = self::translateByte($typesObjectsInfo[$key]['size']);

                    $serviceListInfo[$i]['count_and_size'][] = array(
                        "types_object" => GetTranslation($key),
                        "count" => $typesObjectsInfo[$key]['count'],
                        "size" => $size,
                    );
                }

                $serviceListInfo[$i]['count_and_size'][] = array(
                    "types_object" => GetTranslation('other'),
                    "count" => $typesObjectsInfo['other']['count'],
                    "size" => self::translateByte($typesObjectsInfo['other']['size'])
                );
                $i++;
            }
            var_dump($serviceListInfo);
        }

        return $serviceListInfo;
    }

    public static function translateByte($sizeInByte)
    {

        $kb = $sizeInByte / 1024;
        $mb = $kb / 1024;
        $gb = $mb / 1024;

        if ($gb < 1 && $mb < 1 && $kb < 1) {
            $size = round($sizeInByte, 2) . " byte";
        } elseif ($gb < 1 && $mb < 1) {
            $size = round($kb, 2) . " KB";
        } elseif ($gb < 1) {
            $size = round($mb, 2) . " MB";
        } else {
            $size = round($gb, 2) . " GB";
        }

        return $size;
    }

    public function PutFileContent($filePath, $content, $append = false)
    {
        $s3 = self::$s3;

        if (!$s3) {
            $this->AddError("openstack-error");

            return false;
        }

        if (!file_exists($filePath)) {
            touch($filePath);
        }

        $bucket = $this->GetContainer();
        $swiftPath = str_replace(PROJECT_DIR, "", $filePath);
        $key = basename($swiftPath);
        $data = "";

        try {
            if ($s3->doesObjectExist($bucket, $key) && $append) {
                $result = $s3->getObject(array(
                    'Bucket' => $bucket,
                    'Key' => $key
                ));
                $data = (string)$result['Body'];
            }
        } catch (\Exception $exc) {
            $this->AddError("openstack-bad-response");

            return false;
        }

        $s3->putObject([
            'Bucket' => $bucket,
            'Key' => $key,
            'Body' => $data . $content,
        ]);

        return true;
    }

    public function GetFileContent($filePath)
    {
        $filePath = str_replace(PROJECT_DIR, "", $filePath);
        $s3 = self::GetS3();
        if ($s3) {
            $bucket = $this->GetContainer();
            $key = basename($filePath);
            $result = $s3->getObject(array(
                'Bucket' => $bucket,
                'Key' => $key
            ));

            // Cast as a string
            return (string)$result['Body'];
        }

        return false;
    }

    public function Upload(
        $paramName,
        $toDir,
        $saveOriginalFileName = false,
        $acceptMimeTypes = array("image/png", "image/x-png", "image/gif", "image/jpeg", "image/pjpeg")
    ) {
        if (!isset($_FILES[$paramName])) {
            return false;
        }

        //no multiple file uploading support yet
        if (is_array($_FILES[$paramName]["name"])) {
            $this->AddError("swift-no-multiple-file-uploading-support");

            return false;
        }

        $file = $_FILES[$paramName];

        if (!file_exists($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return false;
        }

        if ($file["error"] > 0 && $file["error"] != 4) {
            $this->AddError("swift-file-upload-error", array("ErrorNumber" => $file["error"]));
        }

        if (!preg_match("/\.([^.]*?)$/i", $file["name"], $extension)) {
            $file["ErrorInfo"] = GetTranslation("swift-incorrect-file-name", array("FileName" => $file["name"]));
        }

        if (!empty($acceptMimeTypes) && !in_array($file["type"], $acceptMimeTypes)) {
            $file["ErrorInfo"] = GetTranslation("swift-unsupported-file-mime-type", array("MimeType" => $file["type"]));
        }

        if ($this->HasErrors()) {
            return false;
        }

        //change full path to relative for swift pseudo folder support
        $toDir = str_replace(PROJECT_DIR, "", $toDir);

        $s3 = self::GetS3();
        if (!$s3) {
            $this->AddError("swift-cannot-be-initialized");

            return false;
        }

        if ($saveOriginalFileName) {
            $file["FileName"] = $file["name"];
        } else {
            $file["FileName"] = $this->GenerateUniqueName(
                $toDir,
                $file["name"],
                $extension[1],
                $saveOriginalFileName
            );
        }

        $swiftFileName = $toDir . $file["FileName"];

        $bucket = $this->GetContainer();
        $key = basename($swiftFileName);

        if ($s3->doesObjectExist($bucket, $key)) {
            $this->AddError("swift-file-exists", array("FileName" => $swiftFileName, "FolderName" => $toDir));

            return false;
        }

        $s3->putObject([
            'Bucket' => $bucket,
            'Key' => $key,
            'SourceFile' => $swiftFileName,
        ]);

        if ($s3->doesObjectExist($bucket, $key)) {
            return $file;
        }

        $this->AddError("swift-cannot-create-object");

        return false;
    }

    private function GenerateUniqueName($toDir, $fileName, $extension)
    {
        $fileSys = new FileSys();
        $result = $fileSys->RandStr(10) . "." . $extension;
        $key = basename($fileName);

        $s3 = self::GetS3();
        $bucket = $this->GetContainer();
        while ($s3->doesObjectExist($bucket, $key)) {
            $result = $fileSys->RandStr(10) . "." . $extension;
        }

        return $result;
    }

    public function Remove($filePath)
    {
        $filePath = str_replace(PROJECT_DIR, "", $filePath);

        $s3 = self::GetS3();
        if (!$s3) {
            return;
        }

        $bucket = $this->GetContainer();
        $key = basename($filePath);

        if (!$s3->doesObjectExist($bucket, $key)) {
            return;
        }

        $s3->deleteObject([
            'Bucket' => $bucket,
            'Key' => $key,
        ]);
    }

    public function GetFileModificationTime($filePath)
    {
        $filePath = str_replace(PROJECT_DIR, "", $filePath);

        $s3 = self::GetS3();
        if ($s3) {
            $bucket = $this->GetContainer();
            $key = basename($filePath);

            if ($s3->doesObjectExist($bucket, $key)) {
                $result = $s3->getObject([
                    'Bucket' => $bucket,
                    'Key' => $key,
                ]);

                return strtotime($result['LastModified']->date);
            }
        }

        return false;
    }

    public function FileExists($filePath)
    {

        $filePath = str_replace(PROJECT_DIR, "", $filePath);

        $s3 = self::GetS3();
        if ($s3) {
            $bucket = $this->GetContainer();
            $key = basename($filePath);

            return $s3->doesObjectExist($bucket, $key);
        }

        return false;
    }


    public function MoveToStorage($filePath, $toDir, $rename = false)
    {

        if (!file_exists($filePath)) {
            return false;
        }
        $file_name = explode("/", $filePath);
        $file_name = $file_name[count($file_name) - 1];
        $file_ext = explode(".", $file_name);
        $file_ext = count($file_ext) > 1 ? $file_ext[count($file_ext) - 1] : "";

        //change full path to relative for swift pseudo folder support
        $toDir = str_replace(PROJECT_DIR, "", $toDir);

        $s3 = self::GetS3();
        if (!$s3) {
            $this->AddError("swift-cannot-be-initialized");

            return false;
        }

        if (gettype($rename) == "string") {
            $file_name = $rename;
        } elseif ($rename) {
            $file_name = $this->GenerateUniqueName($toDir, $file_name, $file_ext);
        }

        $bucket = $this->GetContainer();
        $filePath = $toDir . $file_name;
        $key = basename($filePath);

        if ($s3->doesObjectExist($bucket, $key)) {
            $this->AddError("swift-file-exists", array("FileName" => $file_name, "FolderName" => $toDir));

            return false;
        }

        try {
            $result = self::$s3->putObject([
                'Bucket' => $bucket,
                'Key' => $key,
                'SourceFile' => $filePath,
            ]);
            $result = self::$s3->doesObjectExist($bucket, $key);
        } catch (Aws\Exception\AwsException $e) {
            echo "trouble2";
            echo $e->getMessage() . "\n";
        }

        @unlink($filePath);

        return array(
            'name' => $file_name,
            'error' => 0
        );
    }

    public function CopyFile($source, $dest)
    {
        /*$openstack = self::GetOpenstack();
        $source = str_replace(PROJECT_DIR, "", $source);
        $dest = str_replace(PROJECT_DIR, "", $dest);
        if (!$openstack) {
            $this->AddError("openstack-error");
            return false;
        }
        $service = $openstack->objectStoreV1();
        $container = $service->getContainer($this->GetContainer());

        if (!$container->objectExists($source)){
            $this->AddError("file-not-exists");
            return false;
        }
        $container->getObject($source)->copy(['destination' => "/".$this->GetContainer()."/".$dest]);
        return $container->objectExists($dest);*/

        $s3 = self::GetS3();

        if (!$s3) {
            return;
        }

        $sourceBucket = $this->GetContainer();
        $sourceKeyname = $source;
        $targetKeyname = $dest;

        // Copy an object.
        $s3->copyObject([
            'Bucket' => $sourceBucket,
            'Key' => $targetKeyname,
            'CopySource' => "{$sourceBucket}/{$sourceKeyname}",
        ]);
    }

    public function Move($from, $to)
    {
        $from = str_replace(PROJECT_DIR, "", $from);
        $to = str_replace(PROJECT_DIR, "", $to);
        if ($from == $to) {
            return true;
        }

        $s3 = self::GetS3();
        if (!$s3) {
            $this->AddError("swift-cannot-be-initialized");

            return false;
        }

        $result = $this->CopyFile($from, $to);
        if ($result) {
            $this->Remove($from);
        }

        return $result;
    }

    public function GetContentLength($filePath)
    {
        $s3 = self::GetS3();
        if (!$s3) {
            $this->AddError("openstack-error");

            return false;
        }
        $filePath = str_replace(PROJECT_DIR, "", $filePath);

        $bucket = $this->GetContainer();
        $key = basename($filePath);
        if ($s3->doesObjectExist($bucket, $key)) {
            $object = $s3->getObject([
                'Bucket' => $bucket,
                'Key' => $key,
            ]);

            return $object['ContentLength'];
        }

        return false;
    }

    public function SetContainer($container)
    {
        $this->container = $container;
    }

    public function GetContainer()
    {
        return self::$containerPrefix . '-' . $this->container;
    }

    public function MoveBetweenContainers($from, $to, $filePath)
    {
        if ($from == $to) {
            return true;
        }

        $this->SetContainer($from);
        if ($this->FileExists($filePath) && $content = $this->GetFileContent($filePath)) {
            $this->SetContainer($to);
            if ($this->PutFileContent($filePath, $content)) {
                $this->SetContainer($from);
                $this->Remove($filePath);

                return true;
            }
        }

        return false;
    }
}
