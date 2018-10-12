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
namespace PluginCompany\CustomerGroupSwitching\Model\Rule\Condition\Compatibility;

class PeriodConfig
{
    /**
     * The period options.
     * Complete history, between dates, last xx days, before xx days
     *
     * @return array
     */
    public function getPeriodOptions()
    {
        $options = [
            'all' => 'since day one',
            'between' => 'on and between dates',
            'lessdaysago' => 'of the last',
            'moredaysago' => 'older than',
        ];
        return $options;
    }

}