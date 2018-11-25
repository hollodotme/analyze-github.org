<?php declare(strict_types=1);

namespace hollodotme\GitHub\OrgAnalyzer\Application\CGI\Responses;

use hollodotme\GitHub\OrgAnalyzer\Exceptions\LogicException;
use hollodotme\GitHub\OrgAnalyzer\Exceptions\RuntimeException;
use function fclose;
use function fflush;
use function fwrite;
use function is_resource;

final class OutputStream
{
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
	 * @param bool $flush
	 *
	 * @throws LogicException
	 */
	public function beginStream( bool $flush = true ) : void
	{
		$this->resource = fopen( $this->target, $this->mode );

		if ( false === $this->resource )
		{
			return;
		}

		$this->guardStreamIsActive();

		if ( $flush )
		{
			fflush( $this->resource );
		}
	}

	public function isActive() : bool
	{
		return is_resource( $this->resource ) && 'stream' === get_resource_type( $this->resource );
	}

	/**
	 * @param string $data
	 *
	 * @throws LogicException
	 */
	public function stream( string $data ) : void
	{
		$this->guardStreamIsActive();

		if ( false === $this->resource )
		{
			return;
		}

		foreach ( explode( PHP_EOL, $data ) as $line )
		{
			fwrite( $this->resource, $line );
		}

		fflush( $this->resource );
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
	 * @param string $filePath
	 *
	 * @throws LogicException
	 * @throws RuntimeException
	 */
	public function streamWhileFileExists( string $filePath ) : void
	{
		$handle = fopen( $filePath, 'rb' );

		if ( false === $handle )
		{
			throw new RuntimeException( 'Could not open file: ' . $filePath );
		}

		while ( $line = fgets( $handle, 1024 ) )
		{
			$this->stream( $line );
		}

		while ( file_exists( $filePath ) )
		{
			$read  = [$handle];
			$write = $except = null;

			if ( !stream_select( $read, $write, $except, 0, 200000 ) )
			{
				continue;
			}

			$this->stream( (string)fread( $handle, 1024 ) );
		}

		fclose( $handle );
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

	/**
	 * @throws LogicException
	 */
	public function endStream() : void
	{
		$this->guardStreamIsActive();

		if ( false !== $this->resource )
		{
			fflush( $this->resource );
			fclose( $this->resource );
		}
	}
}
