<?php

/**
 * Magento
 *
 * @category   RayPay
 * @package    RayPay
 * @copyright  Copyright (c) 2021 RayPay (https://raypay.ir)
 */

class RayPay_Helper_Data extends Mage_Payment_Helper_Data
{
	public function getMessage($messageNumber)
	{
		switch ($messageNumber) {
			case 100 :
				$msg = "تابع cURL در سرور فعال نمی باشد";
				break;
			case 101 :
				$msg = "در ارتباط با وب سرویس رای پی خطایی رخ داده است";
				break;
			case 102 :
				$msg = "اطلاعات ارسال شده مربوط به تایید تراکنش ناقص و یا غیر معتبر است";
				break;
			case 103 :
				$msg = "تراكنش با خطا مواجه شد و یا توسط پرداخت کننده کنسل شده است";
				break;
			case 104 :
				$msg = "در ارتباط با وب سرویس رای پی و بررسی تراکنش خطایی رخ داده است";
				break;
			default :
				$msg = "خطای ناشناخته ای در پروسه پرداخت رخ داد";
		}
		return $msg;
	}
}
