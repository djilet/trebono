<?php

interface FileStorageInterface
{

    public function Upload(
        $paramName,
        $toDir,
        $saveOriginalFileName = false,
        $acceptMimeTypes = array(
            'image/png',
            'image/x-png',
            'image/gif',
            'image/jpeg',
            'image/pjpeg',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-excel'
        )
    );

    public function Remove($filePath);

    public function GetFileModificationTime($filePath);

    public function FileExists($filePath);

    public function GetFileContent($filePath);

    public function MoveToStorage($filePath, $toDir, $rename);

    public function PutFileContent($filePath, $content, $append = false);

    public function CopyFile($source, $dest);

    public function Move($from, $to);

    function HasErrors();

    function GetErrorsAsString($separator = ",");

    function GetContentLength($filePath);

    public function MoveBetweenContainers($from, $to, $filePath);
}

