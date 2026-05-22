<?php

namespace Clubdeuce\Tessitura\Tests\Unit;

use Clubdeuce\Tessitura\Interfaces\ApiInterface;
use Clubdeuce\Tessitura\Resources\AddressType;
use Clubdeuce\Tessitura\Resources\AddressTypes;
use Clubdeuce\Tessitura\Resources\ConstituentType;
use Clubdeuce\Tessitura\Resources\ConstituentTypes;
use Clubdeuce\Tessitura\Resources\Constituents;
use Clubdeuce\Tessitura\Resources\ElectronicAddressType;
use Clubdeuce\Tessitura\Resources\ElectronicAddressTypes;
use Clubdeuce\Tessitura\Resources\OriginalSource;
use Clubdeuce\Tessitura\Resources\OriginalSources;
use Clubdeuce\Tessitura\Tests\TestCase;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Constituents::class)]
class ConstituentsTest extends TestCase
{
    public function testCreateOmitsAddressesWhenAddressDataIsNotProvided(): void
    {
        $api                    = $this->createMock(ApiInterface::class);
        $originalSources        = $this->createMock(OriginalSources::class);
        $constituentTypes       = $this->createMock(ConstituentTypes::class);
        $addressTypes           = $this->createMock(AddressTypes::class);
        $electronicAddressTypes = $this->createMock(ElectronicAddressTypes::class);

        $originalSources->method('sourceById')
            ->with(7)
            ->willReturn(new OriginalSource(['Id' => 7, 'Description' => 'Web']));
        $constituentTypes->method('getById')
            ->with(8)
            ->willReturn(new ConstituentType(['Id' => 8, 'Description' => 'Patron']));
        $addressTypes->expects($this->never())->method('getById');
        $electronicAddressTypes->method('typeById')
            ->with(9)
            ->willReturn(new ElectronicAddressType(['Id' => 9, 'Description' => 'Email']));

        $api->expects($this->once())
            ->method('post')
            ->with(
                'CRM/Constituents/Detail',
                $this->callback(function (array $args): bool {
                    $body = json_decode($args['body'], true);

                    $this->assertArrayNotHasKey('Addresses', $body);
                    $this->assertSame('Ada', $body['FirstName']);
                    $this->assertSame(9, $body['ElectronicAddresses'][0]['ElectronicAddressType']['Id']);

                    return true;
                })
            )
            ->willReturn(['Id' => 1234]);

        $sut = new Constituents(
            $api,
            $originalSources,
            $constituentTypes,
            $addressTypes,
            $electronicAddressTypes
        );

        $result = $sut->create([
            'first_name'                 => 'Ada',
            'last_name'                  => 'Lovelace',
            'email'                      => 'ada@example.com',
            'original_source_id'         => 7,
            'constituent_type_id'        => 8,
            'electronic_address_type_id' => 9,
        ]);

        $this->assertSame(1234, $result);
    }

    public function testCreateUsesExplicitAddressData(): void
    {
        $api                    = $this->createMock(ApiInterface::class);
        $originalSources        = $this->createMock(OriginalSources::class);
        $constituentTypes       = $this->createMock(ConstituentTypes::class);
        $addressTypes           = $this->createMock(AddressTypes::class);
        $electronicAddressTypes = $this->createMock(ElectronicAddressTypes::class);

        $country = ['Description' => 'Canada', 'Id' => 2, 'Inactive' => false];
        $state   = [
            'Description' => 'Ontario',
            'StateCode'   => 'ON',
            'Id'          => 3,
            'Inactive'    => false,
        ];

        $originalSources->method('sourceById')
            ->willReturn(new OriginalSource(['Id' => 7, 'Description' => 'Web']));
        $constituentTypes->method('getById')
            ->willReturn(new ConstituentType(['Id' => 8, 'Description' => 'Patron']));
        $addressTypes->expects($this->once())
            ->method('getById')
            ->with(5)
            ->willReturn(new AddressType(['Id' => 5, 'Description' => 'Home']));
        $electronicAddressTypes->method('typeById')
            ->willReturn(new ElectronicAddressType(['Id' => 9, 'Description' => 'Email']));

        $api->expects($this->once())
            ->method('post')
            ->with(
                'CRM/Constituents/Detail',
                $this->callback(function (array $args) use ($country): bool {
                    $body = json_decode($args['body'], true);

                    $this->assertSame(5, $body['Addresses'][0]['AddressType']['Id']);
                    $this->assertSame($country, $body['Addresses'][0]['Country']);
                    $this->assertSame('Ontario', $body['Addresses'][0]['State']['Description']);
                    $this->assertSame($country, $body['Addresses'][0]['State']['Country']);

                    return true;
                })
            )
            ->willReturn(['Id' => 1234]);

        $sut = new Constituents(
            $api,
            $originalSources,
            $constituentTypes,
            $addressTypes,
            $electronicAddressTypes
        );

        $sut->create([
            'first_name'                 => 'Ada',
            'last_name'                  => 'Lovelace',
            'email'                      => 'ada@example.com',
            'original_source_id'         => 7,
            'constituent_type_id'        => 8,
            'electronic_address_type_id' => 9,
            'address_type_id'            => 5,
            'street1'                    => '123 Queen St',
            'city'                       => 'Toronto',
            'postal_code'                => 'M5H 2N2',
            'country'                    => $country,
            'state'                      => $state,
        ]);
    }

    public function testCreateValidatesRequiredFields(): void
    {
        $sut = new Constituents($this->createMock(ApiInterface::class));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('email');

        $sut->create([
            'first_name'                 => 'Ada',
            'last_name'                  => 'Lovelace',
            'original_source_id'         => 7,
            'constituent_type_id'        => 8,
            'electronic_address_type_id' => 9,
        ]);
    }

    public function testCreateThrowsWhenResponseDoesNotIncludeId(): void
    {
        $api                    = $this->createMock(ApiInterface::class);
        $originalSources        = $this->createMock(OriginalSources::class);
        $constituentTypes       = $this->createMock(ConstituentTypes::class);
        $addressTypes           = $this->createMock(AddressTypes::class);
        $electronicAddressTypes = $this->createMock(ElectronicAddressTypes::class);

        $originalSources->method('sourceById')
            ->willReturn(new OriginalSource(['Id' => 7, 'Description' => 'Web']));
        $constituentTypes->method('getById')
            ->willReturn(new ConstituentType(['Id' => 8, 'Description' => 'Patron']));
        $electronicAddressTypes->method('typeById')
            ->willReturn(new ElectronicAddressType(['Id' => 9, 'Description' => 'Email']));
        $addressTypes->expects($this->never())->method('getById');
        $api->method('post')->willReturn([]);

        $sut = new Constituents(
            $api,
            $originalSources,
            $constituentTypes,
            $addressTypes,
            $electronicAddressTypes
        );

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Constituent creation did not return an Id');

        $sut->create([
            'first_name'                 => 'Ada',
            'last_name'                  => 'Lovelace',
            'email'                      => 'ada@example.com',
            'original_source_id'         => 7,
            'constituent_type_id'        => 8,
            'electronic_address_type_id' => 9,
        ]);
    }
}
