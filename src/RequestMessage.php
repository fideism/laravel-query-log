<?php

namespace Fideism\DatabaseLog;

use Illuminate\Foundation\Http\Events\RequestHandled;

class RequestMessage
{
    /**
     * @var RequestHandled
     */
    protected $event;

    /**
     * RequestMessage constructor.
     * @param RequestHandled $event
     */
    public function __construct(RequestHandled $event)
    {
        $this->event = $event;
    }

    /**
     * @return array
     */
    public function message()
    {
        $message = [
            'uri' => str_replace($this->event->request->root(), '', $this->event->request->fullUrl()) ?: '/',
            'method' => $this->event->request->method(),
            'controller_action' => optional($this->event->request->route())->getActionName(),
            //'middleware' => array_values(optional($this->event->request->route())->gatherMiddleware() ?? []),
            //'headers' => $this->headers($this->event->request->headers->all()),
            'response_status' => $this->event->response->getStatusCode(),
        ];

        return $message;
    }
}