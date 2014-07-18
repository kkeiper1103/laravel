<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class InstallCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'kkeiper1103:install';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Sets up the necessary stuff for Sentry, Debugbar, AssetPipeline, etc';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		// run the other artisan tasks that are necessary
		
		// check if installed
		$lockFile = app_path() . DIRECTORY_SEPARATOR . "start" . DIRECTORY_SEPARATOR . "install.lock";
		if( file_exists($lockFile) )
		{
		    $this->info("Install Has been Previously Completed. Please delete 'app/start/install.lock' if you really would like to run this again.");
		    exit;
		}
		
		// check if we have a database connection
		try
		{
		  DB::connection()->getDatabaseName();
		}
		catch(Exception $e)
		{
		  $this->error("Cannot Connect to Database. To Install Sentry, Please Configure the Database and re-run this task.");
		  exit;
		}
		
		// setup asset pipeline folders
		$this->call("assets:setup", array());
		
		// publish the config for Debugbar
		$this->call("config:publish", array("barryvdh/laravel-debugbar", "--path" => app_path() ));
		$this->call("debugbar:publish");
		
		// migrate Sentry tables
		$this->call("migrate", array( "--package" => "cartalyst/sentry" ));
		
		// publish sentry config
		$this->call("config:publish", array("cartalyst/sentry", "--path" => app_path()));
		
		// figure out local dev environment and add that to the detectEnvironment
		$machineName = exec("hostname");
		$configPath = base_path() . DIRECTORY_SEPARATOR . "bootstrap" . DIRECTORY_SEPARATOR . "start.php";
		
		$startConfig = file_get_contents( $configPath );
		$startConfig = str_replace( "'local' => array('homestead'),", "'local' => array('homestead', '{$machineName}'),", $startConfig );
		$bytesWritten = file_put_contents( $configPath, $startConfig );
		$this->info( "Wrote to /bootstrap/start.php File: " . $bytesWritten );
		
		// finally, we need to set some flag that means installation has happened, so no rerunning this task
		
		touch( $lockFile );
		$this->info( "Installation of Framework has Completed. Please Enjoy Developing the next Greatest Application!" );
		
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array(
			
		);
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return array(
			
		);
	}

}
