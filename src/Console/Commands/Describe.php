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

        $this->info("Migration Describer: \n");

        if($this->option('file')){

            if(strpos( $this->option('file'), 'database/migrations') !== false){
                $file = $this->option('file');
            } else{
                $file = $this->getMigrationPath() .'/'. $this->option('file');
            }

            if(file_exists($file)){
                $migration = $file;
            } else {
                $this->warn('Migration not found.');
                return;
            }

        } else {
            $paths = $this->getMigrationPaths();
            $files = $this->migrator->getMigrationFiles($paths);
            $select = array_keys($files);

            $choice = $this->choice('Select migration to describe', $select, key($select));

            $migration = $files[$choice];
        }

        $this->migrator->runMigrationList([$migration], ['pretend' => true]);

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
}
