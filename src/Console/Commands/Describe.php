<?php

namespace Ronanversendaal\MigrationDescriber\Console\Commands;

use Illuminate\Database\Console\Migrations\BaseCommand;
use Illuminate\Database\Migrations\Migrator;
use Symfony\Component\Console\Input\InputOption;

class Describe extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:describe';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reads a migration file and outputs the SQL to the console.
                                {--file : "The migration file to read."}
                                {--database : "The database connection to use."';


    /**
     * The migrator instance.
     *
     * @var \Illuminate\Database\Migrations\Migrator
     */
    protected $migrator;


    protected $migrations = [];

    protected $files = [];

    protected $paths;

    protected $select;

    /**
     * Create a new command instance.
     *
     * @param Migrator $migrator
     */
    public function __construct(Migrator $migrator)
    {
        parent::__construct();

        $this->getOptions();

        $this->migrator = $migrator;

    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->prepareDatabase();

        $this->prepareMigrations();

        $this->info("Migration Describer: \n");

        if($this->option('file')){

            // See if migrations folder is included.
            $file = $this->resolveFile();

            $filepath = $this->getMigrationPath() . '/'. $file;

            if(substr($file, -1) == '*'){
                // Wildcard. Grab everything.
                $files = glob($filepath);

                foreach($files as $path){
                    $this->migrations[] = str_replace('.php', '', $this->resolvePath($path));
                }

            } else {

                if(file_exists($filepath) && !is_dir($filepath)){
                    // Add to migrations;
                    $this->migrations[] = str_replace('.php', '', $file);
                }

            }

            // Can't find by file option.
            if(count($this->migrations) < 1){
                $this->warn('No migrations found with the --file option.');
            }

        }

        $this->pickMigration();

        $this->migrator->runMigrationList($this->migrations, true);

        foreach ($this->migrator->getNotes() as $note) {
            $this->output->writeln($note);
        }


    }
    /**
     * Prepare the migration database for running.
     *
     * @return void
     */
    protected function prepareDatabase()
    {
        $this->migrator->setConnection($this->option('database'));

        if (! $this->migrator->repositoryExists()) {
            $options = ['--database' => $this->option('database')];
            $this->call('migrate:install', $options);
        }
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        $this->addOption('database', config('app.database.default'), InputOption::VALUE_OPTIONAL, 'The database connection to use.');
        $this->addOption('file', null, InputOption::VALUE_OPTIONAL, 'The migration file to read. If none given, the user will be prompted with a list of migrations.');
    }

    private function prepareMigrations()
    {
        $this->paths = database_path('migrations');
        $this->files = $this->migrator->getMigrationFiles($this->paths);
        $this->select = $this->files;
    }

    private function pickMigration()
    {
        // Make a choice when no migration given
        if(count($this->migrations) == 0){

            // Reverse keys for last pick as default;
            end($this->select);

            $choice = $this->choice('Select migration to describe', $this->select, key($this->select));

            if(false !== $file = array_search($choice, $this->files)){
                $this->migrations[] = $this->files[$file];
            }
        }
    }

    private function resolveFile()
    {
        if(strpos( $this->option('file'), 'database/migrations') !== false){
            // Use full option.
            return str_replace('database/migrations/', '', $this->option('file'));
        } else{
            // return .
            return $this->option('file');
        }
    }

    private function resolvePath($path)
    {
        if(strpos( $path, $this->getMigrationPath()) !== false){
            // Use full option.
            return str_replace($this->getMigrationPath().'/', '', $path);
        } else{
            // return path.
            return $path;
        }
    }
}
