<?php declare(strict_types=1);

namespace hollodotme\GitHub\OrgAnalyzer;

use hollodotme\GitHub\OrgAnalyzer\Infrastructure\TemplateRenderer\TemplateRenderer;
use Throwable;

require_once __DIR__ . '/../vendor/autoload.php';

try
{
	$renderer = new TemplateRenderer( [__DIR__ . '/../src/Application/Web/Templates'] );
	echo $renderer->renderWithData( 'HomePage.phtml', [] );
}
catch ( Throwable $e )
{
	die( $e->getMessage() );
}
