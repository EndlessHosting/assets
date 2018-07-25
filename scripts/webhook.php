<?php 

$url = 'Discord Webhook URL';
$newstring = '**' . $_POST['subject'] . '**' . '\n' . $_POST['body']; # Some bullshittery
$myvars = 'content=' . $newstring;

$ch = curl_init( $url );
curl_setopt( $ch, CURLOPT_POST, 1);
curl_setopt( $ch, CURLOPT_POSTFIELDS, $myvars);
curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt( $ch, CURLOPT_HEADER, 0);
curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);
$response = curl_exec( $ch );
echo $response;
?>