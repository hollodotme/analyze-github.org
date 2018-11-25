<?php declare(strict_types=1);

namespace hollodotme\GitHub\OrgAnalyzer\Tests\Unit\Application\Web\Responses;

use hollodotme\GitHub\OrgAnalyzer\Application\Web\Responses\EventSourceStream;
use hollodotme\GitHub\OrgAnalyzer\Exceptions\LogicException;
use PHPUnit\Framework\TestCase;

final class EventSourceStreamTest extends TestCase
{
	/**
	 * @runInSeparateProcess
	 * @throws LogicException
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testCanBeginStream() : void
	{
		$stream = new EventSourceStream();

		$stream->beginStream( false );

		/** @noinspection ForgottenDebugOutputInspection */
		$this->assertArraySubset( ['Content-Type: text/event-stream; charset=utf-8'], xdebug_get_headers() );
		$this->expectOutputString( "id: 1\nevent: beginOfStream\ndata: \n\n" );
	}

	/**
	 * @throws LogicException
	 */
	public function testEndingNonActiveStreamThrowsException() : void
	{
		$stream = new EventSourceStream();

		$this->expectException( LogicException::class );

		$stream->endStream();
	}

	/**
	 * @throws LogicException
	 */
	public function testStreamingAnEventOnNonActiveStreamThrowsException() : void
	{
		$stream = new EventSourceStream();

		$this->expectException( LogicException::class );

		$stream->streamEvent( 'Test' );
	}

	/**
	 * @runInSeparateProcess
	 * @throws LogicException
	 */
	public function testCanStreamEvents() : void
	{
		$stream = new EventSourceStream();

		$expectedOutput = "id: 1\nevent: beginOfStream\ndata: \n\n";
		$expectedOutput .= "id: 2\ndata: Unit\n\n";
		$expectedOutput .= "id: 3\nevent: testEvent\ndata: Test\n\n";
		$expectedOutput .= "id: 4\nevent: endOfStream\ndata: \n\n";

		$stream->beginStream( false );
		$stream->streamEvent( 'Unit' );
		$stream->streamEvent( 'Test', 'testEvent' );
		$stream->endStream();

		$this->expectOutputString( $expectedOutput );
	}
}
