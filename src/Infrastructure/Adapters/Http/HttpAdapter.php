<?php declare(strict_types=1);

namespace hollodotme\GitHub\OrgAnalyzer\Infrastructure\Adapters\Http;

use hollodotme\GitHub\OrgAnalyzer\Exceptions\RuntimeException;
use hollodotme\GitHub\OrgAnalyzer\Infrastructure\Adapters\Http\Exceptions\HttpConnectException;
use hollodotme\GitHub\OrgAnalyzer\Infrastructure\Interfaces\ProvidesRequestData;
use hollodotme\GitHub\OrgAnalyzer\Infrastructure\Interfaces\ProvidesResponseData;
use hollodotme\GitHub\OrgAnalyzer\Infrastructure\Interfaces\WrapsRemoteTransfer;

final class HttpAdapter implements WrapsRemoteTransfer
{
	/** @var HttpContextOptions */
	private $contextOptions;

	public function __construct( ?HttpContextOptions $contextOptions = null )
	{
		$this->contextOptions = $contextOptions ?? new HttpContextOptions( [] );
	}

	/**
	 * @param ProvidesRequestData $request
	 *
	 * @return ProvidesResponseData
	 * @throws RuntimeException
	 * @throws HttpConnectException
	 */
	public function send( ProvidesRequestData $request ) : ProvidesResponseData
	{
		$url = sprintf( '%s?%s', $request->getUrl(), http_build_query( $request->getParams() ) );

		$stream = @fopen( $url, 'rb', false, $this->createContext( $request ) );

		if ( false === $stream )
		{
			$error = error_get_last();

			throw new HttpConnectException( 'HTTP Connection failed: ' . ($error ? $error['message'] : '') );
		}

		return new HttpResponse( $stream );
	}

	/**
	 * @param ProvidesRequestData $request
	 *
	 * @return resource
	 */
	private function createContext( ProvidesRequestData $request )
	{
		$options = [
			'http' => [
				'method'  => $request->getMethod(),
				'header'  => implode( "\r\n", $request->getHeaders() ),
				'content' => $request->getBody(),
			],
		];

		$options = array_replace_recursive( $options, $this->contextOptions->getOptions() );

		return stream_context_create( $options );
	}
}
