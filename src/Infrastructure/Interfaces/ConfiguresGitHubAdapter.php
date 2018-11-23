<?php declare(strict_types=1);

namespace hollodotme\GitHub\OrgAnalyzer\Infrastructure\Interfaces;

interface ConfiguresGitHubAdapter
{
	public function getApiUrl() : string;

	public function getPersonalAccessToken() : string;
}