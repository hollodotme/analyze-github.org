<?php declare(strict_types=1);

namespace hollodotme\GitHub\OrgAnalyzer\Cgi;

use DateTimeImmutable;
use hollodotme\FastCGI\Client;
use hollodotme\FastCGI\Interfaces\ProvidesResponseData;
use hollodotme\FastCGI\Requests\GetRequest;
use hollodotme\FastCGI\SocketConnections\NetworkSocket;
use hollodotme\GitHub\OrgAnalyzer\Application\CGI\Responses\OutputStream;
use hollodotme\GitHub\OrgAnalyzer\Application\Configs\OrgConfig;
use hollodotme\GitHub\OrgAnalyzer\Application\Repositories\GitHubRepository;
use hollodotme\GitHub\OrgAnalyzer\Infrastructure\Adapters\GitHub\GitHubAdapter;
use hollodotme\GitHub\OrgAnalyzer\Infrastructure\Adapters\Http\HttpAdapter;
use hollodotme\GitHub\OrgAnalyzer\Infrastructure\Configs\GitHubConfig;
use Throwable;
use function array_values;
use function dirname;
use function error_reporting;
use function file_put_contents;
use function http_build_query;
use function ini_set;
use function json_encode;
use function random_int;
use function usleep;

error_reporting( E_ALL );
ini_set( 'display_errors', 'On' );

require __DIR__ . '/../vendor/autoload.php';

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

$gitHubConfig = new GitHubConfig(
	[
		'personalAccessToken' => $accessToken,
		'apiUrl'              => 'https://api.github.com/graphql',
	]
);

$orgConfig = new OrgConfig(
	[
		'organizationName' => $organizationName,
	]
);

$fastCgiSocket = new NetworkSocket( 'php', 9100 );
$fastCgiClient = new Client( $fastCgiSocket );

$gitHubAdapter    = new GitHubAdapter( $gitHubConfig, new HttpAdapter() );
$gitHubRepository = new GitHubRepository( $orgConfig, $gitHubAdapter );
$outputStream     = new OutputStream();

$outputStream->beginStream();

try
{
	$repositoryInfos = $gitHubRepository->getRepositoryInfos();

	$series      = [];
	$repsitories = [];

	foreach ( $repositoryInfos as $repositoryInfo )
	{
		$repsitories[] = $repositoryInfo->getName();

		$outputStream->streamF(
			'Fetched repository information for "%s/%s"',
			$orgConfig->getOrganizationName(),
			$repositoryInfo->getName()
		);

		$dateCreated = $repositoryInfo->getCreatedAt();
		$color       = $repositoryInfo->getColor();

		if ( !isset( $series[ $color ] ) )
		{
			$series[ $color ] = [
				'name'  => $repositoryInfo->getPrimaryLanguage(),
				'data'  => [],
				'color' => $color,
			];
		}

		$series[ $color ]['data'][] = [
			'x'               => $dateCreated->format( 'c' ),
			'y'               => $repositoryInfo->getCountCommits(),
			'z'               => $repositoryInfo->getDiskUsage(),
			'name'            => $repositoryInfo->getName(),
			'lastTag'         => $repositoryInfo->getLastTag(),
			'diskUsage'       => $repositoryInfo->getDiskUsage(),
			'createdAt'       => $dateCreated->format( 'Y-m-d' ),
			'primaryLanguage' => $repositoryInfo->getPrimaryLanguage(),
			'countCommits'    => $repositoryInfo->getCountCommits(),
		];
	}

	$countApiCalls = $repositoryInfos->getReturn();

	$outputStream->streamF( 'DEBUG: GitHub API calls so far: %d', $countApiCalls );

	if ( $useCommitDate )
	{
		$firstCommitRequest = new GetRequest( __DIR__ . '/firstCommit.php', '' );
		$firstCommitRequest->addResponseCallbacks(
			function ( ProvidesResponseData $response ) use ( &$countApiCalls, &$series, $outputStream )
			{
				$body    = trim( $response->getBody() );
				$matches = [];
				if ( preg_match( '#^ERROR\: (.+)$#i', $body, $matches ) )
				{
					$outputStream->streamF( 'ERROR: Got error while checking for first commit: %s', $matches[1] );

					return;
				}

				[$apiCalls, $repo, $commitDate, $commitUrl] = explode( '|', $body );

				$countApiCalls += $apiCalls;
				$outputStream->streamF( 'First commit date of %s is %s', $repo, $commitDate );
				$outputStream->streamF( 'DEBUG: GitHub API calls so far: %d', $countApiCalls );

				foreach ( $series as $color => $row )
				{
					foreach ( $row['data'] as $index => $data )
					{
						if ( $data['name'] === $repo )
						{
							$series[ $color ]['data'][ $index ]['x']         = $commitDate;
							$series[ $color ]['data'][ $index ]['commitUrl'] = $commitUrl;
							$series[ $color ]['data'][ $index ]['createdAt'] = (new DateTimeImmutable(
								$commitDate
							))->format( 'Y-m-d' );
							break;
						}
					}
				}
			}
		);

		foreach ( $repsitories as $index => $repsitory )
		{
			$queryVars = http_build_query(
				[
					'personalAccessToken' => $accessToken,
					'organizationName'    => $organizationName,
					'repository'          => $repsitory,
				]
			);
			$firstCommitRequest->setCustomVar( 'QUERY_STRING', $queryVars );

			for ( $i = 0; $i < random_int( 1, 10 ) * 10; $i++ )
			{
				$outputStream->stream( '[KEEPALIVE]' );
				usleep( 100000 );
			}

			$fastCgiClient->sendAsyncRequest( $firstCommitRequest );

			$fastCgiClient->handleReadyResponses();
		}

		while ( $fastCgiClient->hasUnhandledResponses() )
		{
			$outputStream->stream( '[KEEPALIVE]' );
			$fastCgiClient->handleReadyResponses();
		}
	}

	$jsonFile = sprintf( '%s/results/repositories/%s.json', dirname( __DIR__ ), $accessToken );
	file_put_contents( $jsonFile, json_encode( array_values( $series ) ) );

	$outputStream->streamF( 'RESULT: %s', $jsonFile );
}
catch ( Throwable $e )
{
	/** @noinspection PhpUnhandledExceptionInspection */
	$outputStream->streamF( "\nERROR: %s", $e->getMessage() );
}
finally
{
	$outputStream->endStream();
}