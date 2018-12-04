<?php declare(strict_types=1);

namespace hollodotme\GitHub\OrgAnalyzer\Application\Repositories\GitHub;

use DateTimeImmutable;
use DateTimeInterface;
use stdClass;

final class CommitHistoryItem
{
	/** @var DateTimeInterface */
	private $commitDate;

	/** @var CommitAuthor */
	private $commitAuthor;

	/**
	 * @param stdClass $jsonObject
	 *
	 * @throws \Exception
	 * @return CommitHistoryItem
	 */
	public static function fromJsonObject( stdClass $jsonObject ) : self
	{
		$item               = new self();
		$item->commitDate   = new DateTimeImmutable( $jsonObject->committedDate );
		$item->commitAuthor = CommitAuthor::fromJsonObject( $jsonObject->author );

		return $item;
	}

	public function getCommitDate() : DateTimeInterface
	{
		return $this->commitDate;
	}

	public function getCommitAuthor() : CommitAuthor
	{
		return $this->commitAuthor;
	}
}