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

$gitHubConfig = new GitHubConfig(
	[
		'personalAccessToken' => $_GET['personalAccessToken'],
		'apiUrl'              => 'https://api.github.com/graphql',
	]
);

$orgConfig = new OrgConfig(
	[
		'organizationName' => $_GET['organizationName'],
	]
);

$gitHubAdapter    = new GitHubAdapter( $gitHubConfig, new HttpAdapter() );
$gitHubRepository = new GitHubRepository( $orgConfig, $gitHubAdapter );

try
{
	$outputStream = new OutputStream();
	$outputStream->beginStream();

	foreach ( $gitHubRepository->getRepositoryInfos() as $repositoryInfo )
	{
		$outputStream->streamF(
			'Fetched repository information for "%s/%s"',
			$orgConfig->getOrganizationName(),
			$repositoryInfo->getName()
		);
	}

	$outputStream->endStream();
}
catch ( Throwable $e )
{
	die( $e->getMessage() );
}