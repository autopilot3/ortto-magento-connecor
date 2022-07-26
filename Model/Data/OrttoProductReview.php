<?php
declare(strict_types=1);

namespace Ortto\Connector\Model\Data;

use Magento\Framework\DataObject;
use Ortto\Connector\Api\Data\OrttoProductReviewInterface;

class OrttoProductReview extends DataObject implements OrttoProductReviewInterface
{
    /** @inheirtDoc */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /** @inheirtDoc */
    public function getCreatedAt()
    {
        return (string)$this->getData(self::CREATED_AT);
    }

    /** @inheirtDoc */
    public function setNickname($nickname)
    {
        return $this->setData(self::NICKNAME, $nickname);
    }

    /** @inheirtDoc */
    public function getNickname()
    {
        return (string)$this->getData(self::NICKNAME);
    }

    /** @inheirtDoc */
    public function setDetails($details)
    {
        return $this->setData(self::DETAILS, $details);
    }

    /** @inheirtDoc */
    public function getDetails()
    {
        return (string)$this->getData(self::DETAILS);
    }

    /** @inheirtDoc */
    public function setTitle($title)
    {
        return $this->setData(self::TITLE, $title);
    }

    /** @inheirtDoc */
    public function getTitle()
    {
        return (string)$this->getData(self::TITLE);
    }

    /** @inheirtDoc */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /** @inheirtDoc */
    public function getStatus()
    {
        return (string)$this->getData(self::STATUS);
    }

    /** @inheirtDoc */
    public function setProduct($product)
    {
        return $this->setData(self::PRODUCT, $product);
    }

    /** @inheirtDoc */
    public function getProduct()
    {
        return $this->getData(self::PRODUCT);
    }

    /** @inheirtDoc */
    public function setCustomer($customer)
    {
        return $this->setData(self::CUSTOMER, $customer);
    }

    /** @inheirtDoc */
    public function getCustomer()
    {
        return $this->getData(self::CUSTOMER);
    }

    /** @inheirtDoc */
    public function serializeToArray()
    {
        if ($this == null) {
            return null;
        }
        $result=[];
        $result[self::CREATED_AT] = $this->getCreatedAt();
        $result[self::NICKNAME] = $this->getNickname();
        $result[self::DETAILS] = $this->getDetails();
        $result[self::TITLE] = $this->getTitle();
        $result[self::STATUS] = $this->getStatus();
        $product = $this->getProduct();
        $result[self::PRODUCT] = $product != null ? $product->serializeToArray() : null;
        $customer = $this->getCustomer();
        $result[self::CUSTOMER] = $customer != null ? $customer->serializeToArray() : null;
        return $result;
    }
}
