<?php declare(strict_types=1);

namespace Deployer;

require 'recipe/common.php';

// Project name
set( 'application', 'analyze-github.org' );

// Project repository
set( 'repository', 'git@github.com:hollodotme/analyze-github.org.git' );

// [Optional] Allocate tty for git clone. Default value is false.
set( 'git_tty', true );

// Shared files/dirs between deploys 
set(
	'shared_files',
	[

	]
);
set( 'shared_dirs', ['results/repositories'] );

// Writable dirs by web server 
set( 'writable_dirs', [] );
set( 'allow_anonymous_stats', false );

// Hosts

host( 'analyze-github.org' )
	->user( 'deploy' )
	->multiplexing( true )
	->forwardAgent( false )
	->addSshOption( 'UserKnownHostsFile', '/dev/null' )
	->addSshOption( 'StrictHostKeyChecking', 'no' )
	->set( 'deploy_path', '/var/www/analyze-github.org' );

// Tasks

desc( 'Deploy your project' );
task(
	'deploy',
	[
		'deploy:info',
		'deploy:prepare',
		'deploy:lock',
		'deploy:release',
		'deploy:update_code',
		'deploy:shared',
		'deploy:writable',
		'deploy:vendors',
		'deploy:clear_paths',
		'deploy:symlink',
		'deploy:unlock',
		'cleanup',
		'success',
	]
);

desc( 'Flush OPCache' );
task(
	'flush:opcache',
	function ()
	{
		/** @noinspection CommandExecutionAsSuperUserInspection */
		run( 'sudo service php7.2-fpm restart' );
	}
);

after( 'success', 'flush:opcache' );
after( 'deploy:failed', 'deploy:unlock' );
