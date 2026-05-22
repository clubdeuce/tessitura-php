<?php

namespace Clubdeuce\Tessitura\Resources;

use Clubdeuce\Tessitura\Base\Base;
use Clubdeuce\Tessitura\Interfaces\ApiInterface;
use Clubdeuce\Tessitura\Interfaces\ResourceInterface;
use InvalidArgumentException;

class Constituents extends Base implements ResourceInterface
{
    public const RESOURCE = 'CRM/Constituents';
    private const REQUIRED_CREATE_FIELDS = [
        'first_name',
        'last_name',
        'email',
        'original_source_id',
        'constituent_type_id',
        'electronic_address_type_id',
    ];
    private const OPTIONAL_ADDRESS_FIELDS = [
        'address_type_id',
        'street1',
        'city',
        'postal_code',
        'country',
        'state',
    ];

    // phpcs:disable PSR2.Classes.PropertyDeclaration.Underscore
    protected ApiInterface $_api;
    protected OriginalSources $_originalSources;
    protected ConstituentTypes $_constituentTypes;
    protected AddressTypes $_addressTypes;
    protected ElectronicAddressTypes $_electronicAddressTypes;
    // phpcs:enable PSR2.Classes.PropertyDeclaration.Underscore

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
     *     @type int    $address_type_id
     *     @type string $street1
     *     @type string $city
     *     @type string $postal_code
     *     @type mixed[] $country
     *     @type mixed[] $state
     * }
     * @return int The new constituent ID
     * @throws \Exception On API failure or when the response contains no Id.
     */
    public function create(array $data): int
    {
        $this->validateRequiredFields($data, self::REQUIRED_CREATE_FIELDS);

        $source          = $this->_originalSources->sourceById((int)$data['original_source_id']);
        $constituentType = $this->_constituentTypes->getById((int)$data['constituent_type_id']);
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

        $constituent = [
            'FirstName'           => $data['first_name'],
            'LastName'            => $data['last_name'],
            'SortName'            => sprintf('%s, %s', $data['last_name'], $data['first_name']),
            'ElectronicAddresses' => [$electronicAddress],
            'ConstituentType'     => $constituentType->rawResponse(),
            'OriginalSource'      => $source->rawResponse(),
        ];

        $address = $this->buildAddress($data);
        if (null !== $address) {
            $constituent['Addresses'] = [$address];
        }

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
     * @param mixed[] $data
     * @return mixed[]|null
     */
    private function buildAddress(array $data): ?array
    {
        if (!$this->hasAddressData($data)) {
            return null;
        }

        $this->validateRequiredFields($data, self::OPTIONAL_ADDRESS_FIELDS);

        if (!is_array($data['country'])) {
            throw new InvalidArgumentException('The "country" field must be an array.');
        }

        if (!is_array($data['state'])) {
            throw new InvalidArgumentException('The "state" field must be an array.');
        }

        $addressType = $this->_addressTypes->getById((int)$data['address_type_id']);
        $state       = $data['state'];

        if (!isset($state['Country'])) {
            $state['Country'] = $data['country'];
        }

        return [
            'AddressType'      => $addressType->rawResponse(),
            'City'             => $data['city'],
            'Country'          => $data['country'],
            'Inactive'         => false,
            'Label'            => true,
            'Months'           => 'YYYYYYYYYYYY',
            'PostalCode'       => $data['postal_code'],
            'PrimaryIndicator' => true,
            'State'            => $state,
            'Street1'          => $data['street1'],
        ];
    }

    /**
     * @param mixed[] $data
     * @param string[] $fields
     */
    private function validateRequiredFields(array $data, array $fields): void
    {
        $missing = [];

        foreach ($fields as $field) {
            if (
                !array_key_exists($field, $data)
                || null === $data[$field]
                || '' === $data[$field]
                || (is_array($data[$field]) && [] === $data[$field])
            ) {
                $missing[] = $field;
            }
        }

        if ([] !== $missing) {
            throw new InvalidArgumentException(
                sprintf('Missing required constituent field(s): %s', implode(', ', $missing))
            );
        }
    }

    /**
     * @param mixed[] $data
     */
    private function hasAddressData(array $data): bool
    {
        foreach (self::OPTIONAL_ADDRESS_FIELDS as $field) {
            if (array_key_exists($field, $data)) {
                return true;
            }
        }

        return false;
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
