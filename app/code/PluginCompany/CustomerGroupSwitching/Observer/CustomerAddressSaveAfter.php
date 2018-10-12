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

class CustomerAddressSaveAfter extends AbstractObserver
{
    public function execute(Observer $observer)
    {
        try {
            if($this->_isCron()) return;
            if($this->_isBulk()) return;

            if($this->frameworkRegistry->registry('viv_after_address_save_processed') && !$this->frameworkRegistry->registry('address_processed_after_viv')){
                $this->frameworkRegistry->register('address_processed_after_viv',true);
            }elseif ($this->frameworkRegistry->registry('customer_group_switched') || $this->frameworkRegistry->registry('customer_group_switched_address')){ //TODO: remove _address??
                return;
            }

            $customer = $observer->getEvent()->getCustomerAddress()->getCustomer();
            if(!$customer) return;
            if ($customer->getId()) {
                //register to prevent infinite loop
                if (!$this->frameworkRegistry->registry('customer_group_switched_address')) {
                    $this->frameworkRegistry->register('customer_group_switched_address', true);
                }
                //execute rules
                $this->ruleFactory->create()->processRules($customer,'address_save',null,true);
            }
        } catch (\Exception $e){
            $this->logger->critical($e->getMessage());
        }
    }
}
