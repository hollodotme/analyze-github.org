<?php declare(strict_types=1);

namespace hollodotme\GitHub\OrgAnalyzer\Application\CGI\Responses;

use hollodotme\GitHub\OrgAnalyzer\Exceptions\LogicException;
use function ob_end_clean;
use function ob_end_flush;
use function ob_implicit_flush;
use const PHP_EOL;

final class OutputStream
{
	/** @var bool */
	private $active = false;

	/**
	 * @param bool $flush
	 */
	public function beginStream( bool $flush = true ) : void
	{
		$this->active = true;

		header_remove();
		header( 'Content-Type: text/plain; charset=utf-8', true );

		if ( $flush )
		{
			@ob_end_flush();
			@ob_end_clean();
		}

		@ob_implicit_flush( 1 );

		echo PHP_EOL;
	}

	public function isActive() : bool
	{
		return $this->active;
	}

	/**
	 * @param string $data
	 *
	 * @throws LogicException
	 */
	public function stream( string $data ) : void
	{
		$this->guardStreamIsActive();

		foreach ( explode( PHP_EOL, $data ) as $line )
		{
			echo $line, PHP_EOL;
		}
	}

	/**
	 * @param array $messages
	 *
	 * @throws LogicException
	 */
	public function streamMessages( array $messages ) : void
	{
		foreach ( $messages as $message )
		{
			$this->stream( $message );
		}
	}

	/**
	 * @param string $format
	 * @param mixed  ...$args
	 *
	 * @throws LogicException
	 */
	public function streamF( string $format, ...$args ) : void
	{
		$this->stream( sprintf( $format, ...$args ) );
	}

	/**
	 * @throws LogicException
	 */
	private function guardStreamIsActive() : void
	{
		if ( !$this->isActive() )
		{
			throw new LogicException( 'Output stream is not active.' );
		}
	}

	public function endStream() : void
	{
		$this->active = false;

		@ob_implicit_flush( 0 );
	}
}
