<?php declare(strict_types=1);

namespace hollodotme\GitHub\OrgAnalyzer;

use hollodotme\FastCGI\Client;
use hollodotme\FastCGI\Requests\GetRequest;
use hollodotme\FastCGI\Responses\Response;
use hollodotme\FastCGI\SocketConnections\NetworkSocket;
use hollodotme\GitHub\OrgAnalyzer\Application\Web\Responses\EventSourceStream;
use Throwable;
use function basename;
use function dirname;
use function error_reporting;
use function http_build_query;
use function ignore_user_abort;
use function ini_set;
use function preg_match;
use const PHP_SAPI;

error_reporting( E_ALL );
ini_set( 'display_errors', 'On' );
ignore_user_abort( false );

require_once __DIR__ . '/../vendor/autoload.php';

$fastCgiSocket = new NetworkSocket( 'php', 9100 );
$fastCgiClient = new Client( $fastCgiSocket );

if ( 'cli' === PHP_SAPI )
{
	$accessToken      = trim( (string)$argv[1] );
	$organizationName = trim( (string)$argv[2] );
	$useCommitDate    = (bool)($argv[3] ?? false);
}
else
{
	$accessToken      = trim( (string)$_GET['personalAccessToken'] );
	$organizationName = trim( (string)$_GET['organizationName'] );
	$useCommitDate    = (bool)($_GET['useCommitDate'] ?? false);
}

try
{
	$eventSourceStream = new EventSourceStream();
	$eventSourceStream->beginStream();

	if ( !preg_match( '#^\w+$#', $accessToken ) )
	{
		$eventSourceStream->streamEvent( 'Personal access token is not valid.', 'error' );
		$eventSourceStream->endStream();
		exit();
	}

	if ( !preg_match( '#^[a-z\d](?:[a-z\d]|-(?=[a-z\d])){0,38}$#i', $organizationName ) )
	{
		$eventSourceStream->streamEvent( 'GitHub organization name is not valid.', 'error' );
		$eventSourceStream->endStream();
		exit();
	}

	$queryVars = http_build_query(
		[
			'personalAccessToken' => $accessToken,
			'organizationName'    => $organizationName,
			'useCommitDate'       => $useCommitDate,
		]
	);
	$filePath  = dirname( __DIR__ ) . '/cgi/repositories.php';
	$request   = new GetRequest( $filePath, '' );
	$request->setCustomVar( 'QUERY_STRING', $queryVars );

	$request->addPassThroughCallbacks(
		function ( string $buffer ) use ( $eventSourceStream )
		{
			$buffer = trim( $buffer );
			if ( '' === $buffer )
			{
				return;
			}

			$matches = [];
			if ( preg_match( "#^RESULT: (.+\.json)$#", $buffer, $matches ) )
			{
				$eventSourceStream->streamEvent( basename( $matches[1] ), 'jsonResult' );

				return;
			}

			$matches = [];
			if ( preg_match( '#^DEBUG: (.+)#', $buffer, $matches ) )
			{
				$eventSourceStream->streamEvent( basename( $matches[1] ), 'debug' );

				return;
			}

			$matches = [];
			if ( preg_match( '#^ERROR: (.+)#', $buffer, $matches ) )
			{
				$eventSourceStream->streamEvent( basename( $matches[1] ), 'error' );

				return;
			}

			$eventSourceStream->streamEvent( $buffer );
		}
	);
	$request->addResponseCallbacks(
		function ( Response $response ) use ( $eventSourceStream )
		{
			$eventSourceStream->streamEvent( 'Duration: ' . $response->getDuration(), 'debug' );
			$eventSourceStream->endStream();
		}
	);

	$fastCgiClient->sendAsyncRequest( $request );

	while ( $fastCgiClient->hasUnhandledResponses() )
	{
		$eventSourceStream->streamEvent( '[KEEPALIVE]' );
		$fastCgiClient->handleReadyResponses();
	}
}
catch ( Throwable $e )
{
	echo "event: error\n";
	echo "data: {$e->getMessage()}\n";
}