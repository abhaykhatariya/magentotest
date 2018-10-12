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

use Magento\Email\Model\TemplateFactory;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\DataObject;

class Mailer extends TransportBuilder
{

    private $mailData;
    private $templateFilter;

    public function setReplyTo($value, $name = null)
    {
        $this->getMailData()
            ->setReplyTo($value);
        return $this;
    }

    public function setFromEmail($value)
    {
        $this->getMailData()
            ->setFromEmail($value);
        return $this;
    }

    public function setFromName($value)
    {
        $this->getMailData()
            ->setFromName($value);
        return $this;
    }

    public function setSubject($value)
    {
        $this->getMailData()
            ->setSubject($value);
        return $this;
    }

    public function setBcc($value)
    {
        $this->getMailData()
            ->setBcc($value);
        return $this;
    }

    public function setToName($value)
    {
        $this->getMailData()
            ->setToName($value);
        return $this;
    }

    public function setToEmail($value)
    {
        $this->getMailData()
            ->setToEmail($value);
        return $this;
    }

    public function setBody($value)
    {
        $this->getMailData()
            ->setBody($value);
        return $this;
    }

    public function setTemplateVars($vars)
    {
        $this->getTemplateFilter()
            ->setVariables($vars);
        return $this;
    }

    public function getTemplateFilter()
    {
        if(!$this->templateFilter)
            $this->initTemplateFilter();

        return $this->templateFilter;
    }

    private function initTemplateFilter()
    {
        $this->templateFilter =
            $this->templateFactory
                ->get(null)
                ->getTemplateFilter();
        return $this;
    }

    /**
     * @return DataObject
     */
    public function getMailData()
    {
        if(!$this->mailData){
            $this->initMailData();
        }
        return $this->mailData;
    }

    private function initMailData()
    {
        $this->mailData = new DataObject();
        return $this;
    }

    public function resetMailData()
    {
        return $this->initMailData();
    }

    private function getBodyPrefix()
    {
        return '<html><body style="font-family:calibri,arial,helvetica,sans-serif;font-size:11pt">';
    }

    private function getBodySuffix()
    {
        return '</body></html>';
    }

    public function sendMail()
    {
        $this->reset();
        $this
            ->resetMessage()
            ->filterMailData()
        ;
        $mailData = $this->mailData;
        $this->message
            ->setBody($this->getSurroundedBodyHtml())
            ->setSubject($mailData->getSubject())
            ->setFrom($mailData->getFromEmail(), $mailData->getFromName())
            ->setReplyTo($mailData->getReplyTo())
            ->addBcc($this->getBcc())
            ->addTo($this->getToEmail(), $mailData->getToName())
        ;
        $this->getTransport()->sendMessage();
        $this->resetMessage();
        $this->reset();
        return $this;
    }

    public function resetMessage()
    {
        $this->message
            ->clearFrom()
            ->clearMessageId()
            ->clearRecipients()
            ->clearReplyTo()
            ->clearSubject();
        return $this;
    }

    private function filterMailData()
    {
        foreach($this->getMailData()->getData() as $k => $v)
        {
            $this->getMailData()
                ->setData($k, $this->getFilteredVar($v));
        }
        return $this;
    }

    private function getFilteredVar($v)
    {
        return $this->getTemplateFilter()->filter($v);
    }

    private function getSurroundedBodyHtml()
    {
        return $this->getBodyPrefix() . $this->getMailData()->getBody() . $this->getBodySuffix();
    }


    public function getToEmail()
    {
        return $this->explodeIfCommaDelimited(
            $this->mailData->getData('to_email')
        );
    }

    public function getBcc()
    {
        return $this->explodeIfCommaDelimited(
            $this->mailData->getData('bcc')
        );
    }

    private function explodeIfCommaDelimited($mail)
    {
        if(!is_array($mail) && stristr($mail,',')){
            return explode(',', $mail);
        }
        return $mail;
    }

    /**
     * @param mixed $mailData
     */
    public function setMailData($mailData)
    {
        $this->mailData = $mailData;
    }

    /**
     * Prepare message
     *
     * @return $this
     */
    protected function prepareMessage()
    {
        try{
            $this->message
                ->setMessageType('text/html');
            $this->message
                ->setBodyHtml($this->getSurroundedBodyHtml());
        }catch(\Exception $e){
            //todo error logging
        }
        return $this;
    }

}
