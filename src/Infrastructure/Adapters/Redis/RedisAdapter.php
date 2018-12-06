<?php declare(strict_types=1);

namespace hollodotme\GitHub\OrgAnalyzer\Infrastructure\Adapters\Redis;

use hollodotme\GitHub\OrgAnalyzer\Exceptions\RuntimeException;
use hollodotme\GitHub\OrgAnalyzer\Infrastructure\Interfaces\ProvidesRedisConnectionData;
use Redis;
use function implode;

final class RedisAdapter
{
	/** @var Redis */
	private $redis;

	/** @var ProvidesRedisConnectionData */
	private $connection;

	/** @var bool */
	private $connected;

	public function __construct( ProvidesRedisConnectionData $connection )
	{
		$this->redis      = new Redis();
		$this->connection = $connection;
		$this->connected  = false;
	}

	/**
	 * @param string $key
	 * @param string $hashKey
	 * @param string $value
	 *
	 * @throws RuntimeException
	 * @return int
	 */
	public function hSet( string $key, string $hashKey, string $value ) : int
	{
		$this->connect();

		$result = $this->redis->hSet( $key, $hashKey, $value );

		if ( false === $result )
		{
			throw new RuntimeException( 'Could not set hash value' );
		}

		return $result;
	}

	/**
	 * @param string $key
	 * @param array  $hashValues
	 *
	 * @throws RuntimeException
	 * @return bool
	 */
	public function hMSet( string $key, array $hashValues ) : bool
	{
		$this->connect();

		return $this->redis->hMSet( $key, $hashValues );
	}

	public function getKeyFromSegments( string $segment, string ...$segments ) : string
	{
		return $segment . ($segments ? (':' . implode( ':', $segments )) : '');
	}

	/**
	 * @throws RuntimeException
	 */
	private function connect() : void
	{
		if ( true === $this->connected )
		{
			return;
		}

		$this->redis->connect(
			$this->connection->getHost(),
			$this->connection->getPort(),
			$this->connection->getTimeout()
		);

		if ( false === $this->redis )
		{
			throw new RuntimeException( 'Could not connect to redis server' );
		}

		if ( null !== $this->connection->getPassword() )
		{
			$this->redis->auth( $this->connection->getPassword() );
		}

		foreach ( $this->connection->getOptions() as $name => $value )
		{
			$this->redis->setOption( $name, $value );
		}

		$this->connected = true;
	}
}