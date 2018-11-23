<?php declare(strict_types=1);

namespace hollodotme\GitHub\OrgAnalyzer\Infrastructure\Interfaces;

interface WrapsRemoteTransfer
{
	public function send( ProvidesRequestData $request ) : ProvidesResponseData;
}
