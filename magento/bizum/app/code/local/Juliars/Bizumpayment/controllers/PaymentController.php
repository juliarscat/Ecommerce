<?php

class Juliars_Bizumpayment_PaymentController extends Mage_Core_Controller_Front_Action
{
    public function redirectAction()
    {
        $this->loadLayout();
        $block = $this->getLayout()->createBlock('Mage_Core_Block_Template', 'bizumpayment', array('template' => 'bizumpayment/redirect.phtml'));
        $this->getLayout()->getBlock('content')->append($block);
        $this->renderLayout();
    }

    public function responseAction()
    {
        $response = $this->getRequest()->getPost();

        // Process Bizum Payment response here

        if (isset($response['status']) && $response['status'] == 'success') {
            $order = Mage::getModel('sales/order')->loadByIncrementId($response['order_id']);
            if ($order->getId()) {
                $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true, 'Payment Success.');
                $order->save();
                Mage::getSingleton('checkout/session')->unsQuoteId();
                Mage::getSingleton('core/session')->addSuccess('Payment was successful.');
                $this->_redirect('checkout/onepage/success');
            }
        } else {
            $this->_redirect('checkout/cart');
        }
    }
}
