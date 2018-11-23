<?php declare(strict_types=1);

namespace hollodotme\GitHub\OrgAnalyzer\Infrastructure\Interfaces;

interface ProvidesResponseData
{
	public function getStatus() : string;

	public function getContentType() : string;

	public function getBody() : string;
}
