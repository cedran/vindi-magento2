<?php
namespace Vindi\Payment\Controller\Adminhtml\VindiPlan;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Vindi\Payment\Helper\Data;
use Vindi\Payment\Model\Vindi\Plan;
use Vindi\Payment\Model\VindiPlanFactory;
use Vindi\Payment\Model\VindiPlanRepository;
use Magento\Backend\Model\Session;

/**
 * Class Save
 * @package Vindi\Payment\Controller\Adminhtml\VindiPlan
 */
class Save extends Action
{
    /**
     * @var Plan
     */
    protected $plan;

    /**
     * @var VindiPlanFactory
     */
    protected $vindiPlanFactory;

    /**
     * @var VindiPlanRepository
     */
    protected $vindiPlanRepository;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var Session
     */
    protected $session;

    /**
     * Save constructor.
     * @param Context $context
     * @param Plan $plan
     * @param VindiPlanFactory $vindiPlanFactory
     * @param VindiPlanRepository $vindiPlanRepository
     * @param DateTime $dateTime
     * @param Session $session
     */
    public function __construct(
        Context $context,
        Plan $plan,
        VindiPlanFactory $vindiPlanFactory,
        VindiPlanRepository $vindiPlanRepository,
        DateTime $dateTime,
        Session $session
    ) {
        parent::__construct($context);
        $this->plan                = $plan;
        $this->vindiPlanFactory    = $vindiPlanFactory;
        $this->vindiPlanRepository = $vindiPlanRepository;
        $this->dateTime            = $dateTime;
        $this->session             = $session;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $post = $this->getRequest()->getPostValue();

        if (!$post) {
            return $this->_redirect('*/*/');
        }

        $entityId         = $this->getRequest()->getParam('entity_id');
        $validationResult = $this->validatePostData($post);

        if ($validationResult !== true) {
            $this->messageManager->addWarningMessage($validationResult);
            $this->session->setFormData($post);
            if ($entityId) {
                return $this->_redirect('*/*/edit', ['entity_id' => $entityId]);
            } else {
                return $this->_redirect('*/*/new');
            }
        }

        $existingPlan = null;
        $name = preg_replace('/[^\p{L}\p{N}\s]/u', '', $post["settings"]["name"]);
        $vindiId = $post["settings"]["vindi_id"] ?? '';
        $code = empty($post["settings"]["code"]) ? Data::sanitizeItemSku($name) : $post["settings"]["code"];

        try {
            $data = $this->prepareData($post, $name, $code);

            if (!empty($vindiId)) {
                $existingPlan = $this->vindiPlanRepository->getByVindiId($vindiId);
            }

            if ($existingPlan && $existingPlan->getId()) {
                $this->plan->save($data);

                $data = $this->prepareDataForMagentoStore($data, $post);

                $existingPlan->addData($data);
                $this->vindiPlanRepository->save($existingPlan);

                $this->messageManager->addSuccessMessage(__('Plan updated successfully!'));
            } else {
                $existingPlanByCode = $this->vindiPlanRepository->getByCode($code);

                if ($existingPlanByCode && $existingPlanByCode->getId() && $existingPlanByCode->getId() != $entityId) {
                    $this->messageManager->addErrorMessage(__('A plan with the same code already exists.'));
                    $this->session->setFormData($post);
                    return $this->_redirect('*/*/edit', ['entity_id' => $entityId]);
                }

                $vindiId = $this->plan->save($data);

                $codeData = [];
                $codeData['vindi_id'] = $vindiId;
                $codeData['code'] = $vindiId . '-' . Data::sanitizeItemSku($name);

                $this->plan->save($codeData);

                $data['code'] = $codeData['code'];
                $data = $this->prepareDataForMagentoStore($data, $post);

                $vindiPlan = $this->vindiPlanFactory->create();
                $vindiPlan->setData($data);
                $vindiPlan->setVindiId($vindiId);
                $this->vindiPlanRepository->save($vindiPlan);

                $entityId = $vindiPlan->getId();

                $this->messageManager->addSuccessMessage(__('Plan saved successfully!'));
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->session->setFormData($post);
            if ($entityId) {
                return $this->_redirect('*/*/edit', ['entity_id' => $entityId]);
            } else {
                return $this->_redirect('*/*/new');
            }
            return;
        } finally {
            if ($entityId) {
                return $this->_redirect('*/*/edit', ['entity_id' => $entityId]);
            } else {
                return $this->_redirect('*/*/');
            }
        }
    }

    /**
     * Validates the POST data received.
     *
     * @param array $post The POST data received.
     * @return bool|string True if the data is valid, or a string with the error message.
     */
    private function validatePostData($post)
    {
        $settings = $post['settings'] ?? [];

        if (empty($settings['billing_trigger_type'])) {
            return __('Billing method cannot be empty.');
        }

        if ($settings["billing_trigger_type"] == 'day_of_month' && !empty($settings["interval_count"]) && $settings["interval_count"] > 1) {
            return __('This billing method only supports monthly plans, i.e., month-to-month.');
        }

        if (!empty($settings['installments']) && $settings['installments'] > 1) {
            if (($settings['interval'] ?? '') == 'days') {
                return __('Billing interval cannot be in days for plans with more than one installment.');
            } elseif (!empty($settings['interval_count']) && $settings['installments'] > $settings['interval_count']) {
                return __('The number of installments cannot be greater than the billing interval.');
            }
        }

        if ($settings["billing_cycles"] != "" && $settings["billing_cycles"] < 1) {
            return __('Number of periods must be greater than or equal to 1 for temporary duration. Or empty for indefinite time!');
        }

        return true;
    }

    /**
     * Prepares the data to be saved based on the POST inputs.
     *
     * @param array $post The POST data received.
     * @param string $name The processed plan name.
     * @param string $code The processed plan code.
     * @return array The data prepared for saving.
     */
    private function prepareData($post, $name, $code)
    {
        $data = [
            'updated_at' => $this->dateTime->gmtDate(),
            'created_at' => $this->dateTime->gmtDate(),
        ];

        if (!empty($post["settings"]["vindi_id"])) {
            $data['vindi_id'] = $post["settings"]["vindi_id"];
        }

        if (!empty($name)) {
            $data['name'] = $name;
        }

        if (!empty($post["settings"]["status"])) {
            $data['status'] = $post["settings"]["status"];
        }

        if (!empty($code)) {
            $data['code'] = $code;
        }

        $data['description'] = $post["settings"]["description"] ?? '';

        if (!empty($post["settings"]["interval"])) {
            $data['interval'] = $post["settings"]["interval"];
        }

        if (!empty($post["settings"]["interval_count"])) {
            $data['interval_count'] = $post["settings"]["interval_count"];
        }

        if (!empty($post["settings"]["billing_trigger_type"])) {
            $data['billing_trigger_type'] = $post["settings"]["billing_trigger_type"];
            if ($post["settings"]['billing_trigger_type'] == 'day_of_month') {
                $data['billing_trigger_day'] = $post["settings"]["billing_trigger_day"] ?? null;
            } elseif ($post["settings"]['billing_trigger_type'] == 'based_on_period') {
                $data['billing_trigger_day']  = $post["settings"]["billing_trigger_day_type_on_period"] ?? null;
                $data['billing_trigger_type'] = $post["settings"]['billing_trigger_day_based_on_period'] ?? null;
            }
        }

        if (!empty($post["settings"]["billing_cycles"])) {
            $data['billing_cycles'] = $post["settings"]["billing_cycles"];
        }

        if (!empty($post["settings"]["installments"])) {
            $data['installments'] = $post["settings"]["installments"];
        } else {
            $data['installments'] = 1;
        }

        if (isset($post["settings"]["duration"]) && isset($post["settings"]["billing_cycles"])) {
            if ($post["settings"]["duration"] == 'undefined') {
                $data['billing_cycles'] = null;
            } else {
                $data['billing_cycles'] = $post["settings"]["billing_cycles"];
            }
            $data['duration'] = $post["settings"]["duration"];
        }

        return $data;
    }

    /**
     * Prepares the data to be saved in the Magento store based on the POST inputs.
     *
     * @param array $data The data prepared for saving.
     * @param array $post The POST data received.
     * @return array The data prepared for saving in the Magento store.
     */
    private function prepareDataForMagentoStore($data, $post)
    {
        if (isset($post["settings"]["description_display_on_product_page"])) {
            $data['description_display_on_product_page'] = (int) $post["settings"]["description_display_on_product_page"];
        }

        if (isset($post["settings"]["installments"])) {
            $data['installments'] = empty($post["settings"]["installments"]) ? null : $post["settings"]["installments"];
        }

        if (isset($post["settings"]["billing_cycles"]) && $post["settings"]["billing_cycles"] === '') {
            $data['duration'] = 'undefined';
            $data['billing_cycles'] = null;
        } elseif (!empty($post["settings"]["duration"])) {
            $data['duration'] = $post["settings"]["duration"];
        }

        if (isset($post["settings"]["billing_trigger_day_type_on_period"])) {
            $data['billing_trigger_day_type_on_period'] = $post["settings"]["billing_trigger_day_type_on_period"];
        }

        if (!empty($post["settings"]["billing_trigger_day_based_on_period"])) {
            $data['billing_trigger_day_based_on_period'] = $post["settings"]["billing_trigger_day_based_on_period"];
        }

        return $data;
    }
}

