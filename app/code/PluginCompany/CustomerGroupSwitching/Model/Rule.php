<?php
/*
 * Created by:  Milan Simek
 * Company:     Plugin Company
 *
 * LICENSE: http://plugin.company/docs/magento-extensions/magento-extension-license-agreement
 *
 * YOU WILL ALSO FIND A PDF COPY OF THE LICENSE IN THE DOWNLOADED ZIP FILE
 *
 * FOR QUESTIONS AND SUPPORT
 * PLEASE DON'T HESITATE TO CONTACT US AT:
 *
 * SUPPORT@PLUGIN.COMPANY
 */
namespace PluginCompany\CustomerGroupSwitching\Model;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\ResourceModel\AddressRepository;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;
use Magento\Customer\Model\ResourceModel\GroupRepository;
use Magento\Customer\Model\Session;
use Magento\Email\Model\TemplateFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Stdlib\DateTime\DateTimeFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Rule\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Registry;
use Magento\Framework\Model\Context;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\StoreRepository;
use PluginCompany\CustomerGroupSwitching\Api\Data\RuleInterface;
use Magento\Newsletter\Model\SubscriberFactory;
use PluginCompany\CustomerGroupSwitching\Model\Mailer;
use PluginCompany\CustomerGroupSwitching\Model\Rule\Condition\CombineFactory;
use PluginCompany\CustomerGroupSwitching\Model\RuleFactory;
use PluginCompany\CustomerGroupSwitching\Model\ValidationDataObjectFactory;

class Rule extends AbstractModel implements RuleInterface
{

    /**
     * Entity code.
     * Can be used as part of method name for entity processing
     */
    const ENTITY    = 'plugincompany_groupswitch_rule';
    const CACHE_TAG = 'plugincompany_groupswitch_rule';

    /**
     * @var string
     */
    protected $_cacheTag = self::CACHE_TAG;

    /**
     * Prefix of model events names
     * @var string
     */
    protected $_eventPrefix = 'plugincompany_groupswitch_rule';

    /**
     * Parameter name in event
     * @var string
     */
    protected $_eventObject = 'rule';


    /**
     * @var CombineFactory
     */
    protected $conditionCombineFactory;

    /**
     * @var DateTimeFactory
     */
    protected $dateTimeFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var TemplateFactory
     */
    protected $emailTemplateFactory;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var CollectionFactory
     */
    protected $orderItemCollectionFactory;

    /**
     * @var OrderCollectionFactory
     */
    protected $orderCollectionFactory;

    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var CustomerCollectionFactory
     */
    protected $customerCollectionFactory;

    /**
     * @var RuleFactory
     */
    protected $groupswitchRuleFactory;

    /**
     * @var ValidationDataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $appState;

    /**
     * @var Mailer
     */
    protected $mailer;

    protected $subscriberFactory;

    /**
     * @var StoreRepository
     */
    protected $storeRepository;

    /**
     * @var GroupRepository
     */
    protected $customerGroupRepository;

    /** @var AddressRepository */
    protected $customerAddressRepository;

    /**
     * @var array
     */
    protected $customerData;

    protected $lastProcessedRule = null;
    private $order;
    private $save;
    private $allowedRuleIds;
    private $customer;
    private $validationObject;

