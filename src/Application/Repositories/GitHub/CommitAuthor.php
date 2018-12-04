<?php declare(strict_types=1);

namespace hollodotme\GitHub\OrgAnalyzer\Application\Repositories\GitHub;

use stdClass;

final class CommitAuthor
{
	/** @var string */
	private $id;

	/** @var string */
	private $name;

	/** @var string */
	private $avatarUrl;

	private function __construct()
	{
	}

	public static function fromJsonObject( stdClass $jsonObject ) : self
	{
		$author            = new self();
		$author->id        = $jsonObject->user->id ?? $jsonObject->name;
		$author->name      = $jsonObject->name;
		$author->avatarUrl = $jsonObject->avatarUrl;

		return $author;
	}

	public function getId() : string
	{
		return $this->id;
	}

	public function getName() : string
	{
		return $this->name;
	}

	public function getAvatarUrl() : string
	{
		return $this->avatarUrl;
	}
}