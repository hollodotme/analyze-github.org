<?php declare(strict_types=1);

namespace hollodotme\GitHub\OrgAnalyzer\Application\Web\Responses;

use hollodotme\GitHub\OrgAnalyzer\Exceptions\LogicException;

final class EventSourceStream
{
	private const BEGIN_OF_STREAM_EVENT = 'beginOfStream';

	private const END_OF_STREAM_EVENT   = 'endOfStream';

	/** @var int */
	private $eventSequence = 0;

	/** @var bool */
	private $active = false;

	/**
	 * @param bool $flushBuffer
	 *
	 * @throws LogicException
	 */
	public function beginStream( bool $flushBuffer = true ) : void
	{
		$this->active = true;

		if ( $flushBuffer )
		{
			@ob_end_flush();
			@ob_end_clean();
			flush();
		}

		@ob_implicit_flush( 1 );

		header( 'X-Accel-Buffering: no;' );
		header( 'Cache-Control: no-cache;' );
		header( 'Content-Type: text/event-stream; charset=utf-8' );

		$this->streamEvent( '', self::BEGIN_OF_STREAM_EVENT );
	}

	public function isActive() : bool
	{
		return $this->active;
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

		$this->eventSequence++;

		echo "id: {$this->eventSequence}\n";
		echo (null !== $eventName) ? "event: {$eventName}\n" : '';

		foreach ( explode( "\n", $data ) as $line )
		{
			echo "data: {$line}\n";
		}

		echo "\n";
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

		flush();
		@ob_implicit_flush( 0 );
	}
}
