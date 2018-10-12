<?php
/**
 *
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
 *
 */
namespace PluginCompany\CustomerGroupSwitching\Model;

use Magento\Framework\Flag;

class Cronflag extends Flag {

    protected $_flagCode = 'pc_groupswitch_cron';

    public function saveCustomerIdAndTimestamp($id)
    {
        $this->setFlagData([
            'customer_id' => $id,
            'time' => time()
        ]);
        $this->save();
    }

    public function getLastCustomerId(){
        $data = $this->getFlagData();
        return isset($data['customer_id']) ? $data['customer_id'] : 0;
    }

    public function getLastDate(){
        $data = $this->getFlagData();
        return isset($data['time']) ? $data['time'] : 0;
    }

    public function hasProcessedToday(){
        if(date('Ymd') == date('Ymd', $this->getLastDate())){
            return true;
        }
        return false;
    }
}