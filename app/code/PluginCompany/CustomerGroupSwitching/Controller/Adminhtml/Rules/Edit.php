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

use PluginCompany\CustomerGroupSwitching\Controller\Adminhtml\Rules;

class Edit extends Rules
{
    const ADMIN_RESOURCE = 'PluginCompany_CustomerGroupSwitching::rules';

    /**
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $this->initParams();

        if (!$this->doesRuleExist() && !$this->isNewRule()) {
            return $this->handleInvalidIdError();
        }

        $this
            ->addSessionDataToRuleModel()
            ->registerCurrentRule()
            ->initResultPage()
            ->addBreadCrumbsToResultPage()
            ->addTitleToResultPage()
        ;

        return $this->resultPage;
    }

    private function addBreadCrumbsToResultPage()
    {
        $this->resultPage
            ->addBreadcrumb(
                $this->getPageBreadCrumb(),
                $this->getPageBreadCrumb()
            );
        return $this;
    }

    private function getPageBreadCrumb()
    {
        if($this->doesRuleExist()){
            return __('Edit Automatic Group Switching Rule');
        }
        return __('New Automatic Customer Group Switching Rule');
    }

    private function addTitleToResultPage()
    {
        $this->prependToResultPageTitle(
            __('Automatic Group Switching Rules')
        );
        $this->addSubtitleToPageTitle();
        return $this;
    }

    private function addSubtitleToPageTitle()
    {
        if($this->doesRuleExist()) {
            $this->prependToResultPageTitle(
                __('Edit Automatic Customer Group Switching Rule ID: ') . $this->getRuleId()
            );
        }else{
            $this->prependToResultPageTitle(
                __('New Automatic Group Switching Rule')
            );
        }
        return $this;
    }

    private function getRuleId()
    {
        return $this->ruleModel->getId();
    }

    private function prependToResultPageTitle($text)
    {
        $this->resultPage
            ->getConfig()->getTitle()
            ->prepend(
                $text
            );
        return $this;
    }
}