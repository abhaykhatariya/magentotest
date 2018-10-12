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

namespace PluginCompany\CustomerGroupSwitching\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface RuleRepositoryInterface
{


    /**
     * Save Rule
     * @param \PluginCompany\CustomerGroupSwitching\Api\Data\RuleInterface $rule
     * @return \PluginCompany\CustomerGroupSwitching\Api\Data\RuleInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    
    public function save(
        \PluginCompany\CustomerGroupSwitching\Api\Data\RuleInterface $rule
    );

    /**
     * Retrieve Rule
     * @param string $ruleId
     * @return \PluginCompany\CustomerGroupSwitching\Api\Data\RuleInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    
    public function getById($ruleId);

    /**
     * Retrieve Rule matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \PluginCompany\CustomerGroupSwitching\Api\Data\RuleSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete Rule
     * @param \PluginCompany\CustomerGroupSwitching\Api\Data\RuleInterface $rule
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    
    public function delete(
        \PluginCompany\CustomerGroupSwitching\Api\Data\RuleInterface $rule
    );

    /**
     * Delete Rule by ID
     * @param string $ruleId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    
    public function deleteById($ruleId);
}
