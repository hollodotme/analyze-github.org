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
	private $nameWithOwner;

	/** @var string */
	private $lastTag;

	/** @var int */
	private $diskUsage;

	/** @var DateTimeInterface */
	private $createdAt;

	/** @var string */
	private $primaryLanguage;

	/** @var string */
	private $color;

	/** @var int */
	private $countCommits;

	/**
	 * @param stdClass $jsonObject
	 *
	 * @throws \Exception
	 * @return RepositoryInfo
	 */
	public static function fromJsonObject( stdClass $jsonObject ) : self
	{
		$info                  = new self();
		$info->id              = $jsonObject->id;
		$info->name            = $jsonObject->name;
		$info->nameWithOwner   = $jsonObject->nameWithOwner;
		$info->createdAt       = new DateTimeImmutable( $jsonObject->createdAt );
		$info->diskUsage       = (int)$jsonObject->diskUsage;
		$info->lastTag         = $jsonObject->refs->nodes ? $jsonObject->refs->nodes[0]->name : 'N/A';
		$info->primaryLanguage = $jsonObject->primaryLanguage->name ?? 'N/A';
		$info->color           = $jsonObject->primaryLanguage->color ?? '#ff0000';
		$info->countCommits    = (int)($jsonObject->ref->target->history->totalCount ?? 0);

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

	public function getNameWithOwner() : string
	{
		return $this->nameWithOwner;
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

	public function getPrimaryLanguage() : string
	{
		return $this->primaryLanguage;
	}

	public function getColor() : string
	{
		return $this->color;
	}

	public function getCountCommits() : int
	{
		return $this->countCommits;
	}
}