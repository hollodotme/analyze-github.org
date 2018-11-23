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

	public static function fromConfigFile() : ConfiguresGitHubAdapter
	{
		return new self( (array)require __DIR__ . '/../../../config/GitHub.php' );
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