    public function __construct(
        Context $context,
        Registry $registry,
        CombineFactory $conditionCombineFactory,
        DateTimeFactory $dateTimeFactory,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        TemplateFactory $emailTemplateFactory,
        Session $customerSession,
        ResourceConnection $resourceConnection,
        CollectionFactory $orderItemCollectionFactory,
        OrderCollectionFactory $orderCollectionFactory,
        CheckoutSession $checkoutSession,
        CustomerCollectionFactory $customerCollectionFactory,
        RuleFactory $groupswitchRuleFactory,
        ValidationDataObjectFactory $dataObjectFactory,
        FormFactory $formFactory,
        TimezoneInterface $localeDate,
        Mailer $mailer,
        StoreRepository $storeRepository,
        SubscriberFactory $subscriberFactory,
        GroupRepository $customerGroupRepository,
        AddressRepository $customerAddressRepository,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->dataObjectFactory = $dataObjectFactory;
        $this->conditionCombineFactory = $conditionCombineFactory;
        $this->dateTimeFactory = $dateTimeFactory;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->emailTemplateFactory = $emailTemplateFactory;
        $this->customerSession = $customerSession;
        $this->resourceConnection = $resourceConnection;
        $this->orderItemCollectionFactory = $orderItemCollectionFactory;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->storeRepository = $storeRepository;
        $this->customerGroupRepository = $customerGroupRepository;
        $this->customerAddressRepository = $customerAddressRepository;
        $this->checkoutSession = $checkoutSession;
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->groupswitchRuleFactory = $groupswitchRuleFactory;
        $this->appState = $context->getAppState();
        $this->mailer = $mailer;
        $this->subscriberFactory = $subscriberFactory;

        parent::__construct(
            $context,
            $registry,
            $formFactory,
            $localeDate,
            $resource,
            $resourceCollection,
            $data
        );
    }
    /**
     * Set resource model and Id field name
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('PluginCompany\CustomerGroupSwitching\Model\ResourceModel\Rule');
        $this->setIdFieldName('rule_id');
    }

    /**
     * Initialize rule model data from array.
     * Set store labels if applicable.
     *
     * @param array $data
     *
     * @return \PluginCompany\CustomerGroupSwitching\Model\Rule
     */
    public function loadPost(array $data)
    {
        parent::loadPost($data);

        if (isset($data['store_labels'])) {
            $this->setStoreLabels($data['store_labels']);
        }

        return $this;
    }

    /**
     * Get rule conditions combination instance
     *
     * @return \PluginCompany\CustomerGroupSwitching\Model\Rule\Condition\Combine
     */
    public function getConditionsInstance()
    {
        return $this->conditionCombineFactory->create();
    }

    /**
     * @param string $formName
     * @return string
     */
    public function getConditionsFieldSetId($formName = '')
    {
        return $formName . 'rule_conditions_fieldset_' . $this->getId();
    }

    /**
     * Get rule action instance
     *
     * @return \PluginCompany\CustomerGroupSwitching\Model\Rule\Condition\Combine
     */
    public function getActionsInstance()
    {
        return $this->conditionCombineFactory->create();
    }

    /**
     * Process rules by customer and optionally by order
     *
     * @param DataObject $customer
     * @param $event
     * @param null $order
     * @param bool $canSave
     * @param bool $allowedRuleIds
     * @return $this
     */
    public function processRules(
        DataObject $customer,
        $event,
        $order = null,
        $canSave = false,
        $allowedRuleIds = false
    ) {
        $this->initCustomerData($customer);
        $this->customer = $customer;
        $this->order = $order;
        $this->allowedRuleIds = $allowedRuleIds;

        foreach ($this->getFilteredRuleCollectionForProcessing() as $rule) {
            /* @var $rule Rule */
            if(!$rule->isApplicableForCustomer($this->customer)){
                continue;
            }
            if(!$rule->isApplicableForEvent($event)){
                continue;
            }

            $validated = $rule
                ->setCustomerId(
                    $this->customer->getId()
                )
                ->validate(
                    $this->getValidationObjectForRule($rule)
                );

            if ($validated) {
                $this->applyRule($rule, $this->customer);
                if ($rule->getStopRulesProcessing()) {
                    break;
                }
            }
        }

        if(!$this->hasCustomerSwitched()){
            return $this;
        }

        if ($canSave) {
            $this->customer->save();
        }
        $this->sendNotifications(
            $this->customer,
            $this->lastProcessedRule
        );
        $this->registerOrderGroupIdIfApplicable(
            $this->customer->getGroupId()
        );
        return $this;
    }

    /**
     * @return ResourceModel\Rule\Collection
     */
    private function getFilteredRuleCollectionForProcessing()
    {
        return $this->getRulesCollection(
            $this->customer->getWebsiteId(),
            $this->getCurrentDate(),
            $this->allowedRuleIds
        );
    }

    /**
     * Get rule collection for current date
     *
     * @param $storeId
     * @param $date
     * @param bool|array $allowedIds
     * @return ResourceModel\Rule\Collection
     */
    private function getRulesCollection($storeId, $date, $allowedIds = false)
    {
        $rules = $this->getCollection();
        $rules
            ->addFieldToFilter('store_ids',
                [
                    ['finset' => $storeId],
                    ['finset' => 0]
                ]
            )
            ->addFieldToFilter(
                'from_date', [
                    ['lteq' => $date],
                    ['null' => true]
                ]
            )
            ->addFieldToFilter(
                'to_date', [
                    ['gteq' => $date],
                    ['null' => true]
                ]
            )
            ->addFieldToFilter('is_active',1)
            ->setOrder('sort_order','ASC')
            ->setOrder('rule_id','ASC')
        ;
        if($allowedIds && is_array($allowedIds)){
            $rules->addFieldToFilter(
                'rule_id', [
                    'in' => $allowedIds
                ]
            );
        }
        return $rules;
    }

