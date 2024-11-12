<?php

namespace Clubdeuce\Tessitura\Resources;


use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Class Seasons
 * @package DSO\Modules\Tessitura
 *
 * @link https://www.tessituranetwork.com/REST_v151/TessituraService/HELP/RESOURCES/SEASONS.HTM
 */
class Seasons
{

    const RESOURCE = 'ReferenceData/Seasons';

    protected Client $client;

    public function __construct(Client $client = null)
    {
        if (empty ($client)) {
            $client = new Client();
        }

        $this->client = $client;
    }

    /**
     * @link https://www.tessituranetwork.com/REST_v151/TessituraService/HELP/API/GET_REFERENCEDATA_SEASONS_ID_FI.HTM
     * @throws Exception
     */
    public function getById(int $id): Season
    {
        try {
            $response = $this->client->request('get', sprintf('%s/%s', self::RESOURCE, $id));
            return new Season(json_decode($response->getBody()->getContents(), 'associative array'));
        } catch (GuzzleException $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @return Season[]
     * @throws Exception
     */
    public function get(): array
    {
        try {
            $response = $this->client->get(self::RESOURCE);
            $data     = json_decode($response->getBody()->getContents(), true);

            return array_map( function(array $season) {
                return new Season($season);
            }, $data);
        } catch (GuzzleException $e) {
            throw new Exception($e->getMessage());
        }
    }
}
