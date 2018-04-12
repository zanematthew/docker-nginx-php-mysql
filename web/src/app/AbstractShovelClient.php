<?php

namespace App;

use Goutte\Client as GoutteClient;

abstract class AbstractShovelClient
{
    protected $url;

    protected $client;

    protected $crawler;

    public function __construct($url)
    {
        $this->url = $url;
    }

    public function getClient()
    {
        if (! $this->client) {
            $this->client = new GoutteClient();
        }

        return $this->client;
    }

    public function setClient(GoutteClient $client)
    {
        $this->client = $client;
    }

    public function getCrawler()
    {
        if (! $this->crawler) {
            $this->crawler = $this->getClient()->request('GET', $this->url);
        }
        return $this->crawler;
    }

    public function filter($selector)
    {
        return $this->getCrawler()->filter($selector);
    }

    public function getHttpResponse()
    {
        $this->getClient()->request('GET', $this->url);
        $response = $this->getClient()->getResponse();
        return $response->getStatus();
    }
}
