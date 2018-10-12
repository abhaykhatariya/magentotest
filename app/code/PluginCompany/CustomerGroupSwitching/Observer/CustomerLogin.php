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
namespace PluginCompany\CustomerGroupSwitching\Observer;

use Magento\Framework\Event\Observer;

class CustomerLogin extends AbstractObserver
{
    public function execute(Observer $observer)
    {
        try {
            $customer = $observer->getEvent()->getCustomer();
            if(!$customer) return;
            if ($customer->getId() && !$this->frameworkRegistry->registry('customer_group_switched')) {
                //register to prevent infinite loop
                $this->frameworkRegistry->register('customer_group_switched', true);
                //execute rules
                $this->ruleFactory->create()->processRules($customer,'customer_login',null,true);
            }
        } catch (\Exception $e){
            $this->logger->critical($e->getMessage());
        }
    }
}
