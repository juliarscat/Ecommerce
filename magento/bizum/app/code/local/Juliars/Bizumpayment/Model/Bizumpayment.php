<?php

class Juliars_Bizumpayment_Model_Bizumpayment extends Mage_Payment_Model_Method_Abstract
{
    protected $_code = 'bizumpayment';
    protected $_formBlockType = 'bizumpayment/form';
    protected $_infoBlockType = 'bizumpayment/info';
    protected $_canUseInternal = false;
    protected $_canUseForMultishipping = false;

    public function getOrderPlaceRedirectUrl()
    {
        return Mage::getUrl('bizumpayment/payment/redirect');
    }

    public function getFormFields()
    {
        $orderIncrementId = Mage::getSingleton('checkout/session')->getLastRealOrderId();
        $order = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);
        $amount = $order->getGrandTotal();
        $fields = array(
            'order_id' => $orderIncrementId,
            'amount' => $amount,
        );

        return $fields;
    }

    public function assignData($data)
    {
        $info = $this->getInfoInstance();
        $info->setAdditionalInformation('Bizum Number', $data->getBizumpaymentNumber());

        return $this;
    }

    public function validate()
    {
        parent::validate();

        $info = $this->getInfoInstance();
        $number = $info->getAdditionalInformation('Bizum Number');
        if (!$number) {
            $errorCode = 'invalid_data';
            $errorMsg = $this->_getHelper()->__("Bizum Number is required");
        }

        if ($errorMsg) {
            Mage::throwException($errorMsg);
        }

        return $this;
    }
}
