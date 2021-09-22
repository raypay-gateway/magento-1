<?php

/**
 * Magento
 *
 * @category   RayPay
 * @package    RayPay
 * @copyright  Copyright (c) 2021 RayPay (https://raypay.ir)
 */

class RayPay_Block_Success extends Mage_Core_Block_Template
{
	protected function _toHtml()
	{
		require_once Mage::getBaseDir() . DS . 'lib' . DS . 'Zend' . DS . 'Log.php';
        $session = Mage::getSingleton('checkout/session');

        $orderId   = Mage::helper ( 'core' )->decrypt(Mage::getSingleton('core/session')->getOrderId());
        Mage::getSingleton('core/session')->unsOrderId();

        $this->_order = new Mage_Sales_Model_Order();
        $this->_order->loadByIncrementId($session->getLastRealOrderId());

		//$order = new Mage_Sales_Model_Order();
		$incrementId = Mage::getSingleton('checkout/session')->getLastRealOrderId();
		//$order->loadByIncrementId($incrementId);
        $payment = $this->_order->getPayment();
        if ($payment != false) {
            $this->_paymentInst = $this->_order->getPayment()->getMethodInstance();
        }
		$success = false;
		$message = Mage::Helper('raypay')->getMessage();

		if ( isset($_GET['order_id']) ) {
            $order_id = $_GET['order_id'];
            $result = self::common('https://api.raypay.ir/raypay/api/v1/Payment/verify', $_POST);

            if (empty($result->Data)) {
                $message = Mage::Helper('raypay')->getMessage(104) . ' خطای سرور : ' . $result->Message;
            } else {
                $state           = $result->Data->Status;
                $verify_order_id = $result->Data->FactorNumber;
                $verify_amount   = $result->Data->Amount;
                if (!empty($verify_order_id) && !empty($verify_amount) && $state == 1) {
                    $success = true;
                } else {
                    $message = Mage::Helper('raypay')->getMessage(103);
                }
            }
		}
		else {

			$message = Mage::Helper('raypay')->getMessage(102);
		}

		if ($success == true) {

			$invoice = $this->_order->prepareInvoice();
			$invoice->register()->capture();

			Mage::getModel('core/resource_transaction')->addObject($invoice)->addObject($invoice->getOrder())->save();

			$message = "پرداخت شما با موفقیت انجام شد.";
            $this->_order->addStatusToHistory($this->_paymentInst->getConfigData('second_order_status'), $message, true);
            $this->_order->save();

            $this->_order->sendNewOrderEmail();

			Mage::getSingleton('core/session')->addSuccess($message);

			$html = '<html><body> <script type="text/javascript"> window.location = "' . Mage::getUrl('checkout/onepage/success', array('_secure' => true)) . '" </script> </body></html>';
			return $html;
		}
		else {

			$this->_order = Mage::getModel('sales/order')->loadByIncrementId($incrementId);

            $this->_order->addStatusToHistory(Mage_Sales_Model_Order::STATE_CANCELED, $message, true);
            $this->_order->save();

			$this->_order->sendOrderUpdateEmail(true, $message);

			Mage::getSingleton('checkout/session')->setErrorMessage($message);

			$html = '<html><body> <script type="text/javascript"> window.location = "' . Mage::getUrl('checkout/onepage/failure', array('_secure' => true)) . '" </script></body></html>';
			return $html;
		}
	}

	private function common($url, $params)
	{
        $options = array('Content-Type: application/json');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HTTPHEADER,$options );
        $response = curl_exec($ch);
        $output = json_decode($response );
        curl_close($ch);
        return $output;
	}
}
