<?php

namespace Fideism\DatabaseLog;

use Illuminate\Support\Collection;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Database\Events\TransactionBeginning;
use Illuminate\Database\Events\TransactionCommitted;
use Illuminate\Database\Events\TransactionRolledBack;

class QueryMessage
{
    /**
     * @var Collection
     */
    protected $events;

    /**
     * @var array
     */
    private $logs = [];

    /**
     * @var bool
     */
    private $explain;

    /**
     * QueryMessage constructor.
     *
     * @param Collection $events
     * @param bool $explain
     */
    public function __construct(Collection $events, bool $explain = false)
    {
        $this->events = $events;
        $this->explain = $explain;
    }

    /**
     * @return string
     */
    public function logMessage()
    {
        if ($this->events->isEmpty()) {
            return $this->logs;
        }

        foreach ($this->events as $event) {
            if ($event instanceof QueryExecuted) {
                $this->formatQueryExecuted($event);
            }

            if ($event instanceof TransactionBeginning) {
                $this->logs[] = $this->formatTransactionBegin($event);
            }

            if ($event instanceof TransactionCommitted) {
                $this->logs[] = $this->formatTransactionCommit($event);
            }

            if ($event instanceof  TransactionRolledBack) {
                $this->logs[] = $this->formatTransactionRollback($event);
            }
        }

        return $this->logs;
    }

    /**
     * @param TransactionCommitted $event
     * @return string
     */
    protected function formatTransactionCommit(TransactionCommitted $event)
    {
        return sprintf("[Transaction commit] [%s]", $event->connectionName);
    }

    /**
     * @param TransactionRolledBack $event
     * @return string
     */
    protected function formatTransactionRollback(TransactionRolledBack $event)
    {
        return sprintf("[Transaction rollback] [%s]", $event->connectionName);
    }

    /**
     * @param TransactionBeginning $event
     * @return string
     */
    protected function formatTransactionBegin(TransactionBeginning $event)
    {
        return sprintf("[Transaction begin] [%s]", $event->connectionName);
    }

    /**
     * Query
     *
     * @param QueryExecuted $event
     * @return string
     */
    protected function formatQueryExecuted(QueryExecuted $event)
    {
        $sql = $event->sql;

        foreach ($event->bindings as $key => $value) {
            $value = sprintf("'%s'", str_replace("'", "\'", $value));

            if (is_int($key)) {
                $index = strpos($sql, '?');

                if ($index === false) {
                    continue;
                }

                $sql = substr_replace($sql, $value, $index, 1);

            } else {
                if (strpos($sql, sprintf(':%s', $key)) !== false) {
                    $sql = str_replace(sprintf(':%s', $key), $value, $sql);
                } else {
                    $sql = str_replace($key, $value, $sql);
                }
            }
        }

        $this->logs[] = sprintf("%s [%s][%sms]", $sql, $event->connectionName, $event->time);

        $this->selectExplain($event, $sql);
    }

    /**
     * explain select sql
     *
     * @param QueryExecuted $event
     * @param string $sql
     */
    protected function selectExplain(QueryExecuted $event, string $sql)
    {
        if (! $this->explain) {
            return;
        }

        $pos = mb_stripos($sql, 'SELECT', 0);
        if ($pos === false || $pos != 0) {
            return;
        }

        $explain = $event->connection->select('EXPLAIN ' . $sql);

        $this->logs[] = 'EXPLAIN:';
        foreach ($explain as $item) {
            $message = get_object_vars($item);
            $this->logs[] = json_encode($message);
        }
    }
}
