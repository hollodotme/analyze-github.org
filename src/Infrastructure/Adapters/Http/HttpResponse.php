<?php declare(strict_types=1);

namespace hollodotme\GitHub\OrgAnalyzer\Infrastructure\Adapters\Http;

use hollodotme\GitHub\OrgAnalyzer\Exceptions\RuntimeException;
use hollodotme\GitHub\OrgAnalyzer\Infrastructure\Interfaces\ProvidesResponseData;
use function error_get_last;
use function explode;
use function get_resource_type;
use function is_resource;
use function stream_get_contents;
use function stream_get_meta_data;
use function strpos;
use function strtolower;
use function substr;
use function trim;

final class HttpResponse implements ProvidesResponseData
{
	/** @var resource */
	private $stream;

	/** @var array */
	private $headers;

	/**
	 * @param resource $stream
	 *
	 * @throws RuntimeException
	 */
	public function __construct( $stream )
	{
		$this->guardStreamIsValid( $stream );

		$this->stream = $stream;
		$this->readHeaders();
	}

	/**
	 * @param resource|null $stream
	 *
	 * @throws RuntimeException
	 */
	private function guardStreamIsValid( $stream ) : void
	{
		if ( !is_resource( $stream ) || 'stream' !== get_resource_type( $stream ) )
		{
			$error = error_get_last();

			throw new RuntimeException( 'Not a valid stream: ' . ($error ? $error['message'] : '') );
		}
	}

	private function readHeaders() : void
	{
		$streamMetaData = stream_get_meta_data( $this->stream );
		$wrapperData    = $streamMetaData['wrapper_data'];
		$this->headers  = [];

		/** @var iterable $wrapperData */
		foreach ( $wrapperData as $header )
		{
			if ( false === strpos( $header, ':' ) )
			{
				if ( 0 === strpos( $header, 'HTTP' ) )
				{
					$this->headers['status'] = substr( $header, 9, 3 );
				}

				continue;
			}

			$headerParts = explode( ': ', $header );
			$name        = strtolower( $headerParts[0] );

			$this->headers[ $name ] = trim( $headerParts[1] ?? '' );
		}
	}

	public function getStatus() : string
	{
		return $this->headers['status'] ?? '';
	}

	public function getContentType() : string
	{
		return $this->headers['content-type'] ?? '';
	}

	public function hasHeader( string $header ) : bool
	{
		return isset( $this->headers[ strtolower( $header ) ] );
	}

	public function getHeader( string $header ) : string
	{
		return $this->headers[ strtolower( $header ) ] ?? '';
	}

	public function getBody() : string
	{
		return (string)stream_get_contents( $this->stream );
	}
}
