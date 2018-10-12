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

class NewsletterSubscriberSaveAfter extends AbstractObserver
{
    public function execute(Observer $observer)
    {
        try {
            $subscriber = $observer->getEvent()->getSubscriber();
            //check if connected to customer account
            if (!$subscriber->getCustomerId()) {
                return;
            }

            $customer = $this->customerRegistry->retrieve(
                $subscriber->getCustomerId()
            );

            //register to prevent infinite loop
            if (!$this->frameworkRegistry->registry('customer_group_switched')) {
                $this->frameworkRegistry->register('customer_group_switched', true);
            }
            //execute rules
            $this->ruleFactory->create()->processRules($customer, 'newsletter_subscriber_save', null, true);

        } catch (\Throwable $e){
            $this->logger->critical($e->getMessage());
        }
    }
}
