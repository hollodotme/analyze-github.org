<?php declare(strict_types=1);

namespace hollodotme\GitHub\OrgAnalyzer\Infrastructure\Adapters\GitHub;

use hollodotme\GitHub\OrgAnalyzer\Infrastructure\Adapters\GitHub\Exceptions\GitHubApiRequestFailed;
use hollodotme\GitHub\OrgAnalyzer\Infrastructure\Adapters\Http\HttpPostRequest;
use hollodotme\GitHub\OrgAnalyzer\Infrastructure\Interfaces\ConfiguresGitHubAdapter;
use hollodotme\GitHub\OrgAnalyzer\Infrastructure\Interfaces\ProvidesGitHubData;
use hollodotme\GitHub\OrgAnalyzer\Infrastructure\Interfaces\WrapsHttpTransfer;
use stdClass;
use function is_array;

final class GitHubAdapter implements ProvidesGitHubData
{
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

		if ( $response->getStatus() !== '200 OK' )
		{
			throw new GitHubApiRequestFailed( 'GitHub API request failed: ' . $response->getStatus() );
		}

		$result = $response->getBody();

		$jsonResult = json_decode( $result );

		if ( $jsonResult->data === null && is_array( $jsonResult->errors ) )
		{
			throw new GitHubApiRequestFailed( 'GitHub API request failed: ' . $jsonResult->errors[0]->message );
		}

		return $jsonResult->data;
	}
}