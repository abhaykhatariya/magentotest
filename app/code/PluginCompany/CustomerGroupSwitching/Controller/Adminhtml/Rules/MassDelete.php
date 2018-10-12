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
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use PluginCompany\CustomerGroupSwitching\Model\RuleRepository;

class MassDelete extends Action
{
    const ADMIN_RESOURCE = 'PluginCompany_CustomerGroupSwitching::rules';

    private $ruleRepository;
    private $deleted = 0;
    private $errors = 0;

    /**
     * @param Context $context
     * @param RuleRepository $ruleRepository
     */
    public function __construct(
        Context $context,
        RuleRepository $ruleRepository
    )
    {
        $this->ruleRepository = $ruleRepository;
        parent::__construct($context);
    }

    /**
     * @return RuleRepository
     */
    public function getRuleRepository()
    {
        return $this->ruleRepository;
    }

    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $this
            ->deleteRulesBasedOnPostData()
            ->addResultMessages()
        ;
        return $this->redirectToListing();
    }

    private function deleteRulesBasedOnPostData()
    {
        $ids = $this->getSelectedRuleIds();
        foreach ($ids as $id) {
            $this->tryDeleteRule($id);
        }
        return $this;
    }

    private function getSelectedRuleIds()
    {
        $ids = $this->getRequest()->getPost('selected', []);
        if(empty($ids) && !$this->hasRuleFilter()){
            $ids = $this->getAllRuleIds();
        };
        return $ids;
    }

    private function hasRuleFilter()
    {
        $excluded = $this->getRequest()->getParam('excluded');
        if($excluded == "false"){
            return false;
        }
        return true;
    }

    private function getAllRuleIds()
    {
        return $this->ruleRepository->getAllIds();
    }

    private function tryDeleteRule($id)
    {
        try{
            $this->getRuleRepository()->deleteById($id);
            $this->deleted++;
        }
        catch(\Exception $e) {
            $this->errors++;
        }
        return $this;
    }

    private function addResultMessages()
    {
        $this
            ->addSuccessMessage()
            ->addErrorMessage()
        ;
        return $this;
    }

    private function addSuccessMessage()
    {
        if(!$this->deleted) return $this;
        $this->messageManager
            ->addSuccessMessage(
                __('A total of %1 record(s) have been deleted.',
                    $this->deleted)
            );
        return $this;
    }

    private function addErrorMessage()
    {
        if(!$this->errors) return $this;
        $this->messageManager
            ->addErrorMessage(
                __('An error occured while deleting %1 record(s).',
                    $this->errors)
            );
        return $this;
    }

    private function redirectToListing()
    {
        return $this->createRedirect()
            ->setPath('groupswitch/rules/index');
    }

    private function createRedirect()
    {
        return $this->resultFactory
            ->create(ResultFactory::TYPE_REDIRECT);
    }
}