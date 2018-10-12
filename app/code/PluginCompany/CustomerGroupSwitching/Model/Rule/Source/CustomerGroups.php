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

use Magento\Customer\Model\Customer\Source\Group;

class CustomerGroups extends Group
{
    /**
     * Return array of customer groups
     *
     * @return array
     */
    public function toOptionArray()
    {
        $groups = parent::toOptionArray();
        foreach($groups as $key => $group)
        {
            if($group['value'] == 0)
            {
                unset($groups[$key]);
            }
        }

        return $groups;
    }

}

