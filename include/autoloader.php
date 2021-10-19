<?php

class Autoloader
{
    private static $fullIncludeDirList;
    private static $exceptionMap;

    public static function Load($className)
    {
        $exceptionMap = self::GetExceptionMap();
        if (isset($exceptionMap[$className])) {
            $fileNameList = array($exceptionMap[$className]);
        } else {
            /**
             * Class name to file name examples:
             * ReceiptList -> receipt_list.php, receiptlist.php
             * FCMManager -> fcm_manager.php, fcmmanager.php
             */
            $patternList = array(
                "/([^A-Z]+)([A-Z])/U",
                "/([A-Z])([A-Z][^A-Z])/U",
            );
            $fileNameList = array(
                strtolower(preg_replace($patternList, '$1_$2', $className)) . ".php",
                strtolower($className) . ".php"
            );
        }

        $fullDirList = self::GetFullIncludeDirList();
        foreach ($fullDirList as $dir) {
            foreach ($fileNameList as $fileName) {
                $filePath = $dir . "/" . $fileName;
                if (!file_exists($filePath)) {
                    continue;
                }

                require_once $filePath;
                if (class_exists($className)) {
                    return true;
                }
            }
        }

        return false;
    }

    private static function GetFullIncludeDirList()
    {
        if (self::$fullIncludeDirList === null) {
            self::$fullIncludeDirList = array();

            $includeDirList = array();

            $moduleDirList = self::GetDirectoryListRecursively(PROJECT_DIR . "module", 1);
            foreach ($moduleDirList as $moduleDir) {
                $includeDirList[] = $moduleDir . "/include/";
            }

            $includeDirList[] = PROJECT_DIR . "include/";

            foreach ($includeDirList as $includeDir) {
                self::$fullIncludeDirList = array_merge(
                    self::$fullIncludeDirList,
                    self::GetDirectoryListRecursively($includeDir, 2)
                );
            }
        }

        return self::$fullIncludeDirList;
    }

    private static function GetExceptionMap()
    {
        if (self::$exceptionMap === null) {
            self::$exceptionMap = array(
                "AdminPage" => "localpage.php",
                "PublicPage" => "localpage.php",
                "PopupPage" => "localpage.php"
            );
        }

        return self::$exceptionMap;
    }

    private static function GetDirectoryListRecursively($dir, $maxDepth, $depth = 0, &$results = array())
    {
        if ($depth <= $maxDepth) {
            if (is_dir($dir)) {
                $files = scandir($dir);

                foreach ($files as $key => $value) {
                    $path = realpath($dir . "/" . $value);
                    if (!is_dir($path) || $value == "." || $value == "..") {
                        continue;
                    }

                    self::GetDirectoryListRecursively($path, $maxDepth, $depth + 1, $results);
                }
                $results[] = $dir;
                sort($results, SORT_STRING);
            }
        }

        return $results;
    }
}
