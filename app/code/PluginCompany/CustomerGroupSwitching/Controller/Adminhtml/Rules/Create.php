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
namespace PluginCompany\CustomerGroupSwitching\Controller\Adminhtml\Rules;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\ForwardFactory;

class Create extends Action
{
    const ADMIN_RESOURCE = 'PluginCompany_CustomerGroupSwitching::rules';

   /**
    * @var \Magento\Backend\Model\View\Result\Forward
    */
   protected $resultForwardFactory;

   /**
    * @param Context $context
    * @param ForwardFactory $resultForwardFactory
    */
   public function __construct(
       Context $context,
       ForwardFactory $resultForwardFactory
   ) {
       $this->resultForwardFactory = $resultForwardFactory;
       parent::__construct($context);
   }

   /**
    * Forward to edit
    *
    * @return \Magento\Backend\Model\View\Result\Forward
    */
   public function execute()
   {
       return $this->resultForwardFactory->create()
           ->forward('edit')
           ;
   }
}