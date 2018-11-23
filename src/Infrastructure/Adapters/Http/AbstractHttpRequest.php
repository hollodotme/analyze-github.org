<?php declare(strict_types=1);

namespace hollodotme\GitHub\OrgAnalyzer\Infrastructure\Adapters\Http;

use hollodotme\GitHub\OrgAnalyzer\Infrastructure\Interfaces\ProvidesRequestData;

abstract class AbstractHttpRequest implements ProvidesRequestData
{
	/** @var string */
	private $url;

	/** @var array */
	private $headers;

	/** @var array */
	private $params;

	/** @var string */
	private $body;

	public function __construct( string $url )
	{
		$this->url     = $url;
		$this->headers = [];
		$this->params  = [];
		$this->body    = '';
	}

	public function getUrl() : string
	{
		return $this->url;
	}

	public function setBearerToken( string $bearerToken ) : void
	{
		$this->headers[] = 'Authorization: Bearer ' . $bearerToken;
	}

	public function getHeaders() : array
	{
		return $this->headers;
	}

	public function addHeaders( array $headers ) : void
	{
		$this->headers = array_merge( $this->headers, $headers );
	}

	public function getParams() : array
	{
		return $this->params;
	}

	public function addParams( array $params ) : void
	{
		$this->params = array_merge( $this->params, $params );
	}

	public function getBody() : string
	{
		return $this->body;
	}

	public function setBody( string $body ) : void
	{
		$this->body = $body;
	}
}
