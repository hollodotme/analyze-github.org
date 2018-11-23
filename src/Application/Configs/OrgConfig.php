<?php declare(strict_types=1);

namespace hollodotme\GitHub\OrgAnalyzer\Application\Configs;

use hollodotme\GitHub\OrgAnalyzer\Application\Interfaces\ProvidesOrganizationInfo;

final class OrgConfig implements ProvidesOrganizationInfo
{
	/** @var array */
	private $configData;

	public function __construct( array $configData )
	{
		$this->configData = $configData;
	}

	public static function fromConfigFile() : self
	{
		return new self( (array)require __DIR__ . '/../../../config/Org.php' );
	}

	public function getOrganizationName() : string
	{
		return (string)$this->configData['organizationName'];
	}
}