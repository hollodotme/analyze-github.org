<?php declare(strict_types=1);

namespace hollodotme\GitHub\OrgAnalyzer\Tests\Unit\Infrastructure\TemplateRenderer;

use hollodotme\GitHub\OrgAnalyzer\Infrastructure\TemplateRenderer\TemplateRenderer;
use PHPUnit\Framework\TestCase;

final class TemplateRendererTest extends TestCase
{
	/**
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 * @throws \hollodotme\GitHub\OrgAnalyzer\Infrastructure\TemplateRenderer\Exceptions\TemplateNotFoundException
	 */
	public function testCanRenderTemplateWithData() : void
	{
		$searchPaths     = [__DIR__ . '/_files'];
		$template        = 'testTemplate.phtml';
		$data            = ['title' => 'Unit-Test', 'body' => 'Just Testing'];
		$expectedContent = <<<EOF
<html lang="en">
<head><title>Unit-Test</title></head>
<body>Just Testing</body>
</html>

EOF;

		$content = (new TemplateRenderer( $searchPaths ))
			->renderWithData( $template, $data );

		$this->assertSame( $expectedContent, $content );
	}
}
