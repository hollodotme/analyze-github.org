<?php declare(strict_types=1);

namespace hollodotme\GitHub\OrgAnalyzer;

use hollodotme\GitHub\OrgAnalyzer\Application\Configs\OrgConfig;
use hollodotme\GitHub\OrgAnalyzer\Application\Repositories\GitHubRepository;
use hollodotme\GitHub\OrgAnalyzer\Infrastructure\Adapters\GitHub\GitHubAdapter;
use hollodotme\GitHub\OrgAnalyzer\Infrastructure\Adapters\Http\HttpAdapter;
use hollodotme\GitHub\OrgAnalyzer\Infrastructure\Configs\GitHubConfig;
use Throwable;
use function header;

require_once __DIR__ . '/../vendor/autoload.php';

$gitHubAdapter    = new GitHubAdapter( GitHubConfig::fromConfigFile(), new HttpAdapter() );
$gitHubRepository = new GitHubRepository( OrgConfig::fromConfigFile(), $gitHubAdapter );

try
{
	header( 'Content-Type: text/plain; charset=utf-8', true, 200 );
	foreach ( $gitHubRepository->getRepositoryInfos() as $repositoryInfo )
	{
		print_r( $repositoryInfo );
		flush();
	}
}
catch ( Throwable $e )
{
	die( $e->getMessage() );
}