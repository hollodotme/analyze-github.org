<?php declare(strict_types=1);

namespace hollodotme\GitHub\OrgAnalyzer\Tests\Stubs;

use ArrayAccess;
use ArrayIterator;
use IteratorAggregate;
use function strlen;

final class HttpStreamStub implements IteratorAggregate, ArrayAccess
{
	private $position = 0;
	private $bodyData = 'test body data';
	private $foo      = ['HTTP/1.1 200 OK', 'Content-Type: text/plain'];

	public function getIterator()
	{
		return new ArrayIterator( $this->foo );
	}

	public function offsetExists( $offset )
	{
		return array_key_exists( $offset, $this->foo );
	}

	public function offsetGet( $offset )
	{
		return $this->foo[ $offset ];
	}

	public function offsetSet( $offset, $value ) : void
	{
		$this->foo[ $offset ] = $value;
	}

	public function offsetUnset( $offset ) : void
	{
		unset( $this->foo[ $offset ] );
	}

	public function stream_open()
	{
		return true;
	}

	public function stream_read()
	{
		$this->position += strlen( $this->bodyData );
		if ( $this->position > strlen( $this->bodyData ) )
		{
			return false;
		}

		return $this->bodyData;
	}

	public function stream_eof()
	{
		return $this->position >= strlen( $this->bodyData );
	}

	public function stream_stat()
	{
		return ['wrapper_data' => []];
	}

	public function stream_tell()
	{
		return $this->position;
	}
}
