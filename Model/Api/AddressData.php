<?php
declare(strict_types=1);


namespace Ortto\Connector\Model\Api;

use Ortto\Connector\Logger\OrttoLogger;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\RegionInterface;
use Magento\Directory\Api\CountryInformationAcquirerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderAddressInterface;

class AddressData
{
    private OrttoLogger $logger;
    private CountryInformationAcquirerInterface $countryRepository;

    public function __construct(OrttoLogger $logger, CountryInformationAcquirerInterface $countryRepository)
    {
        $this->logger = $logger;
        $this->countryRepository = $countryRepository;
    }

    /**
     * @param AddressInterface|OrderAddressInterface|\Magento\Quote\Api\Data\AddressInterface $address
     * @return array
     */
    public function toArray($address): array
    {
        $data = [
            'city' => (string)$address->getCity(),
            'street_lines' => $address->getStreet(),
            'post_code' => (string)$address->getPostcode(),
            'prefix' => (string)$address->getPrefix(),
            'first_name' => (string)$address->getFirstname(),
            'middle_name' => (string)$address->getMiddlename(),
            'last_name' => (string)$address->getLastname(),
            'suffix' => (string)$address->getSuffix(),
            'company' => (string)$address->getCompany(),
            'vat' => (string)$address->getVatId(),
            'phone' => (string)$address->getTelephone(),
            'fax' => (string)$address->getFax(),
        ];

        $region = $address->getRegion();
        if ($region instanceof RegionInterface) {
            $data['region'] = [
                'code' => (string)$region->getRegionCode(),
                'name' => (string)$region->getRegion(),
            ];
        }

        try {
            $country = $this->countryRepository->getCountryInfo($address->getCountryId());
            if (!empty($country)) {
                $data['country'] = [
                    'name_en' => (string)$country->getFullNameEnglish(),
                    'name_local' => (string)$country->getFullNameLocale(),
                    'abbr2' => (string)$country->getTwoLetterAbbreviation(),
                    'abbr3' => (string)$country->getThreeLetterAbbreviation(),
                ];
            }
        } catch (NoSuchEntityException $e) {
            $this->logger->debug('Failed to fetch country details: ' . $e->getMessage());
        }

        return $data;
    }
}
