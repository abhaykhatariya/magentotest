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
namespace PluginCompany\CustomerGroupSwitching\Model\Rule\Source;;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory;

/**
 * Class OrderStatus
 */
class OrderStatus implements OptionSourceInterface
{
    private $orderStatusCollectionFactory;

    /**
     * OrderStatus constructor.
     * @param CollectionFactory $statusCollectionFactory
     */
    public function __construct
    (
        CollectionFactory $statusCollectionFactory
    )
    {
        $this->orderStatusCollectionFactory = $statusCollectionFactory;
        return $this;
    }
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->orderStatusCollectionFactory
            ->create()
            ->toOptionArray();
    }
}