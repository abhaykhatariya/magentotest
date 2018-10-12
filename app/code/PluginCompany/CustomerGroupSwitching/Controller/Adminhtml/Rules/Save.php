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

use Magento\Framework\Exception\LocalizedException;
use PluginCompany\CustomerGroupSwitching\Controller\Adminhtml\Rules;

class Save extends Rules
{
    const ADMIN_RESOURCE = 'PluginCompany_CustomerGroupSwitching::rules';

    private $postData;

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $this->initParams();

        if (!$this->postData) {
            return $this->createRedirect()->setPath('*/*/');
        }
        if (!$this->doesRuleExist() && !$this->isNewRule()) {
            return $this->handleInvalidIdError();
        }

        return $this->executeSave();
    }

    private function executeSave()
    {
        try {
            $this->saveRuleBasedOnPostData();
            return $this->redirectAfterSave();
        }
        catch (LocalizedException $e) {
            $this->handleLocalizedException($e);
        }
        catch (\Exception $e) {
            $this->handleCriticalException($e);
        }

        $this->restoreDataPersistor();
        return $this->redirectToEditPage();
    }

    protected function initParams()
    {
        parent::initParams();
        $this->postData = $this->getRequest()->getPostValue();
    }

    private function saveRuleBasedOnPostData()
    {
        $this
            ->preparePostData()
            ->addPostDataToRule()
            ->saveRule()
            ->addSuccessMessage(__('You saved the Rule.'))
            ->clearDataPersistor()
        ;
        return $this;
    }

    private function preparePostData()
    {
        if (isset($this->postData['rule']['conditions'])) {
            $this->postData['conditions'] = $this->postData['rule']['conditions'];
        }
        unset($this->postData['rule']);
        unset($this->postData['conditions_serialized']);
        foreach($this->postData as $key => &$item)
        {
            if($key == 'conditions') continue;
            if(is_array($item)){
                $item = implode(',', $item);
            }
        }
        return $this;
    }

    private function addPostDataToRule()
    {
        $this->ruleModel
            ->loadPost($this->postData)
        ;
        return $this;
    }

    private function saveRule()
    {
        $this->ruleModel->save();
        return $this;
    }

    private function clearDataPersistor()
    {
        $this->dataPersistor->clear('groupswitch_rules_edit');
        return $this;
    }

    private function redirectAfterSave()
    {
        if ($this->getRequest()->getParam('back')) {
            return $this->redirectToEditPage();
        }
        if ($this->getRequest()->getParam('execute')) {
            return $this->redirectToBulkProcessPage();
        }
        return $this->createRedirect()->setPath('*/*/');
    }

    private function redirectToEditPage()
    {
        return $this->createRedirect()
            ->setPath('*/*/edit',
                ['rule_id' => $this->ruleModel->getId()]
            );
    }

    private function redirectToBulkProcessPage()
    {
        return $this->createRedirect()
            ->setPath('*/*/bulkprocess',
                ['selected' => $this->ruleModel->getId()]
            );
    }

    private function restoreDataPersistor()
    {
        $this->dataPersistor->set(
            'groupswitch_rules_edit',
            $this->getRequest()->getPostValue()
        );
        return $this;
    }

    private function handleLocalizedException($e)
    {
        $this->logger->critical($e->getMessage());
        $this->messageManager
            ->addErrorMessage($e->getMessage());
        return $this;
    }

    private function handleCriticalException($e)
    {
        $this->logger->critical($e->getMessage());
        $this->messageManager
            ->addExceptionMessage($e, __('Something went wrong while saving the Rule.'));
        return $this;
    }

}
