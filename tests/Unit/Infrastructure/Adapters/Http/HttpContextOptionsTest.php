<?php declare(strict_types=1);

namespace hollodotme\GitHub\OrgAnalyzer\Tests\Unit\Infrastructure\Adapters\Http;

use hollodotme\GitHub\OrgAnalyzer\Infrastructure\Adapters\Http\HttpContextOptions;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

final class HttpContextOptionsTest extends TestCase
{
	/**
	 * @throws ExpectationFailedException
	 * @throws InvalidArgumentException
	 */
	public function testCanGetDefaults() : void
	{
		$contextOptions = new HttpContextOptions( [] );

		$expectedDefaults = [
			'http' => [
				'protocol_version' => 1.1,
				'user_agent'       => 'hollodotme/GitHub-Org-Analyzer',
				'timeout'          => 30,
				'ignore_errors'    => true,
			],
		];

		$this->assertSame( $expectedDefaults, $contextOptions->getOptions() );
	}

	/**
	 * @param array $options
	 * @param array $expectedOptions
	 *
	 * @throws ExpectationFailedException
	 * @throws InvalidArgumentException
	 * @dataProvider optionsProvider
	 */
	public function testCanOverwriteAndExtendDefaults( array $options, array $expectedOptions ) : void
	{
		$contextOptions = new HttpContextOptions( $options );

		$this->assertSame( $expectedOptions, $contextOptions->getOptions() );
	}

	public function optionsProvider() : array
	{
		return [
			# Replace default options
			[
				'options'         => [
					'http' => [
						'timeout' => 10,
					],
				],
				'expectedOptions' => [
					'http' => [
						'protocol_version' => 1.1,
						'user_agent'       => 'hollodotme/GitHub-Org-Analyzer',
						'timeout'          => 10,
						'ignore_errors'    => true,
					],
				],
			],
			# Extend options
			[
				'options'         => [
					'ssl' => [
						'verify_peer' => true,
					],
				],
				'expectedOptions' => [
					'http' => [
						'protocol_version' => 1.1,
						'user_agent'       => 'hollodotme/GitHub-Org-Analyzer',
						'timeout'          => 30,
						'ignore_errors'    => true,
					],
					'ssl'  => [
						'verify_peer' => true,
					],
				],
			],
			# Overwrite and extend options
			[
				'options'         => [
					'http' => [
						'timeout' => 10,
					],
					'ssl'  => [
						'verify_peer' => true,
					],
				],
				'expectedOptions' => [
					'http' => [
						'protocol_version' => 1.1,
						'user_agent'       => 'hollodotme/GitHub-Org-Analyzer',
						'timeout'          => 10,
						'ignore_errors'    => true,
					],
					'ssl'  => [
						'verify_peer' => true,
					],
				],
			],
		];
	}
}
