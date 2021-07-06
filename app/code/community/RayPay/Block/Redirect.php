<?php

/**
 * Magento
 *
 * @category   RayPay
 * @package    RayPay
 * @copyright  Copyright (c) 2021 RayPay (https://raypay.ir)
 */

class RayPay_Block_Redirect extends Mage_Core_Block_Abstract
{
	protected function _toHtml()
	{
		$module = 'raypay';
		$payment = $this->getOrder()->getPayment()->getMethodInstance();
		$res = $payment->getUrl();

		if ($res->StatusCode == 200) {


			$html = '<html><body> <script type="text/javascript"> window.location = "" </script> </body></html>';
		}
		else {
			$html = '<html><body> <script type="text/javascript"> window.location = "' . Mage::getUrl('checkout/onepage/failure', array('_secure' => true)) . '" </script> </body></html>';
		}
		return $html;
	}
}
