<?php

namespace Vindi\Payment\Block\Info;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Pricing\Helper\Data;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Payment\Block\Info;
use Vindi\Payment\Api\PixConfigurationInterface;
use Vindi\Payment\Model\Payment\PaymentMethod;

/**
 * Class BankSlipPix
 *
 * @package Vindi\Payment\Block\Info
 */
class BankSlipPix extends Info
{

    /**
     * @var string
     */
    protected $_template = 'Vindi_Payment::info/bankslippix.phtml';

    /**
     * @var Data
     */
    protected $currency;

    /**
     * @var PixConfigurationInterface
     */
    protected $pixConfiguration;

    /** @var TimezoneInterface */
    protected $timezone;

    /**
     * @var Json
     */
    protected $json;

    protected $paymentMethod;

    /**
     * @param PaymentMethod $paymentMethod
     * @param Data $currency
     * @param Context $context
     * @param PixConfigurationInterface $pixConfiguration
     * @param Json $json
     * @param TimezoneInterface $timezone
     * @param array $data
     */
    public function __construct(
        PaymentMethod             $paymentMethod,
        Data                      $currency,
        Context                   $context,
        PixConfigurationInterface $pixConfiguration,
        Json                      $json,
        TimezoneInterface         $timezone,
        array                     $data = []
    )
    {
        parent::__construct($context, $data);
        $this->paymentMethod = $paymentMethod;
        $this->currency = $currency;
        $this->pixConfiguration = $pixConfiguration;
        $this->timezone = $timezone;
        $this->json = $json;
    }

    /**
     * Disable block cache
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setCacheLifetime(false);
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getBillId()
    {
        $order = $this->getOrder();
        return $order->getVindiBillId() ?? null;
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getOrder()
    {
        return $this->getInfo()->getOrder();
    }

    /**
     * Get reorder URL
     *
     * @param object $order
     * @return string
     */
    public function getReorderUrl()
    {
        $order = $this->getOrder();
        return $this->getUrl('sales/order/reorder', ['order_id' => $order->getId()]);
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function hasInvoice()
    {
        return $this->getOrder()->hasInvoices();
    }

    /**
     * Get order payment method name
     *
     * @return string
     */
    public function getPaymentMethodName()
    {
        return $this->getOrder()->getPayment()->getMethodInstance()->getTitle();
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function canShowBankSlipPixInfo()
    {
        $paymentMethod = $this->getOrder()->getPayment()->getMethod() === \Vindi\Payment\Model\Payment\BankSlipPix::CODE;
        $daysToPayment = $this->getMaxDaysToPayment();

        if (!$daysToPayment) {
            return true;
        }

        $timestampMaxDays = strtotime($daysToPayment);

        return $paymentMethod && $this->isValidToPayment($timestampMaxDays);
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getQrCodePix()
    {
        return $this->getOrder()->getPayment()->getAdditionalInformation('qrcode_path');
    }

    /**
     * @return string
     */
    public function getQrCodeWarningMessage()
    {
        return (string) $this->pixConfiguration->getQrCodeWarningMessage();
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getQrcodeOriginalPath(): string
    {
        try {
            $qrcodeOriginalPath = (string)$this->getOrder()->getPayment()->getAdditionalInformation('qrcode_original_path');
            return $this->json->serialize($qrcodeOriginalPath);
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getDaysToKeepWaitingPayment()
    {
        $daysToPayment = $this->getMaxDaysToPayment();
        if (!$daysToPayment) {
            return '';
        }

        $timestampMaxDays = strtotime($daysToPayment);
        return date('d/m/Y H:m:s', $timestampMaxDays);
    }

    /**
     * @param $timestampMaxDays
     *
     * @return bool
     */
    protected function isValidToPayment($timestampMaxDays)
    {
        if (!$timestampMaxDays) {
            return false;
        }

        return $timestampMaxDays >= $this->timezone->scopeTimeStamp();
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getMaxDaysToPayment(): string
    {
        return (string) $this->getOrder()->getPayment()->getAdditionalInformation('max_days_to_keep_waiting_payment');
    }

    public function getPrintUrl(): string
    {
        return (string) $this->getOrder()->getPayment()->getAdditionalInformation('print_url');
    }

    public function getDueDate(): string
    {
        return (string) $this->getOrder()->getPayment()->getAdditionalInformation('due_at');
    }
}
