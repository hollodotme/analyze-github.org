<?php declare(strict_types=1);

namespace hollodotme\GitHub\OrgAnalyzer\Application\Repositories\GitHub;

use DateTimeImmutable;
use DateTimeInterface;
use stdClass;

final class RepositoryInfo
{
	/** @var string */
	private $id;

	/** @var string */
	private $name;

	/** @var string */
	private $lastTag;

	/** @var int */
	private $diskUsage;

	/** @var DateTimeInterface */
	private $createdAt;

	/**
	 * @param stdClass $jsonObject
	 *
	 * @throws \Exception
	 * @return RepositoryInfo
	 */
	public static function fromJsonObject( stdClass $jsonObject ) : self
	{
		$info            = new self();
		$info->id        = $jsonObject->id;
		$info->name      = $jsonObject->name;
		$info->createdAt = new DateTimeImmutable( $jsonObject->createdAt );
		$info->diskUsage = $jsonObject->diskUsage;
		$info->lastTag   = $jsonObject->refs->nodes ? $jsonObject->refs->nodes[0]->name : 'N/A';

		return $info;
	}

	public function getId() : string
	{
		return $this->id;
	}

	public function getName() : string
	{
		return $this->name;
	}

	public function getLastTag() : string
	{
		return $this->lastTag;
	}

	public function getDiskUsage() : int
	{
		return $this->diskUsage;
	}

	public function getCreatedAt() : DateTimeInterface
	{
		return $this->createdAt;
	}
}