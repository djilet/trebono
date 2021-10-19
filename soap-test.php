<?php
$options = array(
    'login' => "serviceuser",
    'password' => "9pWBHtS52aLjoXbAHLcz",
);

try {
    $client = new SoapClient("https://signatur.trebono.de:8443/as-soap/SignService?wsdl", $options);
} catch (Exception $e) {
    echo $e->getMessage();
}
echo "Client for signatur.trebono.de: ";
var_dump($client);

echo "<br /><br />";

try {
    $client = new SoapClient("https://verify.trebono.de:8443/av-soap/VerificationService?wsdl", $options);
} catch (Exception $e) {
    echo $e->getMessage();
}
echo "Client for verify.trebono.de: ";
var_dump($client);