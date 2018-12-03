<?php declare(strict_types=1);

namespace hollodotme\GitHub\OrgAnalyzer\Infrastructure\Adapters\GitHub;

use hollodotme\GitHub\OrgAnalyzer\Infrastructure\Adapters\GitHub\Exceptions\GitHubApiRequestFailed;
use hollodotme\GitHub\OrgAnalyzer\Infrastructure\Adapters\Http\HttpPostRequest;
use hollodotme\GitHub\OrgAnalyzer\Infrastructure\Interfaces\ConfiguresGitHubAdapter;
use hollodotme\GitHub\OrgAnalyzer\Infrastructure\Interfaces\ProvidesGitHubData;
use hollodotme\GitHub\OrgAnalyzer\Infrastructure\Interfaces\ProvidesResponseData;
use hollodotme\GitHub\OrgAnalyzer\Infrastructure\Interfaces\WrapsHttpTransfer;
use stdClass;
use function implode;
use function in_array;
use function is_array;

final class GitHubAdapter implements ProvidesGitHubData
{
	private const MINIMUM_OAUTH_SCOPES = [
		'public_repo',
		'repo',
	];

	/** @var ConfiguresGitHubAdapter */
	private $gitHubConfig;

	/** @var WrapsHttpTransfer */
	private $httpAdapter;

	public function __construct( ConfiguresGitHubAdapter $gitHubConfig, WrapsHttpTransfer $httpAdapter )
	{
		$this->gitHubConfig = $gitHubConfig;
		$this->httpAdapter  = $httpAdapter;
	}

	/**
	 * @param string $query
	 *
	 * @throws GitHubApiRequestFailed
	 * @return stdClass
	 */
	public function executeQuery( string $query ) : stdClass
	{
		$postRequest = new HttpPostRequest( $this->gitHubConfig->getApiUrl() );
		$postRequest->setBearerToken( $this->gitHubConfig->getPersonalAccessToken() );

		$postRequest->setBody( $query );

		$response = $this->httpAdapter->send( $postRequest );

		$this->guardRequestSucceeded( $response->getStatus() );
		$this->guardMinimumOauthScopesFulfilled( $response );

		$jsonResult = json_decode( $response->getBody() );

		$this->guardResponseHasData( $jsonResult );

		return $jsonResult->data;
	}

	/**
	 * @param string $responseStatus
	 *
	 * @throws GitHubApiRequestFailed
	 */
	private function guardRequestSucceeded( string $responseStatus ) : void
	{
		if ( $responseStatus !== '200 OK' )
		{
			throw new GitHubApiRequestFailed( 'GitHub API request failed: ' . $responseStatus );
		}
	}

	/**
	 * @param ProvidesResponseData $response
	 *
	 * @throws GitHubApiRequestFailed
	 */
	private function guardMinimumOauthScopesFulfilled( ProvidesResponseData $response ) : void
	{
		if ( !$response->hasHeader( 'X-OAuth-Scopes' ) )
		{
			throw new GitHubApiRequestFailed( 'No OAuth scopes received.' );
		}

		if ( !in_array( $response->getHeader( 'X-OAuth-Scopes' ), self::MINIMUM_OAUTH_SCOPES, true ) )
		{
			throw new GitHubApiRequestFailed(
				'Minimum OAuth scopes not fulfilled. Required: '
				. implode( ', ', self::MINIMUM_OAUTH_SCOPES )
			);
		}
	}

	/**
	 * @param stdClass $jsonResult
	 *
	 * @throws GitHubApiRequestFailed
	 */
	private function guardResponseHasData( stdClass $jsonResult ) : void
	{
		if ( $jsonResult->data === null && is_array( $jsonResult->errors ) )
		{
			throw new GitHubApiRequestFailed( 'GitHub API request failed: ' . $jsonResult->errors[0]->message );
		}
	}
}