<?php

namespace Fideism\DatabaseLog;

use Monolog\Logger as Monolog;
use Illuminate\Support\Collection;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\RotatingFileHandler;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Database\Events\TransactionBeginning;
use Illuminate\Database\Events\TransactionCommitted;
use Illuminate\Database\Events\TransactionRolledBack;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/database.php', 'database-log');

        if ($this->dbDebug()) {
            $this->app->singleton('database.log', function () {
                return $this->createLog();
            });

            $this->databaseEvents();

            $this->listenEvents();

            $this->terminate();
        }

        if ($this->dbRequest()) {
            $this->app['events']->listen(RequestHandled::class, [$this, 'recordRequest']);
        }
    }

    /**
     * App Terminate Log
     */
    protected function terminate()
    {
        // App::finish Knowing that it is old
        // Can be used anywhere you can access the $app instance
        $this->app->terminating(function () {
            $this->logQuery();
        });
    }

    /**
     * @param RequestHandled $event
     */
    public function recordRequest(RequestHandled $event)
    {
        $message = (new RequestMessage($event))->message();

        $this->app['database.log']->log($this->level(), var_export($message, true));
    }

    /**
     * Log Query
     */
    protected function logQuery()
    {
        $message = new QueryMessage($this->app['database.events'], $this->dbExplain());

        $logs = $message->logMessage();

        if (empty($logs)) {
            return;
        }

        $this->app['database.log']->log($this->level(), "\n" . implode("\n", $logs));
    }

    /**
     * Log Debug
     *
     * @return mixed
     */
    protected function dbDebug()
    {
        return $this->app['config']['database-log']['debug'];
    }

    /**
     * Log Explain
     * 
     * @return mixed
     */
    protected function dbExplain()
    {
        return $this->app['config']['database-log']['explain'];
    }

    /**
     * Log Explain
     *
     * @return mixed
     */
    protected function dbRequest()
    {
        return $this->app['config']['database-log']['request'];
    }

    /**
     * Set Database Events
     */
    protected function databaseEvents()
    {
        $this->app->singleton('database.events', function () {
            return Collection::make();
        });
    }

    /**
     * Listen Database Events
     */
    protected function listenEvents()
    {
        foreach ($this->getEvents() as $event) {
            $this->app['events']->listen($event, function ($event) {
                $this->app['database.events']->push($event);
            });
        }
    }

    /**
     * @return array
     */
    protected function getEvents()
    {
        return [
            QueryExecuted::class,
            TransactionBeginning::class,
            TransactionCommitted::class,
            TransactionRolledBack::class
        ];
    }

    /**
     * @return mixed
     *
     * @throws DatabaseLogException
     */
    protected function createLog()
    {
        $config = $this->getConfig();

        $driverMethod = ucfirst($config['channel']) . 'Driver';
        if (! method_exists($this, $driverMethod)) {
            throw new DatabaseLogException('method not exists');
        }

        $driver = $this->{$driverMethod}($config);

        return $driver;
    }

    /**
     * Make Daily Driver
     *
     * @param array $config
     *
     * @return Monolog
     */
    protected function dailyDriver(array $config)
    {
        return new Monolog($this->parseName(), [
            $this->prepareHandler(new RotatingFileHandler(
                $config['log'], $config['days'] ?? 7, $this->level()
            )),
        ]);
    }

    /**
     * Make Single Driver
     *
     * @param array $config
     *
     * @return Monolog
     */
    protected function singleDriver(array $config)
    {
        return new Monolog($this->parseName(), [
            $this->prepareHandler(
                new StreamHandler($config['log'], $this->level())
            ),
        ]);
    }

    /**
     * Get Log Level
     *
     * @param array $config
     *
     * @return mixed|string
     */
    protected function level()
    {
        $config = $this->getConfig();

        return $config['level'] ?? 'debug';
    }

    /**
     * Get Log Name
     *
     * @param array $config
     *
     * @return mixed|string
     */
    protected function parseName()
    {
        $config = $this->getConfig();

        return $config['name'] ?? 'database.sql';
    }

    /**
     * Get Log Config
     *
     * @return mixed
     */
    protected function getConfig()
    {
        return $this->app['config']['database-log'];
    }

    /**
     * Prepare the handler for usage by Monolog.
     *
     * @param \Monolog\Handler\HandlerInterface $handler
     *
     * @return \Monolog\Handler\HandlerInterface
     */
    protected function prepareHandler(HandlerInterface $handler)
    {
        return $handler->setFormatter($this->formatter());
    }

    /**
     * Get a Monolog formatter instance.
     *
     * @return \Monolog\Formatter\FormatterInterface
     */
    protected function formatter()
    {
        $formatter = new LineFormatter(null, null, true, true);
        $formatter->includeStacktraces();

        return $formatter;
    }
}
