<?php declare(strict_types=1);

namespace hollodotme\GitHub\OrgAnalyzer\Infrastructure\Interfaces;

interface RendersTemplate
{
	public function renderWithData( string $template, array $data ) : string;
}