<?php
/**
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
namespace PluginCompany\CustomerGroupSwitching\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\Filter\Date;
use Magento\Framework\View\Result\PageFactory;
use PluginCompany\CustomerGroupSwitching\Model\RuleRepository;
use Psr\Log\LoggerInterface;

abstract class Rules extends Action
{
    const ADMIN_RESOURCE = 'PluginCompany_CustomerGroupSwitching::rules';
    /**
     * @var Registry
     */
    protected $coreRegistry = null;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var RuleRepository
     */
    protected $ruleRepository;

    protected $ruleId;
    protected $ruleModel;
    protected $resultPage;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;
    /**
     * @var Date
     */
    protected $date;
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param Action\Context $context
     * @param PageFactory $resultPageFactory
     * @param Registry $registry
     * @param DataPersistorInterface $dataPersistor
     * @param Date $date
     * @param LoggerInterface $logger
     * @param RuleRepository $ruleRepository
     */

    public function __construct(
        Action\Context $context,
        PageFactory $resultPageFactory,
        Registry $registry,
        DataPersistorInterface $dataPersistor,
        Date $date,
        LoggerInterface $logger,
        RuleRepository $ruleRepository
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->coreRegistry = $registry;
        $this->ruleRepository = $ruleRepository;
        $this->dataPersistor = $dataPersistor;
        $this->date = $date;
        $this->logger = $logger;
        parent::__construct($context);
    }

    protected function initParams()
    {
        $this->ruleId = $this->getRequest()->getParam('rule_id');
        $this->initRuleModel();
        return $this;
    }

    protected function initRuleModel()
    {
        $this->ruleModel =
            $this->ruleRepository
                ->getByIdOrNew($this->ruleId)
        ;
        return $this;
    }

    protected function doesRuleExist()
    {
        return $this->ruleId && $this->ruleModel->getId();
    }

    protected function isNewRule()
    {
        return (bool)$this->ruleId == false;
    }

    protected function handleInvalidIdError()
    {
        $this->messageManager
            ->addErrorMessage(__('This Automatic Group Switching Rule doesn\'t exist.'));
        return $this->createRedirect()->setPath('*/*/');
    }

    protected function addSessionDataToRuleModel()
    {
        $data = $this->_getSession()->getFormData(true);
        if (!empty($data)) {
            $this->ruleModel->setData($data);
        }
        return $this;
    }

    protected function registerCurrentRule()
    {
        $this->coreRegistry
            ->register('current_groupswitching_rule', $this->ruleModel);
        return $this;
    }

    protected function createRedirect()
    {
        return $this->resultRedirectFactory->create();
    }

    /**
     * Init Result Page
     */
    protected function initResultPage()
    {
        $this->resultPage = $this->resultPageFactory->create();
        return $this;
    }

    protected function addErrorMessage($message)
    {
        $this->messageManager->addErrorMessage($message);
        return $this;
    }

    protected function addSuccessMessage($message)
    {
        $this->messageManager->addSuccessMessage($message);
        return $this;
    }

}
