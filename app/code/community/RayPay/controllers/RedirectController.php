<?php

/**
 * Magento
 *
 * @category   RayPay
 * @package    RayPay
 * @copyright  Copyright (c) 2021 RayPay (https://raypay.ir)
 */

class RayPay_RedirectController extends Mage_Core_Controller_Front_Action
{

	protected $_redirectBlockType = 'raypay/redirect';
	protected $_successBlockType = 'raypay/success';
	protected $_sendNewOrderEmail = true;
	protected $_order = NULL;
	protected $_paymentInst = NULL;
	protected $_transactionID = NULL;
	protected function _expireAjax()
	{
		if (!$this->getCheckout()->getQuote()->hasItems()) {
			$this->getResponse()->setHeader('HTTP/1.1', '403 Session Expired');
			exit();
		}
	}

	public function getCheckout()
	{
		return Mage::getSingleton('checkout/session');
	}

	public function redirectAction()
	{
		$session = $this->getCheckout();
		$session->setraypayQuoteId($session->getQuoteId());
		$session->setraypayRealOrderId($session->getLastRealOrderId());
		$order = Mage::getModel('sales/order');
		$order->loadByIncrementId($session->getLastRealOrderId());
		$this->_order = Mage::getModel('sales/order')->loadByIncrementId($session->getLastRealOrderId());
		$this->_paymentInst = $this->_order->getPayment()->getMethodInstance();
		$this->getResponse()->setBody($this->getLayout()->createBlock($this->_redirectBlockType)->setOrder($order)->toHtml());
		$session->unsQuoteId();
	}

	public function successAction()
	{
		$session = $this->getCheckout();
		$session->unsraypayRealOrderId();
		$session->setQuoteId($session->getraypayQuoteId(true));
		$session->getQuote()->setIsActive(false)->save();
		$order = Mage::getModel('sales/order');
		$order->load($this->getCheckout()->getLastOrderId());
		$this->getResponse()->setBody($this->getLayout()->createBlock($this->_successBlockType)->setOrder($this->_order)->toHtml());
	}
}
