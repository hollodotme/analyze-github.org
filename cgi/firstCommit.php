<?php declare(strict_types=1);

namespace hollodotme\GitHub\OrgAnalyzer\Cgi;

use hollodotme\GitHub\OrgAnalyzer\Application\CGI\Responses\OutputStream;
use hollodotme\GitHub\OrgAnalyzer\Application\Configs\OrgConfig;
use hollodotme\GitHub\OrgAnalyzer\Application\Repositories\GitHubRepository;
use hollodotme\GitHub\OrgAnalyzer\Infrastructure\Adapters\GitHub\GitHubAdapter;
use hollodotme\GitHub\OrgAnalyzer\Infrastructure\Adapters\Http\HttpAdapter;
use hollodotme\GitHub\OrgAnalyzer\Infrastructure\Configs\GitHubConfig;
use Throwable;

require __DIR__ . '/../vendor/autoload.php';

$accessToken      = $_GET['personalAccessToken'];
$organizationName = $_GET['organizationName'];
$repository       = $_GET['repository'];

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
	$apiCalls    = 0;
	$firstCommit = $gitHubRepository->getFirstCommitDate( $repository, $apiCalls );

	$outputStream->beginStream();
	$outputStream->streamF(
		'%d|%s|%s|%s',
		$apiCalls,
		$repository,
		$firstCommit->getCommitDate()->format( 'c' ),
		$firstCommit->getCommitUrl()
	);
	$outputStream->endStream();
}
catch ( Throwable $e )
{
	die( 'ERROR: ' . $e->getMessage() );
}