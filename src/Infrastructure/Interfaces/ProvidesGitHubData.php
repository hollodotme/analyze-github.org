<?php declare(strict_types=1);

namespace hollodotme\GitHub\OrgAnalyzer\Infrastructure\Interfaces;

use hollodotme\GitHub\OrgAnalyzer\Infrastructure\Adapters\GitHub\Exceptions\GitHubApiRequestFailed;
use stdClass;

interface ProvidesGitHubData
{
	/**
	 * @param string $query
	 *
	 * @throws GitHubApiRequestFailed
	 * @return stdClass
	 */
	public function executeQuery( string $query ) : stdClass;
}