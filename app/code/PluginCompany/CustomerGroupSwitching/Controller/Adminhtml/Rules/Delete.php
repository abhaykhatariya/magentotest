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
namespace PluginCompany\CustomerGroupSwitching\Controller\Adminhtml\Rules;

use Magento\Backend\App\Action;
use PluginCompany\CustomerGroupSwitching\Model\RuleRepository;

class Delete extends Action
{
    const ADMIN_RESOURCE = 'PluginCompany_CustomerGroupSwitching::rules';

    /**
     * @param Action\Context $context
     * @param RuleRepository
     */
    protected $ruleRepository;
    private $id;

    public function __construct(
        Action\Context $context,
        RuleRepository $ruleRepository
    ) {
        parent::__construct($context);
        $this->ruleRepository = $ruleRepository;
    }

    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $this->id = $this->getRequest()->getParam('id');

        if ($this->id) {
            try {
                return $this->processDelete();
            } catch (\Exception $e) {
                return $this->handleError($e);
            }
        }
        return $this->handleNoIdError();
    }

    private function processDelete()
    {
        $this
            ->deleteById($this->id)
            ->addSuccess('Automatic Group Switching Rule deleted')
        ;
        return $this->createRedirect()
            ->setPath('*/*/');
    }

    private function deleteById($id)
    {
        $this->ruleRepository->deleteById($id);
        return $this;
    }

    private function addSuccess($message)
    {
        $this->messageManager->addSuccessMessage(__($message));
        return $this;
    }

    private function handleError($e)
    {
        $this->addError($e->getMessage());

        return $this->createRedirect()
            ->setPath(
                '*/*/edit',
                ['id' => $this->id]
            );
    }

    private function handleNoIdError()
    {
        $this->addError(__('Automatic Group Switching Rule does not exist'));
        return $this->createRedirect()->setPath('*/*/');
    }

    private function addError($message)
    {
        $this->messageManager
            ->addError(__($message));
        return $this;
    }

    private function createRedirect()
    {
        return $this->resultRedirectFactory->create();
    }

}