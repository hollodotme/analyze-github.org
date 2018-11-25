<?php declare(strict_types=1);

namespace hollodotme\GitHub\OrgAnalyzer;

use hollodotme\FastCGI\Client;
use hollodotme\FastCGI\Requests\GetRequest;
use hollodotme\FastCGI\Responses\Response;
use hollodotme\FastCGI\SocketConnections\NetworkSocket;
use hollodotme\GitHub\OrgAnalyzer\Application\Web\Responses\EventSourceStream;
use Throwable;
use function dirname;
use function http_build_query;
use function strpos;
use function substr;

require_once __DIR__ . '/../vendor/autoload.php';

$fastCgiSocket = new NetworkSocket( 'php', 9100 );
$fastCgiClient = new Client( $fastCgiSocket );

$accessToken      = trim( (string)$_GET['personalAccessToken'] );
$organizationName = trim( (string)$_GET['organizationName'] );

try
{
	$eventSourceStream = new EventSourceStream();
	$eventSourceStream->beginStream();

	if ( !preg_match( '#^\w+$#', $accessToken ) )
	{
		$eventSourceStream->streamEvent( 'Personal access token is not valid.', 'validationError' );
		$eventSourceStream->endStream();
		exit();
	}

	if ( !preg_match( '#^[a-z\d](?:[a-z\d]|-(?=[a-z\d])){0,38}$#i', $organizationName ) )
	{
		$eventSourceStream->streamEvent( 'GitHub organization name is not valid.', 'validationError' );
		$eventSourceStream->endStream();
		exit();
	}

	$queryVars = http_build_query( ['personalAccessToken' => $accessToken, 'organizationName' => $organizationName] );
	$filePath  = dirname( __DIR__ ) . '/cgi/index.php';
	$request   = new GetRequest( $filePath, '' );
	$request->setCustomVar( 'QUERY_STRING', $queryVars );

	$request->addPassThroughCallbacks(
		function ( string $buffer ) use ( $eventSourceStream )
		{
			$beginDataSet = strpos( $buffer, '[DS]' );
			$endDataSet   = strpos( $buffer, '[/DS]' );

			if ( false !== $beginDataSet && false !== $endDataSet )
			{
				$eventSourceStream->streamEvent( substr( $buffer, 0, $beginDataSet + 4 ) );
				$eventSourceStream->streamEvent( '', 'beginDataSet' );
				$eventSourceStream->streamEvent(
					substr( $buffer, $beginDataSet + 4, $endDataSet - ($beginDataSet + 4) )
				);
				$eventSourceStream->streamEvent( '', 'endDataSet' );
				$eventSourceStream->streamEvent( substr( $buffer, $endDataSet ) );

				return;
			}

			if ( false !== $beginDataSet )
			{
				$eventSourceStream->streamEvent( substr( $buffer, 0, $beginDataSet + 4 ) );
				$eventSourceStream->streamEvent( '', 'beginDataSet' );
				$eventSourceStream->streamEvent( substr( $buffer, $beginDataSet + 4 ) );

				return;
			}

			if ( false !== $endDataSet )
			{
				$eventSourceStream->streamEvent( substr( $buffer, 0, $endDataSet ) );
				$eventSourceStream->streamEvent( '', 'endDataSet' );
				$eventSourceStream->streamEvent( substr( $buffer, $endDataSet ) );

				return;
			}

			$eventSourceStream->streamEvent( $buffer );
		}
	);
	$request->addResponseCallbacks(
		function ( Response $response ) use ( $eventSourceStream )
		{
			$eventSourceStream->streamEvent( 'Duration: ' . $response->getDuration() );
			$eventSourceStream->endStream();
		}
	);

	$fastCgiClient->sendAsyncRequest( $request );
	$fastCgiClient->waitForResponses();
}
catch ( Throwable $e )
{
	echo "event: error\n";
	echo "data: {$e->getMessage()}\n";
}