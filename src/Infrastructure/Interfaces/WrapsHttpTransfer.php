<?php declare(strict_types=1);

namespace hollodotme\GitHub\OrgAnalyzer\Infrastructure\Interfaces;

interface WrapsHttpTransfer
{
	public function send( ProvidesRequestData $request ) : ProvidesResponseData;
}
