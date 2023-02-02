<?php

class Juliars_Bizumpayment_Model_Payment extends Mage_Payment_Model_Method_Abstract
{
    protected $_code = 'bizumpayment';
    protected $_formBlockType = 'bizumpayment/form';
    protected $_infoBlockType = 'bizumpayment/info';

    public function assignData($data)
    {
        $info = $this->getInfoInstance();
        $info->setAdditionalInformation('Bizum payment', $data->getBizumpayment());
        return $this;
    }
}
