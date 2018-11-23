<?php declare(strict_types=1);

namespace hollodotme\GitHub\OrgAnalyzer\Tests\Unit\Infrastructure\Adapters\Http;

use hollodotme\GitHub\OrgAnalyzer\Infrastructure\Adapters\Http\HttpPostRequest;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

final class HttpPostRequestTest extends TestCase
{

	/**
	 * @throws ExpectationFailedException
	 * @throws InvalidArgumentException
	 */
	public function testCanGetInitialValues() : void
	{
		$postRequest = new HttpPostRequest( 'http://example.com' );

		$this->assertSame( 'http://example.com', $postRequest->getUrl() );
		$this->assertSame( 'POST', $postRequest->getMethod() );
		$this->assertSame( [], $postRequest->getHeaders() );
		$this->assertSame( [], $postRequest->getParams() );
		$this->assertSame( '', $postRequest->getBody() );
	}

	/**
	 * @throws ExpectationFailedException
	 * @throws InvalidArgumentException
	 */
	public function testCanSetCredentials() : void
	{
		$bearerToken    = '123343948738112342';
		$expectedHeader = 'Authorization: Bearer ' . $bearerToken;

		$postRequest = new HttpPostRequest( 'http://example.com' );
		$postRequest->setBearerToken( $bearerToken );

		$this->assertSame( [$expectedHeader], $postRequest->getHeaders() );
	}

	/**
	 * @throws ExpectationFailedException
	 * @throws InvalidArgumentException
	 */
	public function testCanAddHeaders() : void
	{
		$postRequest = new HttpPostRequest( 'http://example.com' );
		$postRequest->addHeaders(
			[
				'Content-Type: text/plain',
				'Accept: application/json',
			]
		);
		$postRequest->addHeaders(
			[
				'X-Producer-ID: Unit-Test-Producer-ID',
			]
		);

		$expectedHeaders = [
			'Content-Type: text/plain',
			'Accept: application/json',
			'X-Producer-ID: Unit-Test-Producer-ID',
		];

		$this->assertSame( $expectedHeaders, $postRequest->getHeaders() );
	}

	/**
	 * @throws ExpectationFailedException
	 * @throws InvalidArgumentException
	 */
	public function testCanAddParams() : void
	{
		$postRequest = new HttpPostRequest( 'http://example.com' );
		$postRequest->addParams(
			[
				'unit' => 'test',
				'test' => 'unit',
			]
		);
		$postRequest->addParams(
			[
				'limit'     => 10,
				'eventName' => 'EventTested',
			]
		);

		$expectedParams = [
			'unit'      => 'test',
			'test'      => 'unit',
			'limit'     => 10,
			'eventName' => 'EventTested',
		];

		$this->assertSame( $expectedParams, $postRequest->getParams() );
	}

	/**
	 * @throws ExpectationFailedException
	 * @throws InvalidArgumentException
	 */
	public function testCanSetBody() : void
	{
		$body        = 'Some Body';
		$postRequest = new HttpPostRequest( 'http://example.com' );
		$postRequest->setBody( $body );

		$this->assertSame( $body, $postRequest->getBody() );
	}
}
