<?php declare(strict_types=1);

$accessToken = (string)($argv[1] ?? $_GET['personalAccessToken']);
$resultType  = (string)($argv[2] ?? $_GET['resultType']);

$filePath = sprintf( '%s/results/%s/%s.json', dirname( __DIR__ ), $resultType, $accessToken );

if ( !file_exists( $filePath ) )
{
	header( 'Content-Type: application/json; charset=utf-8', true, 404 );
	echo '{message: "Result file not found"}';
	exit();
}

header( 'Content-Type: application/json; charset=utf-8', true, 200 );
$handle = fopen( $filePath, 'rb' );
fpassthru( $handle );
fclose( $handle );

@unlink( $filePath );