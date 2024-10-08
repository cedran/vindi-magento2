<?php

namespace Vindi\Payment\Block\Adminhtml\Subscription;

use DateTime;
use Exception;
use Magento\Backend\Block\Widget\Container;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Registry;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Vindi\Payment\Helper\Api;
use Vindi\Payment\Model\ResourceModel\SubscriptionOrder\CollectionFactory as SubscriptionOrderCollectionFactory;
use Vindi\Payment\Model\SubscriptionFactory;

/**
 * Class View
 * @package Vindi\Payment\Block\Adminhtml\Subscription
 */
class View extends Container
{
    /**
     * @var array
     */
    private $subscriptions = null;
    /**
     * @var array
     */
    private $periods = null;
    /**
     * @var Api
     */
    private $api;
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var SubscriptionOrderCollectionFactory
     */
    private $subscriptionsOrderCollectionFactory;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceHelper;

    /**
     * @var SubscriptionFactory
     */
    private $subscriptionFactory;

    /**
     * View constructor.
     * @param Context $context
     * @param Registry $registry
     * @param SubscriptionOrderCollectionFactory $subscriptionsOrderCollectionFactory
     * @param Api $api
     * @param PriceCurrencyInterface $priceHelper
     * @param SubscriptionFactory $subscriptionFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        SubscriptionOrderCollectionFactory $subscriptionsOrderCollectionFactory,
        Api $api,
        PriceCurrencyInterface $priceHelper,
        SubscriptionFactory $subscriptionFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->api = $api;
        $this->registry = $registry;
        $this->subscriptionsOrderCollectionFactory = $subscriptionsOrderCollectionFactory;
        $this->priceHelper = $priceHelper;
        $this->subscriptionFactory = $subscriptionFactory;
    }

    /**
     * @return Container
     */
    protected function _prepareLayout()
    {
        $this->buttonList->add(
            'back',
            [
                'label' => __('Back'),
                'onclick' => 'setLocation(\'' . $this->getBackUrl() . '\')',
                'class' => 'back'
            ]
        );

        $this->buttonList->add('vindi_payment_subscription_edit', [
            'id' => 'vindi_payment_subscription_edit',
            'label' => __('Edit Subscription'),
            'class' => 'primary',
            'button_class' => 'add',
            'onclick' => 'setLocation(\'' . $this->getEditUrl() . '\')'
        ]);

        return parent::_prepareLayout();
    }

