<?php declare(strict_types=1);

namespace hollodotme\GitHub\OrgAnalyzer\Infrastructure\Configs;

use hollodotme\GitHub\OrgAnalyzer\Infrastructure\Interfaces\ConfiguresGitHubAdapter;

final class GitHubConfig implements ConfiguresGitHubAdapter
{
	/** @var array */
	private $configData;

	public function __construct( array $configData )
	{
		$this->configData = $configData;
	}

	public function getApiUrl() : string
	{
		return (string)$this->configData['apiUrl'];
	}

	public function getPersonalAccessToken() : string
	{
		return (string)$this->configData['personalAccessToken'];
	}
}