    private function getCurrentDate()
    {
        return date('Y-m-d H:i:s', $this->getCurrentTimeStamp());
    }

    private function getCurrentTimeStamp()
    {
        return $this->dateTimeFactory
            ->create()
            ->timestamp(time());
    }

    public function isApplicableForCustomer($customer)
    {
        if(!$this->isCustomerInFromGroup($customer)){
            return false;
        }
        if($this->isCustomerInToGroup($customer)){
            return false;
        }
        return true;
    }

    public function isCustomerInFromGroup($customer)
    {
        if($this->usesAllCustomersAsFromGroup()){
            return true;
        }
        if (in_array($customer->getGroupId(), $this->getFromGroupsArray())){
            return true;
        }
        return false;
    }

    public function usesAllCustomersAsFromGroup()
    {
        return in_array('32000', $this->getFromGroupsArray());
    }

    public function getFromGroupsArray()
    {
        return explode(',', $this->getOldCustomergroup());
    }

    public function isCustomerInToGroup($customer)
    {
        return in_array($customer->getGroupId(), $this->getToGroupsArray());
    }

    public function getToGroupsArray()
    {
        return explode(',', $this->getNewCustomergroup());
    }

    public function isApplicableForEvent($eventCode)
    {
        if($eventCode == 'bulk_group_switch'){
            return true;
        }
        if(in_array($eventCode, $this->getEventsArray())){
            return true;
        }
        return false;
    }

    public function getEventsArray()
    {
        return explode(',',$this->getEvents());
    }

    private function getValidationObjectForRule(Rule $rule)
    {
        if(!$this->order){
            return $this->getValidationObject();
        }
        if ($rule->isApplicableForOrderStatus($this->order->getStatus())) {
            return $this->getValidationObjectWithOrder();
        }
        return $this->getValidationObjectWithoutOrder();
    }

    public function isApplicableForOrderStatus($statusCode)
    {
        return in_array($statusCode, $this->getOrderStatusArray());
    }

    public function getOrderStatusArray()
    {
        return explode(',', $this->getOrderStatus());
    }

    private function getValidationObjectWithOrder()
    {
        return $this->getValidationObject()
            ->setCurrentOrder($this->order);
    }

    private function getValidationObjectWithoutOrder()
    {
        return $this->getValidationObject()
            ->unsCurrentOrder();
    }

    private function hasCustomerSwitched()
    {
        if(!$this->lastProcessedRule){
            return false;
        }
        return $this->customer->getOriginalGroupId() != $this->customer->getGroupId();
    }

    /**
     * @return DataObject
     */
    private function getValidationObject()
    {
        if(!$this->validationObject){
            $this->initValidationObject();
        }
        return $this->validationObject;
    }

    private function initValidationObject()
    {
        $this->validationObject = $this->dataObjectFactory->create();
        $this->validationObject->setData($this->getCustomerData());
        return $this;
    }

