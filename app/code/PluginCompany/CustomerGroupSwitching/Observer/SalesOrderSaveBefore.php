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

class SalesOrderSaveBefore extends AbstractObserver
{
    public function execute(Observer $observer)
    {
        try {
            $order = $observer->getEvent()->getOrder();
            if(!$order->getConvertingFromQuote()) return;

            $quote = $order->getQuote();
            $groupId = $quote->getCustomerGroupId();
            if(!$groupId) return;

            $order->setCustomerGroupId($groupId);
        } catch (\Exception $e){
            $this->logger->critical($e->getMessage());
        }
    }
}
