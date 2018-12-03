<?php declare(strict_types=1);

namespace hollodotme\GitHub\OrgAnalyzer\Tests\Unit\Application\CGI\Responses;

use hollodotme\GitHub\OrgAnalyzer\Application\CGI\Responses\OutputStream;
use hollodotme\GitHub\OrgAnalyzer\Exceptions\LogicException;
use PHPUnit\Framework\TestCase;
use const PHP_EOL;

final class OutputStreamTest extends TestCase
{
	/**
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 * @runInSeparateProcess
	 */
	public function testCanBeginStream() : void
	{
		$stream = new OutputStream();

		$this->assertFalse( $stream->isActive() );
		$this->expectOutputString( PHP_EOL );

		$stream->beginStream( false );

		$this->assertTrue( $stream->isActive() );
	}

	/**
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 * @runInSeparateProcess
	 */
	public function testCanEndStream() : void
	{
		$stream = new OutputStream();

		$this->expectOutputString( PHP_EOL );
		$stream->beginStream( false );

		$this->assertTrue( $stream->isActive() );

		$stream->endStream();

		$this->assertFalse( $stream->isActive() );
	}

	/**
	 * @throws LogicException
	 */
	public function testStreamingOutputOnNonActiveStreamThrowsException() : void
	{
		$stream = new OutputStream();

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
		$stream = new OutputStream();

		$expectedOutput = "\nUnit\nTest\nUnit\nTest\nUnitTest\n";

		$this->expectOutputString( $expectedOutput );

		$stream->beginStream( false );
		$stream->stream( 'Unit' );
		$stream->stream( 'Test' );
		$stream->stream( "Unit\nTest" );
		$stream->streamF( 'Unit%s', 'Test' );
		$stream->endStream();
	}
}
