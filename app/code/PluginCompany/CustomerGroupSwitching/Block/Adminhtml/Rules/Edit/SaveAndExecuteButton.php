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

namespace PluginCompany\CustomerGroupSwitching\Block\Adminhtml\Rules\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class SaveAndExecuteButton
    extends GenericButton
    implements ButtonProviderInterface
{

    /**
     * @return array
     */
    public function getButtonData()
    {
        return
            [
                'label' => __('Save and Execute'),
                'class' => 'save',
                'on_click' => '',
                'sort_order' => 80,
                'data_attribute' => [
                    'mage-init' => [
                        'Magento_Ui/js/form/button-adapter' => [
                            'actions' => [
                                [
                                    'targetName' => 'groupswitch_rules_form.groupswitch_rules_form',
                                    'actionName' => 'save',
                                    'params' => [
                                        true,
                                        ['execute' => 1],
                                    ]
                                ]
                            ]
                        ]
                    ],

                ]
            ];
    }

    /**
     * Get URL for delete button
     *
     * @return string
     */
    public function getDeleteUrl()
    {
        return $this->getUrl(
            '*/*/delete',
            [
                'id' => $this->getModelId()
            ]
        );
    }
}

