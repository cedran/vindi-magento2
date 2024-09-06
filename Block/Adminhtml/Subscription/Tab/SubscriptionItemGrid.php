<?php
namespace Vindi\Payment\Block\Adminhtml\Subscription\Tab;

class SubscriptionItemGrid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    protected $coreRegistry = null;
    protected $subscriptionItemFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Vindi\Payment\Model\ResourceModel\VindiSubscriptionItem\CollectionFactory $subscriptionItemFactory,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        $this->subscriptionItemFactory = $subscriptionItemFactory;
        $this->coreRegistry = $coreRegistry;
        parent::__construct($context, $backendHelper, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setId('vindi_grid_subscription_items');
        $this->setDefaultSort('entity_id');
    $this->setDefaultDir('ASC');
        $this->setUseAjax(true);
        if ($this->getRequest()->getParam('entity_id')) {
            $this->setDefaultFilter(['in_subscription_items' => 1]);
        } else {
            $this->setDefaultFilter(['in_subscription_items' => 0]);
        }
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = $this->subscriptionItemFactory->create()
            ->addFieldToSelect(['entity_id', 'product_name', 'status']);
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'entity_id',
    [
        'header' => __('ID'),
        'width' => '50px',
        'index' => 'entity_id',
        'type' => 'number',
    ]
        );
        $this->addColumn(
            'product_name',
            [
                'header' => __('Product Name'),
                'index' => 'product_name',
                'header_css_class' => 'col-type',
                'column_css_class' => 'col-type',
            ]
        );
        $this->addColumn(
            'status',
            [
                'header' => __('Status'),
                'index' => 'status',
                'header_css_class' => 'col-status',
                'column_css_class' => 'col-status',
            ]
        );

        $this->addColumn(
            'edit_action',
            [
                'header' => __('Action'),
                'width' => '100px',
                'type' => 'action',
                'getter' => 'getEntityId',
    'actions' => [
        [
            'caption' => __('Edit'),
            'url' => [
                'base' => 'vindi_payment/subscription/edititem',
                'params' => ['form_key' => $this->getFormKey()]
            ],
            'field' => 'entity_id',
                    ],
                ],
                'filter' => false,
                'sortable' => false,
                'index' => 'stores',
                'header_css_class' => 'col-action',
                'column_css_class' => 'col-action',
            ]
        );

        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/subscription/grids', ['_current' => true]);
    }

    protected function _getSelectedSubscriptionItems()
    {
        $subscriptionItems = array_keys($this->getSelectedSubscriptionItems());
        return $subscriptionItems;
    }

    public function getSelectedSubscriptionItems()
    {
        $id = $this->getRequest()->getParam('entity_id');
        $model = $this->subscriptionItemFactory->create()->addFieldToFilter('subscription_id', $id);
        $grids = [];
        foreach ($model as $key => $value) {
            $grids[] = $value->getEntityId();
        }
        $subscriptionItemIds = [];
        foreach ($grids as $obj) {
            $subscriptionItemIds[$obj] = ['position' => "0"];
        }
        return $subscriptionItemIds;
    }
}
