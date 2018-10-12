<?php
/*
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


namespace PluginCompany\CustomerGroupSwitching\Block\Adminhtml;

use Magento\Backend\Block\Widget\Grid\Container;

class Groupswitch extends Container
{

	public function __construct()
	{
        $this->_controller = "adminhtml_groupswitch";
        $this->_blockGroup = "groupswitch";
        $this->_headerText = __("Automatic Customer Group Switching Rules");
        $this->_addButtonLabel = __("Add New Rule");
        parent::__construct();
	}

}