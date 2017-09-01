<?php
namespace Laranix\Auth\User\Token\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Database\Eloquent\Model;

class ClearExpiredTokens extends Command
{
    /**
     * @var \Illuminate\Contracts\Config\Repository
     */
    protected $config;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laranix:tokens:clear {models?*     : Models to delete from}
                                                 {--T|time=60  : Time in minutes to considered expired}';

    /**
     * Console command description
     *
     * @var string
     */
    protected $description = 'Clears expired tokens from database';

    /**
     * ClearExpiredTokens constructor.
     *
     * @param \Illuminate\Contracts\Config\Repository $config
     */
    public function __construct(Repository $config)
    {
        parent::__construct();

        $this->config = $config;
    }

    /**
     * Run the command
     */
    public function handle()
    {
        $models = $this->getModelArguments();

        if (empty($models)) {
            $this->error('No valid models provided');
            return;
        }

        foreach ($models as $arg) {
            $this->clearTokens(new $arg());
        }
    }

    /**
     * Clear tokens for given key
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     */
    protected function clearTokens(Model $model)
    {
        $model->newQuery()
              ->whereRaw("(TIMESTAMPDIFF(MINUTE, created_at, NOW()) > ?)",
                         $this->option('time'))
              ->delete();
    }

    /**
     * Get models to use
     *
     * @return array
     */
    protected function getModelArguments() : array
    {
        $models = $this->argument('models');

        if (empty($models)) {
            $models = [
                '\Laranix\Auth\Email\Verification\Verification',
                '\Laranix\Auth\Password\Reset\Reset'
            ];
        }

        return $this->validateArgs($models);
    }

    /**
     * Validate given arguments models
     *
     * @param array $models
     * @return array
     */
    protected function validateArgs(array $models) : array
    {
        $validModels = [];

        foreach ($models as $model) {
            if (!class_exists($model)) {
                $this->error('Class "' . $model . '"" does not exist');
                $this->line('');
            } elseif (!(new \ReflectionClass($model))->isInstantiable()) {
                $this->error('Class "' . $model . '" is not instantiable');
                $this->line('');
            } elseif (is_a($model, Model::class)) {
                $this->error('Class "' . $model . '" is not an instance of ' . Model::class);
                $this->line('');
            } else {
                $validModels[] = $model;
            }
        }

        return $validModels;
    }
}
