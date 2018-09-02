<?php
use Sabre\DAV\Client;

include 'vendor/autoload.php';

$settings = array(
	'baseUri' => 'https://tine.informatik.kit.edu/calendars/bd26cdeb8a7f9c836a00035e8cb1cdf7b41a13cf/109',
	'username' => 'meuerkö',
	'password' => 'Schwobbl110'
);

$client = new Client($settings);

$response = $client->request('MKsCALENDAR');

var_dump($response);
?>