    /**
     * Send Email Notification
     *
     * @param DataObject $customer
     * @param $rule
     * @return $this
     */
    public function sendNotifications(DataObject $customer, $rule)
    {
        //load customer store
        if($customer->getStoreId()){
            $store = $this->storeRepository->getById($customer->getStoreId());
        }
        else{
            $store = $this->storeManager->getWebsite($customer->getWebsiteId())->getDefaultStore();
        }

        //Template Variables
        $emailTemplateVariables = [
            'from_group' => $this->getCustomerGroupCode($customer->getOriginalGroupId()),
            'to_group'   => $this->getCustomerGroupCode($customer->getGroupId()),
            'customer'   => $customer,
            'store'      => $store
        ];

        if($rule->getCustomerNotification()){
            $toEmail = $customer->getEmail();
            $toName = $customer->getName();
            $subject = $rule->getCustomerNotificationSubject();
            $body = $rule->getCustomerNotificationContents();

            $this->sendEmail($toEmail, $toName, $subject, $body, $emailTemplateVariables);
        }

        if($rule->getAdminNotification()){

            $toEmail = $rule->getAdminNotificationEmail() ? $rule->getAdminNotificationEmail() : $this->scopeConfig->getValue('trans_email/ident_general/email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $toName = $this->scopeConfig->getValue('trans_email/ident_general/name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $subject = $rule->getAdminNotificationSubject();
            $body = $rule->getAdminNotificationContents();

            $this->sendEmail($toEmail, $toName, $subject, $body, $emailTemplateVariables);
        }

        return $this;
    }

    private function getCustomerGroupCode($groupId)
    {
        return $this->getCustomerGroup($groupId)->getCode();
    }

    private function getCustomerGroup($groupId)
    {
        return $this->customerGroupRepository
            ->getById($groupId);
    }

    /**
     * Send Email
     *
     * @param string $toEmail
     * @param string $toName
     * @param string $subject
     * @param string $body
     * @param array $emailTemplateVariables
     * @return $this
     */
    public function sendEmail($toEmail, $toName, $subject, $body, $emailTemplateVariables)
    {
        try{
            $this->mailer
                ->setTemplateVars($emailTemplateVariables)
                ->setToEmail($toEmail)
                ->setToName($toName)
                ->setFromEmail($this->getNotificationFromEmail())
                ->setFromName($this->getNotificationFromName())
                ->setReplyTo($this->getNotificationFromEmail())
                ->setBody($body)
                ->setSubject($subject)
                ->sendMail()
            ;
        }catch(\Throwable $e) {
            $this->_logger->critical($e->getMessage());
        } catch(\Exception $e) {
            $this->_logger->critical($e->getMessage());
        }
        return $this;
    }

    private function getNotificationFromEmail()
    {
        return $this->scopeConfig->getValue(
            'trans_email/ident_general/email',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    private function getNotificationFromName()
    {
        return $this->scopeConfig->getValue(
            'trans_email/ident_general/name',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }


    /**
     * Apply validated rule on customer
     *
     * @param DataObject $rule
     * @param DataObject $customer
     * @return $this
     */
    public function applyRule(DataObject $rule, DataObject $customer)
    {
        //get next group for round robin distribution
        $newGroupId = $this->getNextGroup($rule);

        $customer
            ->setGroupId($newGroupId);

        $this
            ->setGroupIdToSession($newGroupId)
            ->addGroupIdRegistryEntry($newGroupId)
        ;

        $this->lastProcessedRule = $rule;

        $rule
            ->setExecutedCount($rule->getExecutedCount() + 1)
            ->save()
        ;

        return $this;
    }

    private function setGroupIdToSession($groupId){
        if($this->isCurrentAreaAdmin()){
            return $this;
        }

        //checkout session
        $this->getCustomerFromQuote()
            ->setGroupId($groupId);
        $this->getQuote()->setCustomerGroupId($groupId);
        $this->checkoutSession->setCustomerGroupId($groupId);

        //customer session
        $this->customerSession->setCustomerGroupId($groupId);
        $sCust = $this->customerSession->getCustomer();
        if($sCust){
            $sCust->setGroupId($groupId);
        }
        return $this;
    }

    private function addGroupIdRegistryEntry($groupId)
    {
        if($this->_registry->registry('pc_new_customer_group_id')){
            $this->_registry->unregister('pc_new_customer_group_id');
        }
        $this->_registry->register('pc_new_customer_group_id', $groupId);
        return $this;
    }

    private function isCurrentAreaAdmin()
    {
        return $this->appState->getAreaCode() == 'adminhtml';
    }

    private function registerOrderGroupIdIfApplicable($newId)
    {
        if($this->canRegisterOrderGroupId()){
            $this->registerOrderGroupId($newId);
        }
        return $this;
    }

    private function canRegisterOrderGroupId()
    {
        if($this->isCurrentAreaAdmin()){
            return false;
        }
        return $this->lastProcessedRule && $this->lastProcessedRule->getApplyNewGroupToOrder();
    }

    private function registerOrderGroupId($newId)
    {
        $quote = $this->getQuote();
        $quote->setCustomerGroupId($newId);
        return $this;
    }

    /**
     * Get next group ID for round robin distribution
     *
     * @param $rule
     * @return mixed
     */
    public function getNextGroup($rule)
    {
        $toGroups = explode(',',$rule->getNewCustomergroup());
        $count = count($toGroups);
        $executed = $rule->getExecutedCount();

        if ($executed < $count) {
            $group = $toGroups[$executed];
        }else{
            $key = $executed % $count;
            $group = $toGroups[$key];
        }
        return $group;
    }

    /**
     * get customer data array
     *
     * @return array
     */
    private function getCustomerData()
    {
        if ($this->customerData) {
            return $this->customerData;
        }
        return [];
    }

    /**
     * Get complete product order history for given period
     * (for current customer and order statuses)
     *
     * @param $from
     * @param $to
     * @return bool|\Magento\Sales\Model\ResourceModel\Order\Item\Collection
     */
    public function getProductHistory($from,$to)
    {
        if (!$this->getCustomerId()) {
            return false;
        }

        $orderTable = $this->resourceConnection->getTableName('sales_order');
        $collection = $this->orderItemCollectionFactory->create();
        $collection
            ->getSelect()
            ->joinLeft(['orders' => $orderTable], 'orders.entity_id=main_table.order_id')
            ->where('orders.customer_id=?', $this->getCustomerId())
            ->where("orders.status IN ({$this->getOrderStatusSqlString()})")
            ->group('product_id')
        ;

        if ($from) {
            $collection
                ->getSelect()
                ->where('orders.created_at > ?', $from);
        }

        if ($to) {
            $collection
                ->getSelect()
                ->where('orders.created_at < ?', $to);
        }

        $collection
            ->addExpressionFieldToSelect('product_qty', 'SUM(main_table.qty_ordered)', [])
            ->addExpressionFieldToSelect('product_turnover_incl_tax', 'SUM(main_table.base_row_total_incl_tax)', [])
            ->addExpressionFieldToSelect('product_turnover_excl_tax', 'SUM(main_table.base_row_total)', [])
        ;

        return $collection;
    }

    /**
     * Prepare order status string for SQL IN clause
     *
     * @return array|string
     */
    protected function getOrderStatusSqlString()
    {
        $statusArray = explode(',', $this->getOrderStatus());

        //generate status string for IN clause
        $sqlStatus = [];
        foreach ($statusArray as $item) {
            $sqlStatus[] = "'" . $item . "'";
        }
        $sqlStatus = implode(',', $sqlStatus);
        return $sqlStatus;
    }

    /**
     * Get order totals for customer and current rule statuses for given period
     *
     * @param $from
     * @param $to
     * @return bool|DataObject
     */
    public function getOrderTotals($from,$to)
    {
        if (!$this->getCustomerId()) {
            return false;
        }

        $collection = $this->orderCollectionFactory->create();
        $collection
            ->addFieldToFilter('customer_id', $this->getCustomerId())
            ->addExpressionFieldToSelect('order_base_subtotal_incl_tax', 'SUM(base_subtotal_incl_tax)', [])
            ->addExpressionFieldToSelect('order_base_subtotal', 'SUM(base_subtotal)', [])
            ->addExpressionFieldToSelect('order_base_grand_total', 'SUM(base_grand_total)', [])
            ->addExpressionFieldToSelect('order_base_total_invoiced', 'SUM(base_total_invoiced)', [])
            ->addExpressionFieldToSelect('order_base_total_invoiced_ex_tax', '(SUM(base_total_invoiced) - IFNULL(SUM(base_tax_invoiced),0))', [])
            ->addExpressionFieldToSelect('order_base_total_invoiced_minus_refunded', '(SUM(base_total_invoiced) - IFNULL(SUM(base_total_refunded),0))', [])
            ->addExpressionFieldToSelect(
                'order_base_total_invoiced_minus_refunded_ex_tax',
                '((SUM(base_total_invoiced) - IFNULL(SUM(base_tax_invoiced),0)) - (IFNULL(SUM(base_total_refunded),0) - IFNULL(SUM(base_tax_refunded),0)))',
                [])
            ->addExpressionFieldToSelect('order_base_total_paid', 'SUM(base_total_paid)', [])
            ->addExpressionFieldToSelect('order_base_total_refunded', 'SUM(base_total_refunded)', [])
            ->addExpressionFieldToSelect('order_base_total_paid_minus_refunded', '(SUM(base_total_paid) - IFNULL(SUM(base_total_refunded),0))', [])
            ->addExpressionFieldToSelect('order_base_shipping_incl_tax', 'SUM(base_shipping_incl_tax)', [])
            ->addExpressionFieldToSelect('order_base_shipping_amount', 'SUM(base_shipping_amount)', [])
            ->addExpressionFieldToSelect('order_total_qty_ordered', 'SUM(total_qty_ordered)', [])
            ->addExpressionFieldToSelect('order_weight', 'SUM(weight)', [])
            ->getSelect()
            ->where("status IN ({$this->getOrderStatusSqlString()})")
            ->group('customer_id')
        ;

        if ($from) {
            $collection
                ->getSelect()
                ->where('created_at > ?', $from);
        }

        if ($to) {
            $collection
                ->getSelect()
                ->where('created_at < ?', $to);
        }

        return $collection->getFirstItem();
    }

    /**
     * Get order collection for customer and current rule statuses for given period
     *
     * @param $from
     * @param $to
     * @return \Magento\Sales\Model\ResourceModel\Order\Collection
     */
    public function getOrderCollection($from,$to)
    {
        if (!$this->getCustomerId()) {
            return false;
        }

        $collection = $this->orderCollectionFactory->create();
        $collection
            ->addFieldToFilter('customer_id', $this->getCustomerId())
            ->getSelect()
            ->where("status IN ({$this->getOrderStatusSqlString()})")
        ;

        if ($from) {
            $collection
                ->getSelect()
                ->where('created_at > ?', $from);
        }

        if ($to) {
            $collection
                ->getSelect()
                ->where('created_at < ?', $to);
        }

        return $collection;
    }

    /**
     * Get invoice totals for customer and current rule statuses for given period
     *
     * @param $from
     * @param $to
     * @return bool|DataObject
     */
    public function getInvoiceTotals($from,$to)
    {
        if (!$this->getCustomerId()) {
            return false;
        }

        $collection = $this->orderCollectionFactory->create();
        $collection
            ->addFieldToFilter('customer_id', $this->getCustomerId())
            ->addExpressionFieldToSelect('invoice_base_subtotal_incl_tax', 'SUM(invoice.base_subtotal_incl_tax)', [])
            ->addExpressionFieldToSelect('invoice_base_subtotal', 'SUM(invoice.base_subtotal)', [])
            ->addExpressionFieldToSelect('invoice_base_grand_total', 'SUM(invoice.base_grand_total)', [])
            ->addExpressionFieldToSelect('invoice_base_shipping_incl_tax', 'SUM(invoice.base_shipping_incl_tax)', [])
            ->addExpressionFieldToSelect('invoice_base_shipping_amount', 'SUM(invoice.base_shipping_amount)', [])
            ->addExpressionFieldToSelect('invoice_total_qty', 'SUM(invoice.total_qty)', [])
            ->getSelect()
            ->where("invoice.state IN ({$this->getInvoiceStatus()})")
            ->joinInner(['invoice'=> 'sales_invoice'],'invoice.order_id=main_table.entity_id')
            ->group('main_table.customer_id')
        ;

        if ($from) {
            $collection
                ->getSelect()
                ->where('invoice.created_at > ?', $from);
        }

        if ($to) {
            $collection
                ->getSelect()
                ->where('invoice.created_at < ?', $to);
        }

        return $collection->getFirstItem();
    }


    /**
     * Init customer data
     *
     * @param DataObject $customer
     * @return $this
     */
    public function initCustomerData(DataObject $customer)
    {
        $billingData = $this->getCustomerBilling($customer);
        $shippingData = $this->getCustomerShipping($customer);
        $customerAttribtues = $this->getCustomerAttributes($customer);
        $customer->setOriginalGroupId($customer->getGroupId());

        $data = array_merge($billingData, $shippingData,$customerAttribtues);
        $this->customerData = $data;

        return $this;
    }

    /**
     * Get customer attribute array for validation
     *
     * @param DataObject $customer
     * @return array
     */
    private function getCustomerAttributes(DataObject $customer)
    {
        //check if data is set by post on customer model
        if($this->checkCustomerSubscribed($customer) === true){
            $subscribed = 1;
        }else{
            $subscribed = 0;
        }

        //calculate account age
        $date1 = new \DateTime(date('Y-m-d',strtotime($customer->getCreatedAt())));
        $date2 = new \DateTime(date('Y-m-d'));
        $accountAge = $date2->diff($date1)->format("%a");

        //calculate customer age
        $bdate = $customer->getDob();
        if (!$bdate) {
            $bdate = date('Y-m-d');
        }
        $age = date_diff(date_create($bdate), date_create('now'))->y;

        $data = array_merge(['age' => $age, 'account_age' => $accountAge, 'newsletter' => $subscribed],$customer->getData());

        return $data;
    }

    private function checkCustomerSubscribed($customer)
    {
        return $this->subscriberFactory
            ->create()
            ->loadByCustomerId($customer->getId())
            ->isSubscribed()
            ;
    }

    /**
     * Get customer billing address for validation
     *
     * @param DataObject $customer
     * @return array
     */
    private function getCustomerBilling(DataObject $customer)
    {
        //try customer object from event
        $billing = $this->getBillingFromCustomerObject($customer);

        //try quote session customer
        if(!$billing){
            $billing = $this->getDefaultBillingFromQuote();
        }

        $data = [];
        if ($billing) {
            foreach ($billing->getData() as $k => $v) {
                $data['billing_' . $k] = $v;
            }
        }
        return $data;
    }

    private function getBillingFromCustomerObject($customer)
    {
        $billing = $customer->getDefaultBillingAddress();
        if(!$billing && $customer->getDefaultBilling()){
            $billing = $this->customerAddressRepository->getById(
                $customer->getDefaultBilling()
            );
        }
        return $billing;
    }

    private function getDefaultBillingFromQuote()
    {
        $addresses = $this->getCustomerAddressesFromQuote();
        if(!$addresses) return false;

        foreach($addresses as $address){
            if($address->getIsDefaultBilling()){
                return $address;
            }
        }
        return false;
    }


    private function getCustomerAddressesFromQuote()
    {
        return $this
            ->getCustomerFromQuote()
            ->getAddresses()
            ;
    }

    private function getCustomerFromQuote()
    {
        return $this->getQuote()->getCustomer();
    }

    public function getQuote()
    {
        return $this->checkoutSession->getQuote();
    }

    /**
     * get customer shipping address for validation
     *
     * @param DataObject $customer
     * @return array
     */
    private function getCustomerShipping(DataObject $customer)
    {
        //try customer event object
        $shipping = $this->getShippingFromCustomerObject($customer);

        //try customer quote session object
        if(!$shipping){
            $shipping = $this->getDefaultShippingFromQuote();
        }

        $data = [];
        if ($shipping) {
            foreach ($shipping->getData() as $k => $v) {
                $data['shipping_' . $k] = $v;
            }
        }
        return $data;
    }

    private function getShippingFromCustomerObject($customer)
    {
        $shipping = $customer->getDefaultShippingAddress();
        if(!$shipping && $customer->getDefaultShipping()){
            $shipping = $this->customerAddressRepository->getById($customer->getDefaultShipping());
        }
        return $shipping;
    }

    private function getDefaultShippingFromQuote()
    {
        $addresses = $this->getCustomerAddressesFromQuote();
        if(!$addresses) return false;

        foreach($addresses as $address){
            if($address->getIsDefaultShipping()){
                return $address;
            }
        }
        return false;
    }

    public function applySelectedRulesForCustomerRange($offset, $limit, $ruleIds)
    {
        if(!is_array($ruleIds)){
            $ruleIds = [$ruleIds];
        }

        //load customer collection
        $customerCollection = $this->customerCollectionFactory->create()
            ->addAttributeToSelect('*')
        ;
        $customerCollection
            ->getSelect()
            ->limit($limit,$offset);

        $result = [];
        //apply rule to each customer
        foreach($customerCollection as $customer){
            //apply rule
            $this->groupswitchRuleFactory->create()
                ->processRules($customer, 'bulk_group_switch', null, true, $ruleIds);

            //get from / to group
            $fromGroup = $this->customerGroupRepository->getById(
                $customer->getOrigData('group_id')
            )->getCode();

            $toGroup = $this->customerGroupRepository->getById(
                $customer->getGroupId()
            )->getCode();

            if($fromGroup == $toGroup){
                $toGroup = "No group change";
            }

            //add to results
            $result[] = [
                'customer_id' => $customer->getId(),
                'customer_name' => $customer->getName(),
                'from_group' => $fromGroup,
                'to_group' => $toGroup,
                'date' => $this->getCurrentDate()
            ];
        }

        return $result;

    }

}

