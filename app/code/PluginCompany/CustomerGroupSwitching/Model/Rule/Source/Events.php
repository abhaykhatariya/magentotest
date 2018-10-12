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
namespace PluginCompany\CustomerGroupSwitching\Model\Rule\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Status
 */
class Events implements OptionSourceInterface
{
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $events = [
            [
                'label' => 'Customer account creation & saving',
                'value' => 'customer_save'
            ],
            [
                'label' => 'Customer account log in',
                'value' => 'customer_login'
            ],
            [
                'label' => 'Order creation & saving',
                'value' => 'order_save'
            ],
            [
                'label' => 'Invoice creation & saving',
                'value' => 'invoice_save'
            ],
            [
                'label' => 'Default billing / shipping address saving',
                'value' => 'address_save'
            ],
            [
                'label' => 'Newsletter subscription creation & saving',
                'value' => 'newsletter_subscriber_save'
            ],
            [
                'label' => 'Cronjob group switching batch',
                'value' => 'customer_cron_job'
            ],
        ];
        return $events;
    }
}
