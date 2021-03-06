<?php

namespace Twingly;

class Parser {
    /**
     * Parse an API response body.
     *
     * @param string $document containing an API response XML text
     * @return Result
     * @throws AuthException
     * @throws ServerException
     */
    public function parse($document) {
        $doc = simplexml_load_string($document);

        if($doc->getName() == 'html') {
            $this->handle_non_xml_document($doc);
        }

        if(isset($doc->operationResult)) {
            if((string)$doc->operationResult->attributes()->resultType == 'failure') {
                $this->handle_failure((string)$doc->operationResult);
            }
        }

        return $this->create_result($doc);
    }

    private function create_result($data_node) {
        $result = new Result();

        $result->number_of_matches_returned = (int)$data_node->attributes()->numberOfMatchesReturned;
        $result->seconds_elapsed = (float)$data_node->attributes()->secondsElapsed;
        $result->number_of_matches_total = (int)$data_node->attributes()->numberOfMatchesTotal;

        $result->posts = [];

        foreach($data_node->xpath('//post[@contentType="blog"]') as $p) {
            $result->posts[] = $this->parse_post($p);
        }

        return $result;
    }

    private function parse_post($element) {
        $post_params = [
            'tags' => []
        ];

        foreach($element->children() as $child) {
            if($child->getName() == 'tags') {
                $post_params[$child->getName()] = $this->parse_tags($child);
            } else {
                $post_params[$child->getName()] = (string)$child;
            }
        }

        $post = new Post();
        $post->set_values($post_params);
        return $post;
    }

    private function parse_tags($element) {
        $tags = [];
        foreach($element->xpath('child::tag') as $tag) {
            $tags[] = (string)$tag;
        }
        return $tags;
    }

    private function handle_failure($failure) {
        $ex = new \Twingly\Exception();
        $ex->from_api_response_message($failure);
    }

    private function handle_non_xml_document($document){
        $response_text = (string)$document->xpath('//text()')[0];
        throw new \Twingly\ServerException($response_text);
    }
}
