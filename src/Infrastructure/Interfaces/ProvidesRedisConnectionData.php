<?php declare(strict_types=1);

namespace hollodotme\GitHub\OrgAnalyzer\Infrastructure\Interfaces;

interface ProvidesRedisConnectionData
{
	public function getHost() : string;

	public function getPort() : int;

	public function getDatabase() : int;

	public function getTimeout() : float;

	public function getPassword() : ?string;

	public function getOptions() : array;
}