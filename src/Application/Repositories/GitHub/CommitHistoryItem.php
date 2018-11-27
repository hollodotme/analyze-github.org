<?php declare(strict_types=1);

namespace hollodotme\GitHub\OrgAnalyzer\Application\Repositories\GitHub;

use DateTimeImmutable;
use DateTimeInterface;
use stdClass;

final class CommitHistoryItem
{
	/** @var string */
	private $commitUrl;

	/** @var DateTimeInterface */
	private $commitDate;

	/**
	 * @param stdClass $jsonObject
	 *
	 * @throws \Exception
	 * @return CommitHistoryItem
	 */
	public static function fromJsonObject( stdClass $jsonObject ) : self
	{
		$item             = new self();
		$item->commitUrl  = $jsonObject->commitUrl;
		$item->commitDate = new DateTimeImmutable( $jsonObject->committedDate );

		return $item;
	}

	public function getCommitUrl() : string
	{
		return $this->commitUrl;
	}

	public function getCommitDate() : DateTimeInterface
	{
		return $this->commitDate;
	}
}