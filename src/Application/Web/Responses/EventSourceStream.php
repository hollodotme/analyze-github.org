<?php declare(strict_types=1);

namespace hollodotme\GitHub\OrgAnalyzer\Application\Web\Responses;

use hollodotme\GitHub\OrgAnalyzer\Exceptions\LogicException;
use function fclose;
use function fflush;
use function fwrite;
use function get_resource_type;
use function is_resource;

final class EventSourceStream
{
	private const BEGIN_OF_STREAM_EVENT = 'beginOfStream';

	private const END_OF_STREAM_EVENT   = 'endOfStream';

	/** @var int */
	private $eventSequence = 0;

	/** @var false|resource */
	private $resource;

	/** @var string */
	private $target;

	/** @var string */
	private $mode;

	public function __construct( string $target = 'php://output', string $mode = 'wb' )
	{
		$this->target = $target;
		$this->mode   = $mode;
	}

	/**
	 * @param bool $flushBuffer
	 *
	 * @throws LogicException
	 */
	public function beginStream( bool $flushBuffer = true ) : void
	{
		header( 'Content-Type: text/event-stream; charset=utf-8' );

		$this->resource = fopen( $this->target, $this->mode );

		if ( false === $this->resource )
		{
			return;
		}

		$this->guardStreamIsActive();

		if ( $flushBuffer )
		{
			fflush( $this->resource );
		}

		$this->streamEvent( '', self::BEGIN_OF_STREAM_EVENT );
	}

	public function isActive() : bool
	{
		return is_resource( $this->resource ) && 'stream' === get_resource_type( $this->resource );
	}

	/**
	 * @param string      $data
	 * @param null|string $eventName
	 *
	 * @throws LogicException
	 */
	public function streamEvent( string $data, ?string $eventName = null ) : void
	{
		$this->guardStreamIsActive();

		if ( false === $this->resource )
		{
			return;
		}

		$streamData = $data;

		if ( false === strpos( $streamData, PHP_EOL ) )
		{
			fwrite( $this->resource, 'id: ' . ++$this->eventSequence . PHP_EOL );
			fwrite( $this->resource, (null !== $eventName) ? ('event: ' . $eventName . PHP_EOL) : '' );
			fwrite( $this->resource, 'data: ' . $streamData . PHP_EOL . PHP_EOL );

			return;
		}

		foreach ( explode( PHP_EOL, $streamData ) as $line )
		{
			$this->streamEvent( $line, $eventName );
		}
	}

	/**
	 * @throws LogicException
	 */
	private function guardStreamIsActive() : void
	{
		if ( !$this->isActive() )
		{
			throw new LogicException( 'Event source stream is not active.' );
		}
	}

	/**
	 * @throws LogicException
	 */
	public function endStream() : void
	{
		$this->guardStreamIsActive();

		$this->streamEvent( '', self::END_OF_STREAM_EVENT );

		if ( false !== $this->resource )
		{
			fflush( $this->resource );
			fclose( $this->resource );
		}
	}
}
