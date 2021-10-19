<?php
$headerContentType = 'Content-Type: multipart/form-data';
$base_url = "https://lstocr.cf.eu-de-darz.msh.host/recognize/advanced-bin";

$cfile = makeCurlFile("test_3.jpg");
$post = array('file_image' => $cfile);

$ch = curl_init();
curl_setopt($ch, CURLOPT_HTTPHEADER, array($headerContentType));
curl_setopt($ch, CURLOPT_USERPWD, "admin" . ":" . "123456");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
curl_setopt($ch, CURLOPT_URL, $base_url);
curl_setopt($ch, CURLOPT_FAILONERROR, true);

$output = curl_exec($ch);

if ($output === false) {
    echo 'Ошибка curl: ' . curl_error($ch) . '<br/>';
} else {
    $output = json_decode($output, true);
    print_r($output);

    $info = curl_getinfo($ch);
    print_r($info);
}

curl_close($ch);

function makeCurlFile($file)
{
    $mime = mime_content_type($file);
    $info = pathinfo($file);
    $name = $info['basename'];
    $output = new CURLFile($file, $mime, $name);
    return $output;
}

?>