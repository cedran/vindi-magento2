<?php
namespace Vindi\Payment\Model;

use Vindi\Payment\Api\Data\VindiSubscriptionItemInterface;

/**
 * Class VindiSubscriptionItem
 * @package Vindi\Payment\Model
 */
class VindiSubscriptionItem extends \Magento\Framework\Model\AbstractModel implements VindiSubscriptionItemInterface
{
    protected function _construct()
    {
        $this->_init('Vindi\Payment\Model\ResourceModel\VindiSubscriptionItem');
    }

    public function getId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    public function setId($entityId)
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }

    public function getSubscriptionId()
    {
        return $this->getData(self::SUBSCRIPTION_ID);
    }

    public function setSubscriptionId($subscriptionId)
    {
        return $this->setData(self::SUBSCRIPTION_ID, $subscriptionId);
    }

    public function getProductItemId()
    {
        return $this->getData(self::PRODUCT_ITEM_ID);
    }

    public function setProductItemId($productItemId)
    {
        return $this->setData(self::PRODUCT_ITEM_ID, $productItemId);
    }

    public function getProductName()
    {
        return $this->getData(self::PRODUCT_NAME);
    }

    public function setProductName($productName)
    {
        return $this->setData(self::PRODUCT_NAME, $productName);
    }

    public function getProductCode()
    {
        return $this->getData(self::PRODUCT_CODE);
    }

    public function setProductCode($productCode)
    {
        return $this->setData(self::PRODUCT_CODE, $productCode);
    }

    public function getQuantity()
    {
        return $this->getData(self::QUANTITY);
    }

    public function setQuantity($quantity)
    {
        return $this->setData(self::QUANTITY, $quantity);
    }

    public function getPrice()
    {
        return $this->getData(self::PRICE);
    }

    public function setPrice($price)
    {
        return $this->setData(self::PRICE, $price);
    }

    public function getPricingSchemaId()
    {
        return $this->getData(self::PRICING_SCHEMA_ID);
    }

    public function setPricingSchemaId($pricingSchemaId)
    {
        return $this->setData(self::PRICING_SCHEMA_ID, $pricingSchemaId);
    }

    public function getPricingSchemaType()
    {
        return $this->getData(self::PRICING_SCHEMA_TYPE);
    }

    public function setPricingSchemaType($pricingSchemaType)
    {
        return $this->setData(self::PRICING_SCHEMA_TYPE, $pricingSchemaType);
    }

    public function getPricingSchemaFormat()
    {
        return $this->getData(self::PRICING_SCHEMA_FORMAT);
    }

    public function setPricingSchemaFormat($pricingSchemaFormat)
    {
        return $this->setData(self::PRICING_SCHEMA_FORMAT, $pricingSchemaFormat);
    }

    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    public function getUses()
    {
        return $this->getData(self::USES);
    }

    public function setUses($uses)
    {
        return $this->setData(self::USES, $uses);
    }

    public function getCycles()
    {
        return $this->getData(self::CYCLES);
    }

    public function setCycles($cycles)
    {
        return $this->setData(self::CYCLES, $cycles);
    }

    public function getDiscountType()
    {
        return $this->getData(self::DISCOUNT_TYPE);
    }

    public function setDiscountType($discountType)
    {
        return $this->setData(self::DISCOUNT_TYPE, $discountType);
    }

    public function getDiscountPercentage()
    {
        return $this->getData(self::DISCOUNT_PERCENTAGE);
    }

    public function setDiscountPercentage($discountPercentage)
    {
        return $this->setData(self::DISCOUNT_PERCENTAGE, $discountPercentage);
    }

    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }
}
