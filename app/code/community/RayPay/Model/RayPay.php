<?php

/**
 * Magento
 *
 * @category   RayPay
 * @package    RayPay
 * @copyright  Copyright (c) 2021 RayPay (https://raypay.ir)
 */

class RayPay_Model_raypay extends Mage_Payment_Model_Method_Abstract
{
	protected $_code = 'raypay';
	protected $_formBlockType = 'raypay/form';
	protected $_infoBlockType = 'raypay/info';
	protected $_isGateway = false;
	protected $_canAuthorize = true;
	protected $_canCapture = true;
	protected $_canCapturePartial = false;
	protected $_canRefund = false;
	protected $_canVoid = false;
	protected $_canUseInternal = false;
	protected $_canUseCheckout = true;
	protected $_canUseForMultishipping = false;
	protected $_order;

	public function getOrder()
	{
		if (!$this->_order) {
			$paymentInfo = $this->getInfoInstance();
			$this->_order = Mage::getModel('sales/order')->loadByIncrementId($paymentInfo->getOrder()->getRealOrderId());
		}
		return $this->_order;
	}

	public function validate()
	{
		$quote = Mage::getSingleton('checkout/session')->getQuote();
		$quote->setCustomerNoteNotify(false);
		parent::validate();
	}

	public function getOrderPlaceRedirectUrl()
	{
		return Mage::getUrl('raypay/redirect/redirect', array('_secure' => true));
	}

	public function capture(Varien_Object $payment, $amount)
	{
		$payment->setStatus(self::STATUS_APPROVED)->setLastTransId($this->getTransactionId());
		return $this;
	}

	public function getPaymentMethodType()
	{
		return $this->_paymentMethod;
	}

	public function getUrl()
	{
		require_once Mage::getBaseDir() . DS . 'lib' . DS . 'Zend' . DS . 'Log.php';

		$result = [];

		if (extension_loaded('curl')) {

			$orderId = $this->getOrder()->getRealOrderId();

			Mage::getSingleton('core/session')->setOrderId(Mage::helper('core')->encrypt($this->getOrder()->getRealOrderId()));

			$amount = intval($this->getOrder()->getGrandTotal());
			$redirectUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB) . 'index.php' . '/raypay/redirect/success'.'?order_id='. $orderId .'&';
			$description = 'پرداخت  فروشگاه مجنتو 1 با شماره سفارش ' . $orderId;
            $invoice_id             = round(microtime(true) * 1000);
            $user_id = Mage::helper('core')->decrypt($this->getConfigData('user_id'));
            $acceptor_code = Mage::helper('core')->decrypt($this->getConfigData('acceptor_code'));

            if ($this->getOrder()->getBillingAddress()->getEmail()) {
                $email = $this->getOrder()->getBillingAddress()->getEmail();
            } else {
                $email = $this->getOrder()->getCustomerEmail();
            }
            $name = $this->getOrder()->getBillingAddress()->getFirstname() . ' ' . $this->getOrder()->getBillingAddress()->getLastname();
            $mobile = $this->getOrder()->getShippingAddress()->getTelephone();

			$params = array(
                'amount'       => strval($amount),
                'invoiceID'    => strval($invoice_id),
                'userID'       => $user_id,
                'redirectUrl'  => $redirectUrl,
                'factorNumber' => strval($orderId),
                'acceptorCode' => $acceptor_code,
                'email'        => $email,
                'mobile'       => $mobile,
                'fullName'     => $name,
                'comment'      => $description
			);

			$result = self::common('https://api.raypay.ir/raypay/api/v1/Payment/getPaymentTokenWithUserID', $params);

			if ($result && isset($result->Data) && $result->StatusCode == 200) {

                $access_token = $result->Data->Accesstoken;
                $terminal_id  = $result->Data->TerminalID;

                echo '<p style="color:#ff0000; font:18px Tahoma; direction:rtl;">در حال اتصال به درگاه بانکی. لطفا صبر کنید ...</p>';
                echo '<form name="frmRayPayPayment" method="post" action=" https://mabna.shaparak.ir:8080/Pay ">';
                echo '<input type="hidden" name="TerminalID" value="' . $terminal_id . '" />';
                echo '<input type="hidden" name="token" value="' . $access_token . '" />';
                echo '<input class="submit" type="submit" value="پرداخت" /></form>';
                echo '<script>document.frmRayPayPayment.submit();</script>';

                exit();
			}
			else {
				$message = Mage::Helper('raypay')->getMessage(101);
				$message = isset($result->Message) ? $result->Message : $message;

				$this->getOrder();
				$this->_order->addStatusToHistory(Mage_Sales_Model_Order::STATE_CANCELED, $message, true);
				$this->_order->save();
				Mage::getSingleton('checkout/session')->setErrorMessage($message);
			}
		}
		else {

			$message = Mage::Helper('raypay')->getMessage(100);

			$this->getOrder();
			$this->_order->addStatusToHistory(Mage_Sales_Model_Order::STATE_CANCELED, $message, true);
			$this->_order->save();
			Mage::getSingleton('checkout/session')->setErrorMessage($message);
		}

		return $result;
	}

	public function getFormFields()
	{
		$orderId = $this->getOrder()->getRealOrderId();
		$params = array('x_invoice_num' => $orderId);
		return $params;
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
