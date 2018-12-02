<?php declare(strict_types=1);

namespace hollodotme\GitHub\OrgAnalyzer;

use hollodotme\GitHub\OrgAnalyzer\Infrastructure\TemplateRenderer\TemplateRenderer;
use Throwable;
use function error_reporting;
use function ini_set;
use const E_ALL;

error_reporting( E_ALL );
ini_set( 'display_errors', 'On' );

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
