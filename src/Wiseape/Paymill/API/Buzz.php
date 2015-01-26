<?php

/**
 * @copyright wiseape GmbH
 * @author Ruben Rögels
 * @license LGPL-3.0+
 */

namespace Wiseape\Paymill\API;

use Buzz\Client\ClientInterface;
use Buzz\Client\Curl;
use Buzz\Message\Request;
use Buzz\Message\Response;
use Buzz\Message\Form\FormRequest;
use Paymill\API\CommunicationAbstract;


class Buzz extends CommunicationAbstract {
    
    /**
     * @var string
     */
    private $apiKey;
    
    /**
     * @var string
     */
    private $apiEndpoint;
    
    /**
     * @var ClientInterface
     */
    protected $client;

    /**
     * @param string $apiKey
     * @param ClientInterface $client
     */
    public function __construct($apiKey, ClientInterface $client = null) {
        $this->apiKey = $apiKey;
        $this->client = $client ?: new Curl;
        $this->apiEndpoint = 'https://api.paymill.com/v2.1/';
    }

    /**
     * @param string $action
     * @param array $params
     * @param string $method
     * @return array
     */
    public function requestApi($action = '', $params = array(), $method = 'POST') {
        if('POST' == $method) {
            $request = new FormRequest;
            $request->addFields($params);
        } else {
            $request = new Request;
            $params && $action .= '?' . http_build_query($params);
        }

        $request->setMethod($method);
        $request->setResource($this->apiEndpoint . $action);
        $request->addHeader('Authorization: Basic ' . base64_encode($this->apiKey . ':'));

        $response = new Response;
        $this->client->send($request, $response);

        $contentType = $response->getHeader('Content-Type');
        $content = $response->getContent();

        if ('application/json' == $contentType) {
            $content = json_decode($content, true);

        // TODO content is a string?
        } elseif('text/csv' == $contentType && !isset($content['error'])) {
            return $content;
        }

        return array(
            'header' => array(
                'status' => $response->getStatusCode(),
                'reason' => null,
            ),
            'body' => $content,
        );
    }
    
    /**
     * @param string $apiEndpoint
     * @return void
     */
    public function setAPIEndpoint($apiEndpoint) {
        $this->apiEndpoint = $apiEndpoint;
    }

}
