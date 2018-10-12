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
use Magento\Framework\DataObject;

class SalesOrderInvoiceSaveAfter extends AbstractObserver
{
    public function execute(Observer $observer)
    {
        try {
            $invoice = $observer->getEvent()->getInvoice();
            $order = $invoice->getOrder();
            $customer = $order->getCustomer();

            if(!$customer){
                $customer = new DataObject();
            }

            if (!$customer->getId() && $order->getCustomerId()) {
                $customer = $this->customerRegistry->retrieve(
                    $order->getCustomerId()
                );
            }elseif(!$customer->getId()){
                return;
            }
            //register to prevent infinite loop
            if (!$this->frameworkRegistry->registry('customer_group_switched')) {
                $this->frameworkRegistry->register('customer_group_switched', true);
            }
            //execute rules
            $this->ruleFactory->create()->processRules($customer, 'invoice_save', null, true);
        } catch (\Throwable $e){
            $this->logger->critical($e->getMessage());
        }
    }
}
