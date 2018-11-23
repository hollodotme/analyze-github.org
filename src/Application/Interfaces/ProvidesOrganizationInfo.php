<?php declare(strict_types=1);

namespace hollodotme\GitHub\OrgAnalyzer\Application\Interfaces;

interface ProvidesOrganizationInfo
{
	public function getOrganizationName() : string;
}