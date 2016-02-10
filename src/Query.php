<?php

namespace Twingly;

/**
 * Twingly Search API Query
 *
 * @package Twingly
 */
class Query {
    /**
     * @var string the search query
     */
    public $pattern = '';
    /**
     * @var string language to restrict the query to
     */
    public $language = '';
    /**
     * @var Client the client that this query is connected to
     */
    public $client = null;
    /**
     * @var \DateTime search for posts published after this time (inclusive)
     */
    public $start_time = null;
    /**
     * @var \DateTime search for posts published before this time (inclusive)
     */
    public $end_time = null;

    /**
     * No need to call this method manually.
     *
     * @param Client $client
     */
    function __construct($client) {
        $this->client = $client;
    }

    /**
     * @return string the request url for the query
     */
    function url() {
        return sprintf("%s?%s", $this->client->endpoint_url(), $this->url_parameters());
    }

    /**
     * Executes the query and returns the result
     *
     * @return Result the result for this query
     */
    function execute() {
        return $this->client->execute_query($this);
    }

    /**
     * @return string the query part of the request url
     * @throws QueryException
     */
    function url_parameters() {
        return http_build_query($this->request_parameters());
    }

    /**
     * @return array the request parameters
     * @throws QueryException
     */
    function request_parameters() {
        if(empty($this->pattern)) {
            throw new \Twingly\QueryException("Missing pattern");
        }

        return [
            'key' => $this->client->api_key,
            'searchpattern' => $this->pattern,
            'documentlang' => $this->language,
            'ts' => $this->ts(),
            'tsTo' => $this->ts_to(),
            'xmloutputversion' => 2
        ];
    }

    private function ts() {
        if($this->start_time instanceof \DateTime) {
            return $this->start_time->format('Y-m-d H:i:s');
        } else {
            return '';
        }
    }

    private function ts_to() {
        if($this->end_time instanceof \DateTime) {
            return $this->end_time->format('Y-m-d H:i:s');
        } else {
            return '';
        }
    }
}
