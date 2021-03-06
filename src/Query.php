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
     * No need to call this method manually, instead use Client->query().
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
     * @throws QueryException if no search pattern has been set.
     * @throws AuthException if the API couldn't authenticate you. Make sure your API key is correct.
     * @throws ServerException if the query could not be executed due to a server error.
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
            'ts' => $this->datetime_to_utc_string($this->start_time),
            'tsTo' => $this->datetime_to_utc_string($this->end_time),
            'xmloutputversion' => 2
        ];
    }

    private function datetime_to_utc_string($datetime) {
        if($datetime instanceof \DateTime) {
            $datetime_copy = clone $datetime;
            $datetime_copy->setTimezone(new \DateTimeZone('UTC'));
            return $datetime_copy->format('Y-m-d H:i:s');
        } else {
            return '';
        }
    }
}
