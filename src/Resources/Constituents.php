<?php

namespace Clubdeuce\Tessitura\Resources;

use Clubdeuce\Tessitura\Base\Base;
use Clubdeuce\Tessitura\Interfaces\ApiInterface;
use Clubdeuce\Tessitura\Interfaces\ResourceInterface;

class Constituents extends Base implements ResourceInterface
{
    public const RESOURCE = 'CRM/Constituents';

    protected ApiInterface $_api;
    protected OriginalSources $_originalSources;
    protected ConstituentTypes $_constituentTypes;
    protected AddressTypes $_addressTypes;
    protected ElectronicAddressTypes $_electronicAddressTypes;

    public function __construct(
        ApiInterface $api,
        ?OriginalSources $originalSources = null,
        ?ConstituentTypes $constituentTypes = null,
        ?AddressTypes $addressTypes = null,
        ?ElectronicAddressTypes $electronicAddressTypes = null
    ) {
        $this->_api                    = $api;
        $this->_originalSources        = $originalSources ?? new OriginalSources($api);
        $this->_constituentTypes       = $constituentTypes ?? new ConstituentTypes($api);
        $this->_addressTypes           = $addressTypes ?? new AddressTypes($api);
        $this->_electronicAddressTypes = $electronicAddressTypes ?? new ElectronicAddressTypes($api);
        parent::__construct();
    }

    /**
     * Create a Tessitura constituent from the supplied data.
     *
     * @param  mixed[] $data {
     *     @type string $first_name
     *     @type string $last_name
     *     @type string $email
     *     @type int    $original_source_id
     *     @type int    $constituent_type_id
     *     @type int    $electronic_address_type_id
     *     @type string $street1
     *     @type string $city
     *     @type string $postal_code
     * }
     * @return int The new constituent ID
     * @throws \Exception On API failure or when the response contains no Id.
     */
    public function create(array $data): int
    {
        $source          = $this->_originalSources->sourceById((int)$data['original_source_id']);
        $constituentType = $this->_constituentTypes->getById((int)$data['constituent_type_id']);
        $addressType     = $this->_addressTypes->getById(3);
        $electronicType  = $this->_electronicAddressTypes->typeById((int)$data['electronic_address_type_id']);

        $electronicAddress = [
            'Address'               => $data['email'],
            'AllowMarketing'        => true,
            'Inactive'              => false,
            'IsEMail'               => true,
            'ElectronicAddressType' => $electronicType->rawResponse(),
            'Months'                => 'YYYYYYYYYYYY',
            'PrimaryIndicator'      => true,
        ];

        $address = [
            'AddressType'      => $addressType->rawResponse(),
            'City'             => $data['city'],
            'Country'          => ['Description' => 'USA', 'Id' => 1, 'Inactive' => false],
            'Inactive'         => false,
            'Label'            => true,
            'Months'           => 'YYYYYYYYYYYY',
            'PostalCode'       => $data['postal_code'],
            'PrimaryIndicator' => true,
            'State'            => [
                'Description' => 'Texas',
                'StateCode'   => 'TX',
                'Id'          => 67,
                'Inactive'    => false,
                'Label'       => true,
                'Country'     => ['Description' => 'USA', 'Id' => 1, 'Inactive' => false],
            ],
            'Street1'          => $data['street1'],
        ];

        $constituent = [
            'FirstName'           => $data['first_name'],
            'LastName'            => $data['last_name'],
            'SortName'            => sprintf('%s, %s', $data['last_name'], $data['first_name']),
            'ElectronicAddresses' => [$electronicAddress],
            'ConstituentType'     => $constituentType->rawResponse(),
            'OriginalSource'      => $source->rawResponse(),
            'Addresses'           => [$address],
        ];

        $body     = json_encode(array_filter($constituent));
        $response = $this->_api->post(self::RESOURCE . '/Detail', [
            'headers' => ['Content-Type' => 'application/json'],
            'body'    => $body,
        ]);

        if (!is_array($response) || !isset($response['Id'])) {
            throw new \Exception('Constituent creation did not return an Id');
        }

        return (int)$response['Id'];
    }

    /**
     * @return mixed[]
     */
    public function get(): array
    {
        try {
            return $this->_api->get(self::RESOURCE) ?: [];
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getById(int $id): ?Constituent
    {
        try {
            $data = $this->_api->get(sprintf('%s/%d/Detail', self::RESOURCE, $id));

            return new Constituent($data);
        } catch (\Exception $e) {
            return null;
        }
    }
}
