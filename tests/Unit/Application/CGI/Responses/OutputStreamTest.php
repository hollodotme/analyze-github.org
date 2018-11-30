<?php declare(strict_types=1);

namespace hollodotme\GitHub\OrgAnalyzer\Tests\Unit\Application\CGI\Responses;

use hollodotme\GitHub\OrgAnalyzer\Application\CGI\Responses\OutputStream;
use hollodotme\GitHub\OrgAnalyzer\Exceptions\LogicException;
use PHPUnit\Framework\TestCase;

final class OutputStreamTest extends TestCase
{
	/**
	 * @throws LogicException
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testCanBeginStream() : void
	{
		$stream = new OutputStream( 'php://memory' );

		$this->assertFalse( $stream->isActive() );

		$stream->beginStream( false );

		$this->assertTrue( $stream->isActive() );
	}

	/**
	 * @throws LogicException
	 */
	public function testEndingNonActiveStreamThrowsException() : void
	{
		$stream = new OutputStream( 'php://memory' );

		$this->expectException( LogicException::class );
		$this->expectExceptionMessage( 'Output stream is not active.' );

		$stream->endStream();
	}

	/**
	 * @throws LogicException
	 */
	public function testStreamingOutputOnNonActiveStreamThrowsException() : void
	{
		$stream = new OutputStream( 'php://memory' );

		$this->expectException( LogicException::class );
		$this->expectExceptionMessage( 'Output stream is not active.' );

		$stream->stream( 'Test' );
	}

	/**
	 * @runInSeparateProcess
	 * @throws LogicException
	 */
	public function testCanStreamOutput() : void
	{
		$stream = new OutputStream( 'php://output' );

		$expectedOutput = "Unit\nTest\nUnit\nTest\nUnitTest\n";

		$this->expectOutputString( $expectedOutput );

		$stream->beginStream( false );
		$stream->stream( 'Unit' );
		$stream->stream( 'Test' );
		$stream->stream( "Unit\nTest" );
		$stream->streamF( 'Unit%s', 'Test' );
		$stream->endStream();
	}
}
