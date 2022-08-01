<?php

namespace AlwaysOpen\RequestLogger\Observers;

use AlwaysOpen\RequestLogger\Models\RequestLogBaseModel;

class RequestLogObserver
{
    public function saving(RequestLogBaseModel $model)
    {
        if (null === $model->occurred_at) {
            $model->occurred_at = now();
        }

        $model->body = $this->parsePossibleJson($model->body);
        $model->response = $this->parsePossibleJson($model->response);
    }

    protected function parsePossibleJson(null|array|string|object $possibleJson) : null|array|string|object
    {
        while (
            is_string($possibleJson)
            && ! empty($possibleJson)
            && ($parsed_json = json_decode($possibleJson, true)) !== null
        ) {
            $possibleJson = $parsed_json;
        }

        return $possibleJson;
    }
}
