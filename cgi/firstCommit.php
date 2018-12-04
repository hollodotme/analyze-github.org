<?php declare(strict_types=1);

namespace hollodotme\GitHub\OrgAnalyzer\Cgi;

use hollodotme\GitHub\OrgAnalyzer\Application\CGI\Responses\OutputStream;
use hollodotme\GitHub\OrgAnalyzer\Application\Configs\OrgConfig;
use hollodotme\GitHub\OrgAnalyzer\Application\Repositories\GitHubRepository;
use hollodotme\GitHub\OrgAnalyzer\Infrastructure\Adapters\GitHub\GitHubAdapter;
use hollodotme\GitHub\OrgAnalyzer\Infrastructure\Adapters\Http\HttpAdapter;
use hollodotme\GitHub\OrgAnalyzer\Infrastructure\Configs\GitHubConfig;
use Throwable;
use function error_reporting;
use function ini_set;

error_reporting( E_ALL );
ini_set( 'display_errors', 'On' );

require __DIR__ . '/../vendor/autoload.php';

if ( 'cli' === PHP_SAPI )
{
	$accessToken      = trim( (string)$argv[1] );
	$organizationName = trim( (string)$argv[2] );
	$repository       = trim( (string)$argv[3] );
}
else
{
	$accessToken      = trim( (string)$_GET['personalAccessToken'] );
	$organizationName = trim( (string)$_GET['organizationName'] );
	$repository       = trim( (string)$_GET['repository'] );
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

$gitHubAdapter    = new GitHubAdapter( $gitHubConfig, new HttpAdapter() );
$gitHubRepository = new GitHubRepository( $orgConfig, $gitHubAdapter );
$outputStream     = new OutputStream();

try
{
	$firstCommit = null;
	$history     = $gitHubRepository->getCommitHistory( $repository );

	foreach ( $history as $commitHistoryItem )
	{
		$firstCommit = $commitHistoryItem;
	}

	$apiCalls = (int)$history->getReturn();

	$outputStream->beginStream();
	$outputStream->streamF(
		'%d|%s|%s',
		$apiCalls,
		$repository,
		$firstCommit->getCommitDate()->format( 'c' )
	);
	$outputStream->endStream();
}
catch ( Throwable $e )
{
	die( 'ERROR: ' . $e->getMessage() );
}