<?php

/**
 * Magento
 *
 * @category   RayPay
 * @package    RayPay
 * @copyright  Copyright (c) 2021 RayPay (https://raypay.ir)
 */

class RayPay_Block_Form extends Mage_Payment_Block_Form
{
	protected function _construct()
	{
		parent::_construct();
		$this->setTemplate('raypay/form.phtml');
	}

	public function getPaymentImageSrc()
	{
        return $this->getSkinUrl ( 'images/raypay/logo.svg' );
	}
}