    /**
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('*/*/');
    }

    /**
     * @return string
     */
    public function getEditUrl()
    {
        return $this->getUrl('vindi_payment/subscription/edit', [
            'id' => $this->getSubscriptionId()
        ]);
    }

    /**
     * @return string
     */
    public function getCustomerName()
    {
        $data = $this->getSubscriptionData();
        if (array_key_exists('customer', $data)) {
            return $data['customer']['name'];
        }

        return '';
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        $data = $this->getSubscriptionData();
        if (array_key_exists('status', $data)) {
            return $data['status'];
        }

        return '-';
    }

    /**
     * @return string
     */
    public function getStartAt()
    {
        $data = $this->getSubscriptionData();
        if (!array_key_exists('start_at', $data)) {
            return '-';
        }

        try {
            $startAt = new DateTime($data['start_at']);
            return $startAt->format('d/m/Y');
        } catch (Exception $e) {
        }

        return '-';
    }

    /**
     * @return string
     */
    public function getPlanName()
    {
        $data = $this->getSubscriptionData();
        if (array_key_exists('plan', $data)) {
            return $data['plan']['name'];
        }

        return '-';
    }

    /**
     * @return string
     */
    public function getPlanCycle()
    {
        $data = $this->getSubscriptionData();
        if (array_key_exists('interval', $data) && array_key_exists('interval_count', $data)) {
            $interval = $data['interval'];
            $intervalCount = $data['interval_count'];
            $intervalLabels = [
                'days' => __('day(s)'),
                'weeks' => __('week(s)'),
                'months' => __('month(s)'),
                'years' => __('year(s)')
            ];

            if (array_key_exists($interval, $intervalLabels)) {
                return __('Every %1 %2', $intervalCount, $intervalLabels[$interval]);
            }
        }

        return '-';
    }

    /**
     * @return string
     */
    public function getPlanDuration()
    {
        $data = $this->getSubscriptionData();
        if (array_key_exists('billing_cycles', $data)) {
            $billingCycle = $data["billing_cycles"];

            if ($billingCycle == null || empty($billingCycle) || $billingCycle < 0) {
                return __('Permanent');
            }

            return __('%1 cycles', $billingCycle);
        }

        return __('Permanent');
    }

    /**
     * @return string
     */
    public function getNextBillingAt()
    {
        $data = $this->getSubscriptionData();
        if (!array_key_exists('next_billing_at', $data)) {
            return '-';
        }

        return $this->dateFormat($data['next_billing_at']);
    }

    /**
     * @return string
     */
    public function getBillingTrigger()
    {
        $data = $this->getSubscriptionData();
        if (!array_key_exists('billing_trigger_type', $data)
            || !array_key_exists('billing_trigger_day', $data)
        ) {
            return '-';
        }

        $billingTriggerDay  = $data['billing_trigger_day'];
        $billingTriggerType = $data['billing_trigger_type'];

        if ($billingTriggerType == 'day_of_month') {
            return __('Day %1 of the month', $billingTriggerDay);
        }

        if ($billingTriggerDay == 0) {
            if ($billingTriggerType == 'beginning_of_period') {
                return __('Exactly on the day of the start of the period');
            }

            return __('Exactly on the day of the end of the period');
        }

        $billingTriggerDayLabel = __('before');

        if ($billingTriggerDay > 0) {
            $billingTriggerDayLabel = __('after');
        }

        $billingTriggerTypeLabel = __('end of the period');

        if ($billingTriggerType == 'beginning_of_period') {
            $billingTriggerTypeLabel = __('start of the period');
        }

        return __('%1 days', $billingTriggerDay) . ' ' . $billingTriggerDayLabel . ' ' . $billingTriggerTypeLabel;
    }

    /**
     * @return array
     */
    public function getProducts()
    {
        $data = $this->getSubscriptionData();
        if (!array_key_exists('product_items', $data)) {
            return [];
        }

        return $data['product_items'];
    }

    /**
     * @return string
     */
    public function getPaymentMethod()
    {
        $data = $this->getSubscriptionData();
        if (array_key_exists('payment_method', $data)) {
            return $data['payment_method']['name'];
        }

        return '-';
    }

    /**
     * @param $cycle
     * @return string
     */
    public function getCycleLabel($cycle)
    {
        if (is_null($cycle)) {
            return __('Permanent');
        }

        return $cycle;
    }

    /**
     * @return array|null
     */
    public function getPeriods()
    {
        if (!$id = $this->getSubscriptionId()) {
            return [];
        }

        if ($this->periods === null) {
            $request = $this->api->request('periods?query=subscription_id%3D' . $id, 'GET');
            if (is_array($request) && array_key_exists('periods', $request)) {
                $this->periods = $request['periods'];
            }
        }

        return $this->periods;
    }

    /**
     * @return array|mixed
     */
    public function getDiscounts()
    {
        $products = $this->getProducts();
        if (empty($products)) {
            return [];
        }

        $discounts = [];
        foreach ($products as $key => $product) {
            if (empty($product['discounts'])) {
                continue;
            }

            foreach ($product['discounts'] as $discount) {
                $discounts[$key] = array_merge($discount, [
                    'product' => $product['product']['name']
                ]);
            }
        }

        return $discounts;
    }

    /**
     * @param $date
     * @return string
     */
    public function dateFormat($date)
    {
        try {
            $startAt = new DateTime($date);
            return $startAt->format('d/m/Y');
        } catch (Exception $e) {
        }

        return '-';
    }

    /**
     * @return int
     */
    public function getSubscriptionId()
    {
        $data = $this->getSubscriptionData();
        if (array_key_exists('id', $data)) {
            return $data['id'];
        }

        return 0;
    }

    /**
     * @return array
     */
    public function getLinkedOrders()
    {
        $subscriptionId = $this->getSubscriptionId();
        if (!$subscriptionId) {
            return [];
        }

        $collection = $this->subscriptionsOrderCollectionFactory->create();
        $collection->addFieldToFilter('subscription_id', $subscriptionId);

        return $collection->getItems();
    }

    /**
     * @param $amount
     * @return string
     */
    public function formatPrice($amount)
    {
        return $this->priceHelper->format($amount, false);
    }

    /**
     * @return array|null
     */
    private function getSubscriptionData()
    {
        if ($this->subscriptions === null) {
            $id = $this->registry->registry('vindi_payment_subscription_id');

            $subscriptionModel = $this->subscriptionFactory->create()->load($id);

            $responseData = $subscriptionModel->getData('response_data');

            if ($responseData) {
                $this->subscriptions = json_decode($responseData, true);
            } else {
                $request = $this->api->request('subscriptions/'.$id, 'GET');
                if (is_array($request) && array_key_exists('subscription', $request)) {
                    $this->subscriptions = $request['subscription'];

                    $subscriptionModel->setData('response_data', json_encode($this->subscriptions));
                    $subscriptionModel->save();
                }
            }
        }

        return $this->subscriptions;
    }
}
