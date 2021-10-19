<?php

es_include("file_storage_interface.php");

class FileStorageSwift extends LocalObject implements FileStorageInterface
{
    static $openstack;
    static $containerPrefix;
    private $container;

    public function GetOpenstack()
    {
        if (is_null(self::$openstack)) {
            //the first priority is to try to find openstack credentials in environment variables
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
                    "OS_AUTH_URL" => GetFromConfig("OS_AUTH_URL", "file_storage"),
                    "OS_USERNAME" => GetFromConfig("OS_USERNAME", "file_storage"),
                    "OS_PASSWORD" => GetFromConfig("OS_PASSWORD", "file_storage"),
                    "OS_USER_DOMAIN_ID" => GetFromConfig("OS_USER_DOMAIN_ID", "file_storage"),
                    "OS_PROJECT_ID" => GetFromConfig("OS_PROJECT_ID", "file_storage"),
                    "OS_REGION" => GetFromConfig("OS_REGION", "file_storage"),
                    "OS_CONTAINER" => GetFromConfig("OS_CONTAINER", "file_storage"),
                );
            }

            try {
                self::$openstack = new OpenStack\OpenStack([
                    "interface" => "public",
                    "authUrl" => $credentials["OS_AUTH_URL"],
                    "region" => $credentials["OS_REGION"],
                    "user" => [
                        "name" => $credentials["OS_USERNAME"],
                        "password" => $credentials["OS_PASSWORD"],
                        "domain" => ["id" => $credentials["OS_USER_DOMAIN_ID"]],
                    ],
                    "scope" => ["project" => ["id" => $credentials["OS_PROJECT_ID"]]],
                    "requestOptions" => [
                        "headers" => ["X-History-Location" => $credentials["OS_CONTAINER"] . "_archive"],
                    ],
                ]);
                self::$containerPrefix = $credentials["OS_CONTAINER"];
            } catch (Exception $e) {
            }
        }

        if (is_null(self::$openstack)) {
            $this->AddError("swift-cannot-be-initialized");
        }

        return self::$openstack;
    }

    public function GetContainerListInfo()
    {
        $serviceListInfo = array();
        $typesObjectsInfo = array();

        $typesObjects = [
            "error_log" => "/^var\/log\/error/",
            "reciept" => "/^website\/lst\/var\/receipt/",
            "api_receipt_log" => "/^website\/lst\/var\/receipt\/log/",
            "partner_report_xlsx" => "/^website\/lst\/var\/partner/",
            "recreation_confirmation" => "/^website\/lst\/var\/company\/payroll\/confirmation/",
            "company_apps_img" => "/^website\/lst\/var\/company\/apps/",
            "company_contract_documents" => "/^website\/lst\/var\/company\/contract/",
            "company_yearly_report" => "/^website\/lst\/var\/company\/report/",
            "invoice_pdf" => "/^website\/lst\/var\/billing\/invoice/",
            "invoice_export_csv" => "/^website\/lst\/var\/billing\/export_invoice/",
            "payroll_pdf" => "/^website\/lst\/var\/company\/payroll\/((?!confirmation))/",
            "payroll_LOGGA_csv" => "/LOGGA\.csv$/",
            "payroll_topas_csv" => "/topas\.csv$/",
            "payroll_Lodas_txt" => "/^website\/lst\/var\/company\/payroll\/Lodas/",
            "payroll_Lug_txt" => "/^website\/lst\/var\/company\/payroll\/Lug/",
            "payroll_imp_lbw_txt" => "/^website\/lst\/var\/company\/payroll\/Addison/",
            "payroll_Lex_txt" => "/^website\/lst\/var\/company\/payroll\/Lex/",
            "payroll_perforce_csv" => "/Perforce\.csv$/",
            "bookkeeping_export_pdf" => "/^website\/lst\/var\/company\/bookkeeping_export/",
            "voucher_export_csv" => "/^website\/lst\/var\/billing\/voucher_export/",
            "voucher_pdf" => "/^website\/lst\/var\/company\/voucher/",
            "agreements_pdf" => "/^website\/lst\/var\/agreements/",
            "stored_data_zip" => "/^website\/lst\/var\/company\/stored_data/",
            "master_data" => "/^website\/lst\/var\/billing\/master_data/",
            "product_img" => "/^website\/lst\/var\/product/",
            "config" => "/^website\/lst\/var\/config/",
            "mail" => "/^website\/lst\/var\/mail/",
            "user_img" => "/^website\/lst\/var\/user/",
        ];

        $openstack = self::GetOpenstack();

        if ($openstack) {
            $service = $openstack->objectStoreV1();
            $i = 0;
            foreach ($service->listContainers() as $container) {
                foreach ($typesObjects as $key => $value) {
                    $typesObjectsInfo[$key]['count'] = 0;
                    $typesObjectsInfo[$key]['size'] = 0;
                }

                $typesObjectsInfo['other']['count'] = 0;
                $typesObjectsInfo['other']['size'] = 0;

                $container->retrieve();
                $spaceUsed = self::translateByte($container->bytesUsed);

                $serviceListInfo[] = array(
                    "name" => $container->name,
                    "total_count" => $container->objectCount,
                    "total_size" => $spaceUsed,
                );

                $objects = iterator_to_array($container->listObjects());

                $isFound = false;
                foreach ($objects as $object) {
                    foreach ($typesObjects as $key => $value) {
                        if (!preg_match($value, $object->name)) {
                            continue;
                        }

                        $typesObjectsInfo[$key]['count']++;
                        $typesObjectsInfo[$key]['size'] += $object->contentLength;
                        $isFound = true;
                    }
                    if ($isFound) {
                        continue;
                    }

                    $typesObjectsInfo['other']['count']++;
                    $typesObjectsInfo['other']['size'] += $object->contentLength;
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

        $openstack = self::GetOpenstack();
        if (!$openstack) {
            $this->AddError("swift-cannot-be-initialized");

            return false;
        }

        /** @var \OpenStack\ObjectStore\v1\Service $service */
        $service = $openstack->objectStoreV1();

        /** @var \OpenStack\ObjectStore\v1\Models\Container $container */
        $container = $service->getContainer($this->GetContainer());

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

        if ($container->objectExists($swiftFileName)) {
            $this->AddError("swift-file-exists", array("FileName" => $swiftFileName, "FolderName" => $toDir));

            return false;
        }

        $options = array(
            "name" => $swiftFileName,
            "content" => file_get_contents($file["tmp_name"])
        );

        /** @var \OpenStack\ObjectStore\v1\Models\StorageObject $object */
        $object = $container->createObject($options);
        if ($container->objectExists($swiftFileName)) {
            return $file;
        }

        $this->AddError("swift-cannot-create-object");

        return false;
    }

    private function GenerateUniqueName($toDir, $fileName, $extension)
    {
        $fileSys = new FileSys();
        $result = $fileSys->RandStr(10) . "." . $extension;

        /** @var \OpenStack\ObjectStore\v1\Models\Container $container */
        $container = self::GetOpenstack()->objectStoreV1()->getContainer($this->GetContainer());
        while ($container->objectExists($toDir . $result)) {
            $result = $fileSys->RandStr(10) . "." . $extension;
        }

        return $result;
    }

    public function Remove($filePath)
    {
        $filePath = str_replace(PROJECT_DIR, "", $filePath);

        $openstack = self::GetOpenstack();
        if (!$openstack) {
            return;
        }

        /** @var \OpenStack\ObjectStore\v1\Models\Container $container */
        $container = $openstack->objectStoreV1()->getContainer($this->GetContainer());
        if (!$container->objectExists($filePath)) {
            return;
        }

        $container->getObject($filePath)->delete();
    }

    public function GetFileModificationTime($filePath)
    {
        $filePath = str_replace(PROJECT_DIR, "", $filePath);

        $openstack = self::GetOpenstack();
        if ($openstack) {
            /** @var \OpenStack\ObjectStore\v1\Models\Container $container */
            $container = $openstack->objectStoreV1()->getContainer($this->GetContainer());

            if ($container->objectExists($filePath)) {
                /** @var \OpenStack\ObjectStore\v1\Models\StorageObject $object */
                $object = $container->getObject($filePath);
                $object->retrieve();

                return strtotime($object->lastModified);
            }
        }

        return false;
    }

    public function FileExists($filePath)
    {
        $filePath = str_replace(PROJECT_DIR, "", $filePath);

        $openstack = self::GetOpenstack();
        if ($openstack) {
            /** @var \OpenStack\ObjectStore\v1\Models\Container $container */
            $container = $openstack->objectStoreV1()->getContainer($this->GetContainer());

            return $container->objectExists($filePath);
        }

        return false;
    }

    public function GetFileContent($filePath)
    {
        $filePath = str_replace(PROJECT_DIR, "", $filePath);
        $openstack = self::GetOpenstack();
        if ($openstack) {
            /** @var \OpenStack\ObjectStore\v1\Models\Container $container */
            $container = $openstack->objectStoreV1()->getContainer($this->GetContainer());
            if ($container->objectExists($filePath)) {
                /** @var \OpenStack\ObjectStore\v1\Models\StorageObject $object */
                $object = $container->getObject($filePath);
                $stream = $object->download();

                return $stream->getContents();
            }
        }

        return false;
    }

    public function PutFileContent($filePath, $content, $append = false)
    {
        $openstack = self::GetOpenstack();
        //$compute = $openstack->computeV2();
        //$limit = $compute->getLimits();
        //print_r($limit);
        if (!$openstack) {
            $this->AddError("openstack-error");

            return false;
        }

        if (!file_exists($filePath)) {
            touch($filePath);
        }
        $fp = fopen($filePath, "a");

        $l_count = 0;
        while (!flock($fp, LOCK_EX)) {
            if ($l_count == 10) {
                $this->AddError("openstack-cannot-lock");

                return false;
            }
            usleep(rand(0, 3000) * 1000);
            $l_count++;
        }
        $swiftPath = str_replace(PROJECT_DIR, "", $filePath);

        $container = $openstack->objectStoreV1()->getContainer($this->GetContainer());
        $data = "";

        try {
            if ($container->objectExists($swiftPath) && $append) {
                $data = $container->getObject($swiftPath)->download()->getContents();
            }
        } catch (\Exception $exc) {
            $this->AddError("openstack-bad-response");
            flock($fp, LOCK_UN);

            return false;
        }

        $options = array(
            "name" => $swiftPath,
            "content" => $data . $content
        );
        $container->createObject($options);

        flock($fp, LOCK_UN);

        return true;
    }

    public function PutFileContent2($filePath, $content, $append = false)
    {
        $openstack = self::GetOpenstack();
        //$compute = $openstack->computeV2();
        //$limit = $compute->getLimits();
        //print_r($limit);
        if (!$openstack) {
            $this->AddError("openstack-error");

            return false;
        }

        if (!file_exists($filePath)) {
            touch($filePath);
        }
        $fp = fopen($filePath, "a");

        $l_count = 0;
        /*while(!flock($fp, LOCK_EX)){
         if ($l_count == 10){
         $this->AddError("openstack-cannot-lock");
         return false;
         }
         //print_r(1);
         usleep(rand(0, 3000)*1000);
         $l_count++;
         }*/
        $swiftPath = str_replace(PROJECT_DIR, "", $filePath);

        $container = $openstack->objectStoreV1()->getContainer($this->GetContainer());
        $data = "";

        try {
            if ($container->objectExists($swiftPath) && $append) {
                $data = $container->getObject($swiftPath)->download()->getContents();
            }
        } catch (\Exception $exc) {
            $this->AddError("openstack-bad-response");

            //flock($fp, LOCK_UN);
            return false;
        }

        $options = array(
            "name" => $swiftPath,
            "content" => $data . $content
        );
        $container->createObject($options);

        //flock($fp, LOCK_UN);
        return true;
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

        $openstack = self::GetOpenstack();
        if (!$openstack) {
            $this->AddError("swift-cannot-be-initialized");

            return false;
        }

        /** @var \OpenStack\ObjectStore\v1\Service $service */
        $service = $openstack->objectStoreV1();

        /** @var \OpenStack\ObjectStore\v1\Models\Container $container */
        $container = $service->getContainer($this->GetContainer());

        if (gettype($rename) == "string") {
            $file_name = $rename;
        } elseif ($rename) {
            $file_name = $this->GenerateUniqueName($toDir, $file_name, $file_ext);
        }

        $swiftFileName = $toDir . $file_name;

        if ($container->objectExists($swiftFileName)) {
            $this->AddError("swift-file-exists", array("FileName" => $file_name, "FolderName" => $toDir));

            return false;
        }

        $options = array(
            "name" => $swiftFileName,
            "content" => file_get_contents($filePath)
        );

        /** @var \OpenStack\ObjectStore\v1\Models\StorageObject $object */
        $object = $container->createObject($options);
        try {
            $container->objectExists($swiftFileName);
        } catch (\Exception $e) {
            $this->AddError("swift-cannot-create-object");

            return false;
        }

        @unlink($filePath);

        return array(
            'name' => $file_name,
            'error' => 0
        );
    }

    public function CopyFile($source, $dest)
    {
        $openstack = self::GetOpenstack();
        $source = str_replace(PROJECT_DIR, "", $source);
        $dest = str_replace(PROJECT_DIR, "", $dest);
        if (!$openstack) {
            $this->AddError("openstack-error");

            return false;
        }
        $service = $openstack->objectStoreV1();
        $container = $service->getContainer($this->GetContainer());

        if (!$container->objectExists($source)) {
            $this->AddError("file-not-exists");

            return false;
        }
        $container->getObject($source)->copy(['destination' => "/" . $this->GetContainer() . "/" . $dest]);

        return $container->objectExists($dest);
    }

    public function Move($from, $to)
    {
        $from = str_replace(PROJECT_DIR, "", $from);
        $to = str_replace(PROJECT_DIR, "", $to);
        if ($from == $to) {
            return true;
        }

        $openstack = self::GetOpenstack();
        if (!$openstack) {
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
        $openstack = self::GetOpenstack();
        if (!$openstack) {
            $this->AddError("openstack-error");

            return false;
        }
        $filePath = str_replace(PROJECT_DIR, "", $filePath);
        $container = $openstack->objectStoreV1()->getContainer($this->GetContainer());
        if ($container->objectExists($filePath)) {
            $object = $container->getObject($filePath);
            $object->retrieve();

            return $object->contentLength;
        }

        return false;
    }

    public function SetContainer($container)
    {
        $this->container = $container;
    }

    public function GetContainer()
    {
        return self::$containerPrefix . $this->container;
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
