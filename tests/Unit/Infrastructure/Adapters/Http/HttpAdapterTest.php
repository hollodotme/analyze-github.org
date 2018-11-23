<?php declare(strict_types=1);

namespace hollodotme\GitHub\OrgAnalyzer\Tests\Unit\Infrastructure\Adapters\Http;

use hollodotme\GitHub\OrgAnalyzer\Exceptions\RuntimeException;
use hollodotme\GitHub\OrgAnalyzer\Infrastructure\Adapters\Http\Exceptions\HttpConnectException;
use hollodotme\GitHub\OrgAnalyzer\Infrastructure\Adapters\Http\HttpAdapter;
use hollodotme\GitHub\OrgAnalyzer\Infrastructure\Interfaces\ProvidesRequestData;
use PHPUnit\Framework\TestCase;

final class HttpAdapterTest extends TestCase
{
	/**
	 * @throws HttpConnectException
	 * @throws RuntimeException
	 */
	public function testFailedConnectionThrowsException() : void
	{
		$http    = new HttpAdapter();
		$request = new class implements ProvidesRequestData
		{
			public function getUrl() : string
			{
				return 'https://localhost:8080';
			}

			public function getMethod() : string
			{
				return 'POST';
			}

			public function getHeaders() : array
			{
				return [];
			}

			public function getParams() : array
			{
				return [];
			}

			public function getBody() : string
			{
				return '';
			}

		};

		$this->expectException( HttpConnectException::class );
		$this->expectExceptionMessageRegExp(
			'#^HTTP Connection failed: fopen\(https://localhost:8080\?\): failed to open stream: .+$#'
		);

		$http->send( $request );
	}
}
