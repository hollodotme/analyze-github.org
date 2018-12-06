<?php declare(strict_types=1);

namespace hollodotme\GitHub\OrgAnalyzer\Infrastructure\Adapters\Redis;

use hollodotme\GitHub\OrgAnalyzer\Infrastructure\Interfaces\ProvidesRedisConnectionData;

final class RedisConnection implements ProvidesRedisConnectionData
{
	/** @var string */
	private $host;

	/** @var int */
	private $port;

	/** @var int */
	private $database;

	/** @var float */
	private $timeout;

	/** @var null|string */
	private $password;

	/** @var array */
	private $options;

	public function __construct(
		string $host,
		int $port,
		int $database,
		float $timeout,
		?string $password,
		array $options
	)
	{
		$this->host     = $host;
		$this->port     = $port;
		$this->database = $database;
		$this->timeout  = $timeout;
		$this->password = $password;
		$this->options  = $options;
	}

	public static function fromConfigFile() : self
	{
		$config = (array)require __DIR__ . '/../../../../config/Redis.php';

		return new self(
			(string)$config['host'],
			(int)$config['port'],
			(int)$config['database'],
			(float)$config['timeout'],
			$config['password'],
			(array)$config['options']
		);
	}

	public function getHost() : string
	{
		return $this->host;
	}

	public function getPort() : int
	{
		return $this->port;
	}

	public function getDatabase() : int
	{
		return $this->database;
	}

	public function getTimeout() : float
	{
		return $this->timeout;
	}

	public function getPassword() : ?string
	{
		return $this->password;
	}

	public function getOptions() : array
	{
		return $this->options;
	}
}