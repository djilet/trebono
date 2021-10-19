<?php

require_once(dirname(__FILE__) . "/../include/file_storage_swift.php");

class FileExportSwift extends FileStorageSwift
{
    static $s3;
    static $containerPrefix;

    private function GetS3()
    {
        if (is_null(self::$s3)) {
            //the first priority is to try to find s3 credentials in environment variables
            $vcapServices = json_decode(getenv("VCAP_SERVICES"), true);
            if ($vcapServices) {
                if (isset($vcapServices["user-provided"])) {
                    foreach ($vcapServices["user-provided"] as $userProvidedService) {
                        if ($userProvidedService["name"] != "backup") {
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
                    'debug' => false,
                ]);
                self::$containerPrefix = GetFromConfig("aws_container_prefix", "file_storage_s3");

                //var_dump(self::$s3);

                //self::$containerPrefix = $credentials["OS_CONTAINER"];
            } catch (Exception $e) {
                echo "trouble";
            }
        }

        if (is_null(self::$s3)) {
            $this->AddError("swift-cannot-be-initialized");
        }

        return self::$s3;
    }

    public function GetObjectsFromContainer()
    {
        $openstack = self::GetOpenstack();
        $s3 = self::GetS3();

        $typesObjects = [
            "log" => [
                "/^var\/log\/error/",
                "/^website\/lst\/var\/mail/",
            ],
            "documents" => [
                "/^website\/lst\/var\/config/",
                "/^website\/lst\/var\/company\/voucher/",
                "/^website\/lst\/var\/agreements/",
                "/^website\/lst\/var\/billing\/invoice/",
                "/bookkeeping\.csv$/",
                "/LOGGA\.csv$/",
                "/^website\/lst\/var\/company\/payroll/",
                "/topas\.csv$/",
                "/^website\/lst\/var\/company\/payroll\/Lodas/",
                "/^website\/lst\/var\/company\/payroll\/Lug/",
                "/^website\/lst\/var\/partner/",
                "/^website\/lst\/var\/product/",
                "/^website\/lst\/var\/company\/apps/",
                "/^website\/lst\/var\/user/",
            ],
            "reciept" => [
                "/^website\/lst\/var\/receipt/",
            ],
        ];

        if (!$openstack) {
            $this->AddError('Failed connect to BackupsOfSwift');

            return false;
        }

        if (!$s3) {
            $this->AddError('Failed connect to s3');

            return false;
        }

        $service = $openstack->objectStoreV1();
        $container = $service->getContainer('BackupsOfSwift');
        $container->retrieve();
        $objects = iterator_to_array($container->listObjects());
        foreach ($objects as $object) {
            if ($container->objectExists($object->name)) {
                //set bucket
                $bucket = 'trebono';
                foreach ($typesObjects as $key => $values) {
                    foreach ($values as $value) {
                        if (!preg_match($value, $object->name)) {
                            continue;
                        }

                        $bucket = self::$containerPrefix . '-' . $key;
                    }
                }
                $data = $container->getObject($object->name)->download()->getContents();
                $this->RestoreUpload($object->name, $data, $bucket, $s3);
            }
            continue;
        }

        return true;
    }

    public function RestoreUpload($name, $content, $bucket, $s3)
    {
        if ($s3->doesObjectExist($bucket, $name)) {
            $this->AddError("swift-file-exists", array("FileName" => $name, "FolderName" => $name));

            return false;
        }
        try {
            $result = self::$s3->putObject([
                'Bucket' => $bucket,
                'Key' => $name,
                'Body' => $content,
            ]);
            $result = self::$s3->doesObjectExist($bucket, $name);
            var_dump($result);
        } catch (Aws\Exception\AwsException $e) {
            echo "trouble2";
            echo $e->getMessage() . "\n";
        }
        if ($s3->doesObjectExist($bucket, $name)) {
            return $result;
        }

        $this->AddError("swift-cannot-create-object");

        return false;
    }

    public function CheckFiles()
    {
        $s3 = self::GetS3();

        $bucket = 'trebono';
        $listObjects = $s3->listObjectsV2([
            'Bucket' => $bucket,
        ]);
        var_dump($listObjects);

        return $listObjects;
    }
}
