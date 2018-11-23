<?php declare(strict_types=1);

namespace hollodotme\GitHub\OrgAnalyzer\Infrastructure\Adapters\Http;

final class HttpPostRequest extends AbstractHttpRequest
{
	public function getMethod() : string
	{
		return 'POST';
	}
}
