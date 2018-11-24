<?php declare(strict_types=1);

namespace hollodotme\GitHub\OrgAnalyzer\Infrastructure\TemplateRenderer;

use function htmlentities;
use const ENT_QUOTES;

final class TemplateContainer
{
	/** @var string */
	private $templateFile;

	/** @var array */
	private $data;

	public function __construct( string $templateFile, array $data )
	{
		$this->templateFile = $templateFile;
		$this->data         = $data;
	}

	/** @noinspection MagicMethodsValidityInspection */
	public function __get( string $name )
	{
		return htmlentities( $this->data[ $name ] ?? '', ENT_QUOTES );
	}

	public function getContent() : string
	{
		ob_start();

		/** @noinspection PhpIncludeInspection */
		require $this->templateFile;

		return (string)ob_get_clean();
	}
}