<?php

namespace Vindi\Payment\Model\Data;

use Magento\Framework\Api\AbstractSimpleObject;
use Vindi\Payment\Api\Data\SubscriptionInterface;

/**
 * Class Subscription
 * @package Vindi\Payment\Model\Data
 */
class Subscription extends AbstractSimpleObject implements SubscriptionInterface
{
    /**
     * Get id
     * @return string|null
     */
    public function getId()
    {
        return $this->_get(self::ID);
    }

    /**
     * Set id
     * @param string $id
     * @return SubscriptionInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * Get client
     * @return string|null
     */
    public function getClient()
    {
        return $this->_get(self::CLIENT);
    }

    /**
     * Set client
     * @param string $client
     * @return SubscriptionInterface
     */
    public function setClient($client)
    {
        return $this->setData(self::CLIENT, $client);
    }

    /**
     * Get plan
     * @return string|null
     */
    public function getPlan()
    {
        return $this->_get(self::PLAN);
    }

    /**
     * Set plan
     * @param string $plan
     * @return SubscriptionInterface
     */
    public function setPlan($plan)
    {
        return $this->setData(self::PLAN, $plan);
    }

    /**
     * Get start_at
     * @return string|null
     */
    public function getStartAt()
    {
        return $this->_get(self::START_AT);
    }

    /**
     * Set start_at
     * @param string $startAt
     * @return SubscriptionInterface
     */
    public function setStartAt($startAt)
    {
        return $this->setData(self::START_AT, $startAt);
    }

    /**
     * Get payment_method
     * @return string|null
     */
    public function getPaymentMethod()
    {
        return $this->_get(self::PAYMENT_METHOD);
    }

    /**
     * Set payment_method
     * @param string $paymentMethod
     * @return SubscriptionInterface
     */
    public function setPaymentMethod($paymentMethod)
    {
        return $this->setData(self::PAYMENT_METHOD, $paymentMethod);
    }

    /**
     * Get payment_profile
     * @return string|null
     */
    public function getPaymentProfile()
    {
        return $this->_get(self::PAYMENT_PROFILE);
    }

    /**
     * Set payment_profile
     * @param string $paymentProfile
     * @return SubscriptionInterface
     */
    public function setPaymentProfile($paymentProfile)
    {
        return $this->setData(self::PAYMENT_PROFILE, $paymentProfile);
    }

    /**
     * Get status
     * @return string|null
     */
    public function getStatus()
    {
        return $this->_get(self::STATUS);
    }

    /**
     * Set status
     * @param string $status
     * @return SubscriptionInterface
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * Get next_billing_at
     * @return string|null
     */
    public function getNextBillingAt()
    {
        return $this->_get(self::NEXT_BILLING_AT);
    }

    /**
     * Set next_billing_at
     * @param string $nextBillingAt
     * @return SubscriptionInterface
     */
    public function setNextBillingAt($nextBillingAt)
    {
        return $this->setData(self::NEXT_BILLING_AT, $nextBillingAt);
    }

    /**
     * Get end_at
     * @return string|null
     */
    public function getBillId()
    {
        return $this->_get(self::BILL_ID);
    }

    /**
     * Set end_at
     * @param string $billId
     * @return SubscriptionInterface
     */
    public function setBillId($billId)
    {
        return $this->setData(self::BILL_ID, $billId);
    }

    /**
     * Get response_data
     * @return string|null
     */
    public function getResponseData()
    {
        return $this->_get(self::RESPONSE_DATA);
    }

    /**
     * Set response_data
     * @param string $responseData
     * @return SubscriptionInterface
     */
    public function setResponseData($responseData)
    {
        return $this->setData(self::RESPONSE_DATA, $responseData);
    }
}
