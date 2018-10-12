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
use Magento\Catalog\Model\ResourceModel\Product;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection;
use Magento\Framework\DataObject;
use Magento\Framework\Locale\FormatInterface;
use Magento\Rule\Model\Condition\Context;
use PluginCompany\CustomerGroupSwitching\Model\Rule\Condition\Compatibility\AbstractProduct;

class Allorders extends AbstractProduct
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
        array $data = [])
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
        $attributes['product_qty'] = __('Total Quantity Ordered');
        $attributes['product_turnover_incl_tax'] = __('Total Product Turnover (incl. tax)');
        $attributes['product_turnover_excl_tax'] = __('Total Product Turnover (excl. tax)');
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
            ->setProductQty($object->getProductQty())
            ->setProductTurnoverInclTax($object->getProductTurnoverInclTax())
            ->setProductTurnoverExclTax($object->getProductTurnoverExclTax())
        ;

        $valid = parent::validate($product);

        return $valid;
    }
}
