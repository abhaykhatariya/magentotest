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
namespace PluginCompany\CustomerGroupSwitching\Model\Rule\Condition\Product;

use Magento\Backend\Helper\Data as HelperData;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product as ModelProduct;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\Product\Type\Configurable;
use Magento\Catalog\Model\ResourceModel\Product;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection;
use Magento\Framework\DataObject;
use Magento\Framework\Locale\FormatInterface;
use Magento\Rule\Model\Condition\Context;
use PluginCompany\CustomerGroupSwitching\Model\Rule\Condition\Compatibility\AbstractProduct;

class Neworder extends AbstractProduct
{

    public function __construct(
        Context $context,
        HelperData $backendData, 
        Config $config, 
        ProductFactory $productFactory, 
        ProductRepositoryInterface $productRepository, 
        Product $productResource, 
        Collection $attrSetCollection, 
        FormatInterface $localeFormat, 
        array $data = []
    )
    {
        parent::__construct($context, $backendData, $config, $productFactory, $productRepository, $productResource, $attrSetCollection, $localeFormat, $data);
    }

    /**
     * Add special attributes
     *
     * @param array $attributes
     */
    protected function _addSpecialAttributes(array &$attributes)
    {
        parent::_addSpecialAttributes($attributes);
        $attributes['quote_item_qty'] = __('Quantity in cart');
        $attributes['quote_item_price'] = __('Price in cart');
        $attributes['quote_item_row_total'] = __('Row total in cart');
    }

    /**
     * Validate Product Rule Condition
     *
     * @param DataObject $object
     *
     * @return bool
     */
    public function validateDataObject(DataObject $object)
    {
        $product = $object->getProduct();
        if (!($product instanceof ModelProduct)) {
            $product = $this->_productFactory->create()->load($object->getProductId());
        }

        $product
            ->setQuoteItemQty($object->getQty())
            ->setQuoteItemPrice($object->getPrice()) // possible bug: need to use $object->getBasePrice()
            ->setQuoteItemRowTotal($object->getBaseRowTotal());

        $valid = parent::validate($product);
        if (!$valid && $product->getTypeId() == Configurable::TYPE_CODE) {
            $children = $object->getChildren();
            $valid = $children && $this->validate($children[0]);
        }

        return $valid;
    }
}
