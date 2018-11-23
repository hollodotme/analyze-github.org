<?php declare(strict_types=1);

namespace hollodotme\GitHub\OrgAnalyzer\Infrastructure\Adapters\Http;

final class HttpContextOptions
{
	private const DEFAULTS = [
		'http' => [
			'protocol_version' => 1.1,
			'user_agent'       => 'hollodotme/GitHub-Org-Analyzer',
			'timeout'          => 30,
			'ignore_errors'    => true,
		],
	];

	/** @var array */
	private $options;

	public function __construct( array $options )
	{
		$this->options = array_replace_recursive( self::DEFAULTS, $options );
	}

	public function getOptions() : array
	{
		return $this->options;
	}
}
