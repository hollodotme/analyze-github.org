<?php declare(strict_types=1);

namespace hollodotme\GitHub\OrgAnalyzer\Tests\Unit\Infrastructure\Adapters\Http;

use hollodotme\GitHub\OrgAnalyzer\Exceptions\RuntimeException;
use hollodotme\GitHub\OrgAnalyzer\Infrastructure\Adapters\Http\HttpResponse;
use hollodotme\GitHub\OrgAnalyzer\Tests\Stubs\HttpStreamStub;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\RecursionContext\InvalidArgumentException;
use function stream_wrapper_unregister;

final class HttpResponseTest extends TestCase
{
	/**
	 * @throws RuntimeException
	 */
	public function testConstructionWithInvalidStreamThrowsException() : void
	{
		$this->expectException( RuntimeException::class );

		/** @noinspection PhpParamsInspection */
		new HttpResponse( 'no-stream' );
	}

	/**
	 * @throws RuntimeException
	 * @throws ExpectationFailedException
	 * @throws InvalidArgumentException
	 */
	public function testCanGetResponseStatus() : void
	{
		stream_wrapper_register( 'fake', HttpStreamStub::class );
		$response = new HttpResponse( fopen( 'fake://test', 'rb' ) );

		$this->assertSame( '200', $response->getStatus() );

		stream_wrapper_unregister( 'fake' );
	}

	/**
	 * @throws RuntimeException
	 * @throws ExpectationFailedException
	 * @throws InvalidArgumentException
	 */
	public function testCanGetResponseContentType() : void
	{
		stream_wrapper_register( 'fake', HttpStreamStub::class );
		$response = new HttpResponse( fopen( 'fake://test', 'rb' ) );

		$this->assertSame( 'text/plain', $response->getContentType() );

		stream_wrapper_unregister( 'fake' );
	}

	/**
	 * @throws RuntimeException
	 * @throws ExpectationFailedException
	 * @throws InvalidArgumentException
	 */
	public function testCanGetBody() : void
	{
		stream_wrapper_register( 'fake', HttpStreamStub::class );
		$response = new HttpResponse( fopen( 'fake://test', 'rb' ) );

		$this->assertSame( 'test body data', $response->getBody() );
	}
}
