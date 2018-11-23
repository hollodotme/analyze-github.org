<?php declare(strict_types=1);

namespace hollodotme\GitHub\OrgAnalyzer\Infrastructure\Interfaces;

interface ProvidesRequestData
{
	public function getUrl() : string;

	public function getMethod() : string;

	public function getHeaders() : array;

	public function getParams() : array;

	public function getBody() : string;
}
