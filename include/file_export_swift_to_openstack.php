<?php

require_once(dirname(__FILE__) . "/../include/file_storage_swift.php");

class FileExportOpenstack extends FileStorageSwift
{
    static $openstack;
    static $containerPrefix;

    public function GetRestoreOpenstack()
    {
        if (is_null(self::$openstack)) {
            //the first priority is to try to find openstack credentials in environment variables
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
                    "OS_AUTH_URL" => GetFromConfig("OS_AUTH_URL", "file_storage_openstack"),
                    "OS_USERNAME" => GetFromConfig("OS_USERNAME", "file_storage_openstack"),
                    "OS_PASSWORD" => GetFromConfig("OS_PASSWORD", "file_storage_openstack"),
                    "OS_USER_DOMAIN_ID" => GetFromConfig("OS_USER_DOMAIN_ID", "file_storage_openstack"),
                    "OS_PROJECT_ID" => GetFromConfig("OS_PROJECT_ID", "file_storage_openstack"),
                    "OS_REGION" => GetFromConfig("OS_REGION", "file_storage_openstack"),
                    "OS_CONTAINER" => GetFromConfig("OS_CONTAINER", "file_storage_openstack"),
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
                        "headers" => ["X-History-Location" => $credentials["OS_CONTAINER"]],
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

    public function GetObjectsFromContainer()
    {
        $openstack = self::GetOpenstack();
        $restoreOpenstack = self::GetRestoreOpenstack();

        if (!$openstack) {
            $this->AddError('Failed connect to BackupsOfSwift');

            return false;
        }

        if (!$restoreOpenstack) {
            $this->AddError('Failed connect to lst_restore');

            return false;
        }

        $service = $openstack->objectStoreV1();
        $container = $service->getContainer('lst_demo');
        $container->retrieve();
        $objects = iterator_to_array($container->listObjects());
        foreach ($objects as $object) {
            if (!$container->objectExists($object->name)) {
                continue;
            }

            $data = $container->getObject($object->name)->download()->getContents();
            $this->RestoreUpload($object->name, $data, $restoreOpenstack);
        }

        return true;
    }

    public function RestoreUpload($name, $content, $restoreOpenstack)
    {
        $service = $restoreOpenstack->objectStoreV1();
        $container = $service->getContainer('trebono-demo');
        $container->retrieve();
        if ($container->objectExists($name)) {
            /*$this->AddError("swift-file-exists", array("FileName" => $name, "FolderName" => $name));
            return false;*/
            return true;
        }

        $options = array(
            "name" => $name,
            "content" => $content
        );
        $object = $container->createObject($options);
        if ($container->objectExists($name)) {
            return $object;
        }

        $this->AddError("swift-cannot-create-object");

        return false;
    }

    public function CheckFiles()
    {
        $restoreOpenstack = self::GetRestoreOpenstack();

        $service = $restoreOpenstack->objectStoreV1();
        $container = $service->getContainer('trebono-demo');
        $container->retrieve();
        $objects = iterator_to_array($container->listObjects());

        var_dump($objects);

        return $objects;
    }
}
