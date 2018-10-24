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
     * QueryMessage constructor.
     * 
     * @param Collection $events
     */
    public function __construct(Collection $events)
    {
        $this->events = $events;
    }

    /**
     * @return string
     */
    public function logMessage()
    {
        $log = [];

        if ($this->events->isEmpty()) {
            return $log;
        }

        foreach ($this->events as $event) {
            if ($event instanceof QueryExecuted) {
                $log[] = $this->formatQueryExecuted($event);
            }

            if ($event instanceof TransactionBeginning) {
                $log[] = $this->formatTransactionBegin($event);
            }

            if ($event instanceof TransactionCommitted) {
                $log[] = $this->formatTransactionCommit($event);
            }

            if ($event instanceof  TransactionRolledBack) {
                $log[] = $this->formatTransactionRollback($event);
            }
        }

        return $log;
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

        return sprintf("%s [%s][%sms]", $sql, $event->connectionName, $event->time);
    }
}
