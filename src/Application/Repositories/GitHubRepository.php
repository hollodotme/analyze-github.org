<?php declare(strict_types=1);

namespace hollodotme\GitHub\OrgAnalyzer\Application\Repositories;

use Generator;
use hollodotme\GitHub\OrgAnalyzer\Application\Interfaces\ProvidesOrganizationInfo;
use hollodotme\GitHub\OrgAnalyzer\Application\Repositories\GitHub\CommitHistoryItem;
use hollodotme\GitHub\OrgAnalyzer\Application\Repositories\GitHub\RepositoryInfo;
use hollodotme\GitHub\OrgAnalyzer\Exceptions\RuntimeException;
use hollodotme\GitHub\OrgAnalyzer\Infrastructure\Adapters\GitHub\Exceptions\GitHubApiRequestFailed;
use hollodotme\GitHub\OrgAnalyzer\Infrastructure\Interfaces\ProvidesGitHubData;

final class GitHubRepository
{
	/** @var ProvidesOrganizationInfo */
	private $orgConfig;

	/** @var ProvidesGitHubData */
	private $gitHubAdapter;

	/**
	 * @param ProvidesOrganizationInfo $orgInfo
	 * @param ProvidesGitHubData       $gitHubAdapter
	 */
	public function __construct( ProvidesOrganizationInfo $orgInfo, ProvidesGitHubData $gitHubAdapter )
	{
		$this->orgConfig     = $orgInfo;
		$this->gitHubAdapter = $gitHubAdapter;
	}

	/**
	 * @throws GitHubApiRequestFailed
	 * @throws RuntimeException
	 * @throws \Exception
	 * @return Generator|RepositoryInfo[]
	 */
	public function getRepositoryInfos() : Generator
	{
		$queryTemplate = '
			{
			  organization(login: "%s") {
			    repositories(orderBy: {field: CREATED_AT, direction: ASC}, %sfirst: 100) {
			      pageInfo {
			        endCursor
			        hasNextPage
			      }
			      nodes {
			        refs(refPrefix: "refs/tags/",last: 1) {
			          nodes {
			            name
			          }
			        }
			        ref(qualifiedName: "master") {
			          target {
			            ... on Commit {
			              history(since: "2010-01-01T00:00:00") {
			                totalCount
			              }
			            }
			          }
			        }
			        id
			        name
			        diskUsage
			        createdAt
			        primaryLanguage {
			          name
			          color
			        }
			      }
			    }
			  }
			}
		';

		$endCursor     = '';
		$countApiCalls = 0;
		do
		{
			$graphQuery = sprintf(
				$queryTemplate,
				$this->orgConfig->getOrganizationName(),
				'' !== $endCursor ? "after: \"{$endCursor}\", " : ''
			);

			$query = json_encode( ['query' => $graphQuery] );

			if ( false === $query )
			{
				throw new RuntimeException( 'Could not encode json.' );
			}

			$data = $this->gitHubAdapter->executeQuery( $query );

			foreach ( $data->organization->repositories->nodes as $repositoryInfo )
			{
				yield RepositoryInfo::fromJsonObject( $repositoryInfo );
			}

			$endCursor = $data->organization->repositories->pageInfo->endCursor;

			$countApiCalls++;
		}
		while ( $data->organization->repositories->pageInfo->hasNextPage );

		/** @noinspection SuspiciousReturnInspection */
		return $countApiCalls;
	}

	/**
	 * @param string $repository
	 * @param int    $apiCalls
	 *
	 * @throws GitHubApiRequestFailed
	 * @throws RuntimeException
	 * @throws \Exception
	 * @return CommitHistoryItem
	 */
	public function getFirstCommitDate( string $repository, int &$apiCalls ) : CommitHistoryItem
	{
		$queryTemplate = '
			{ 
			  repository(owner: "%s", name: "%s") {
			    ref(qualifiedName: "master") {
			      target {
			        ... on Commit {
			          history(first: 100%s) {
			            pageInfo {
			              endCursor
			              hasNextPage
			            }
			            nodes {
			              commitUrl
			              committedDate
			            }
			          }
			        }
			      }
			    }
			  }
			}
		';

		$endCursor   = '';
		$apiCalls    = 0;
		$firstCommit = null;

		do
		{
			$graphQuery = sprintf(
				$queryTemplate,
				$this->orgConfig->getOrganizationName(),
				$repository,
				'' !== $endCursor ? ", after: \"{$endCursor}\"" : ''
			);

			$query = json_encode( ['query' => $graphQuery] );

			if ( false === $query )
			{
				throw new RuntimeException( 'Could not encode json.' );
			}

			$data = $this->gitHubAdapter->executeQuery( $query );

			if ( null === $data->repository->ref )
			{
				throw new RuntimeException( 'Could not get commit nodes.' );
			}

			$nodes      = (array)$data->repository->ref->target->history->nodes;
			$jsonObject = end( $nodes );

			$firstCommit = CommitHistoryItem::fromJsonObject( $jsonObject );

			$endCursor = $data->repository->ref->target->history->pageInfo->endCursor;

			$apiCalls++;
		}
		while ( $data->repository->ref->target->history->pageInfo->hasNextPage );

		return $firstCommit;
	}
}