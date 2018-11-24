<?php declare(strict_types=1);

namespace hollodotme\GitHub\OrgAnalyzer\Infrastructure\TemplateRenderer;

use hollodotme\GitHub\OrgAnalyzer\Infrastructure\Interfaces\RendersTemplate;
use hollodotme\GitHub\OrgAnalyzer\Infrastructure\TemplateRenderer\Exceptions\TemplateNotFoundException;
use function file_exists;

final class TemplateRenderer implements RendersTemplate
{
	/** @var array */
	private $searchPaths;

	public function __construct( array $searchPaths )
	{
		$this->searchPaths = $searchPaths;
	}

	/**
	 * @param string $template
	 * @param array  $data
	 *
	 * @throws TemplateNotFoundException
	 * @return string
	 */
	public function renderWithData( string $template, array $data ) : string
	{
		$templateFile = $this->findTemplateFile( $template );

		return (new TemplateContainer( $templateFile, $data ))->getContent();
	}

	/**
	 * @param string $template
	 *
	 * @throws TemplateNotFoundException
	 * @return string
	 */
	private function findTemplateFile( string $template ) : string
	{
		foreach ( $this->searchPaths as $searchPath )
		{
			$path = $searchPath . '/' . $template;
			if ( file_exists( $path ) )
			{
				return $path;
			}
		}

		throw new TemplateNotFoundException( 'Could not find template file: ' . $template );
	}
}