<?php

/**
 * Magento
 *
 * @category   RayPay
 * @package    RayPay
 * @copyright  Copyright (c) 2021 RayPay (https://raypay.ir)
 */

class RayPay_Block_Info extends Mage_Payment_Block_Info
{
	protected function _construct()
	{
		parent::_construct();
		$this->setTemplate('raypay/info.phtml');
	}
	public function getMethodCode()
	{
		return $this->getInfo()->getMethodInstance()->getCode();
	}
	public function toPdf()
	{
		$this->setTemplate('raypay/pdf/info.phtml');
		return $this->toHtml();
	}
}
