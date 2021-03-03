<?php

namespace Bitrix\Rest\Marketplace;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Type\Date;
use Bitrix\Rest\AppTable;
use Bitrix\Rest\Engine\Access;

Loc::loadMessages(__FILE__);

/**
 * Class Notification
 * @package Bitrix\Rest\Marketplace
 */
class Notification
{
	private const MODULE_ID = 'rest';
	private const OPTION_ACCESS_NOTIFICATION = 'rest_access_notification';
	private const OPTION_LAST_CHECK_ACCESS_NOTIFICATION = 'last_check_rest_access_notify';
	private const OPTION_LAST_CHECK_NOTIFICATION = 'last_check_rest_notify';
	private static $timestampNotifyDays = 259200; // 3 * 86400
	private static $codeToNotification = [
		'rest_buy' => 'REST_BUY',
		'limit_subscription_market_demomarket_end' => 'SUBSCRIPTION_MARKET_DEMO_END',
		'limit_subscription_market_tarifwithmarket' => 'SUBSCRIPTION_MARKET_TARIFF_MARKET',
		'limit_subscription_market_marketpaid_trialend' => 'SUBSCRIPTION_MARKET_TRIAL_END',
	];

	/**
	 * @return array|false
	 */
	public static function get()
	{
		$result = false;
		$option = Option::get(static::MODULE_ID, static::OPTION_ACCESS_NOTIFICATION, '');
		if ($option !== '' && !empty(static::$codeToNotification[$option]))
		{
			$url = Loc::getMessage('REST_MARKETPLACE_NOTIFICATION_'.static::$codeToNotification[$option].'_URL');
			if ($option === 'rest_buy' && Loader::includeModule('bitrix24'))
			{
				$prefix = \CBitrix24::getLicensePrefix();
				if ($prefix === 'by')
				{
					$url = 'https://goodbye-2020.bitrix24.site/';
				}
				elseif ($prefix === 'kz')
				{
					$url = 'https://goodbye2020.bitrix24.site/';
				}
				elseif ($prefix === 'ua')
				{
					$url = 'https://skilky-mozhna.bitrix24site.ua/';
				}
				elseif ($prefix === 'ru')
				{
					$url = 'https://goodbye2020.bitrix24.tech/';
				}
				elseif ($prefix === 'en')
				{
					$url = 'https://www.bitrix24.com/promo/sales/holiday-sale/';
				}
				elseif ($prefix === 'jp')
				{
					$url = 'https://www.bitrix24.jp/promo/sales/holiday-sale/';
				}
				elseif ($prefix === 'pl')
				{
					$url = 'https://www.bitrix24.pl/promo/sales/holiday-sale/';
				}
				elseif ($prefix === 'it')
				{
					$url = 'https://www.bitrix24.it/promo/sales/holiday-sale/';
				}
				elseif ($prefix === 'br')
				{
					$url = 'https://www.bitrix24.com.br/promo/sales/holiday-sale/';
				}
				elseif ($prefix === 'fr')
				{
					$url = 'https://www.bitrix24.fr/promo/sales/holiday-sale/';
				}
				elseif ($prefix === 'de')
				{
					$url = 'https://www.bitrix24.de/promo/sales/holiday-sale/';
				}
				elseif ($prefix === 'in')
				{
					$url = 'https://www.bitrix24.in/promo/sales/holiday-sale/';
				}
				elseif ($prefix === 'eu')
				{
					$url = 'https://www.bitrix24.eu/promo/sales/holiday-sale/';
				}
				elseif ($prefix === 'es' || $prefix === 'la')
				{
					$url = 'https://www.bitrix24.es/promo/sales/holiday-sale/';
				}
			}
			$result = [
				'PANEL_MESSAGE' => Loc::getMessage(
					'REST_MARKETPLACE_NOTIFICATION_'.static::$codeToNotification[$option].'_MESS',
					[
						'#BTN#' => '<a target="_blank" href="'
								. $url
								. '">'
								. Loc::getMessage('REST_MARKETPLACE_NOTIFICATION_'.static::$codeToNotification[$option].'_BTN')
								. '</a>'
					]
				)
			];
		}

		return $result;
	}

	public static function setLastCheckTimestamp($timestamp)
	{
		Option::set(static::MODULE_ID, static::OPTION_LAST_CHECK_NOTIFICATION, $timestamp);
		return true;
	}

	public static function getLastCheckTimestamp()
	{
		$result = false;
		$option = (int) Option::get(static::MODULE_ID, static::OPTION_LAST_CHECK_NOTIFICATION, 0);
		if ($option > 0)
		{
			$result = $option + static::$timestampNotifyDays;
		}

		return $result;
	}

	/**
	 * @return string
	 */
	public static function checkAgent()
	{
		if (Loader::includeModule('bitrix24'))
		{
			$code = '';
			if (Access::isActiveRules() && Client::isSubscriptionAccess())
			{
				$dateFinish = Client::getSubscriptionFinalDate();
				$entity = Access::getActiveEntity();
				$maxCount = Access::getAvailableCount();
				$isSubscriptionFinish = $dateFinish
										&& $dateFinish < (new Date())
										&& (time() - static::$timestampNotifyDays) < $dateFinish->getTimestamp();
				if (!Access::isAvailable())
				{
					if (
						$maxCount >= 0
						&& (
							$entity[Access::ENTITY_COUNT] > $maxCount
							|| $entity[Access::ENTITY_TYPE_APP_STATUS][AppTable::STATUS_PAID] > 0
							|| $entity[Access::ENTITY_TYPE_APP_STATUS][AppTable::STATUS_SUBSCRIPTION] > 0
						)
						&& static::getLastCheckTimestamp() > time()
					)
					{
						$code = 'limit_subscription_market_tarifwithmarket';
					}
					elseif ($isSubscriptionFinish)
					{
						$code = 'limit_subscription_market_demomarket_end';
					}
				}
				elseif (Access::isFeatureEnabled() && $isSubscriptionFinish)
				{
					$code = 'limit_subscription_market_marketpaid_trialend';
				}
			}

			if ($code !== '')
			{
				Option::set(static::MODULE_ID, static::OPTION_ACCESS_NOTIFICATION, $code);
				Option::set(static::MODULE_ID, static::OPTION_LAST_CHECK_ACCESS_NOTIFICATION, time());
			}
			elseif(Option::get(static::MODULE_ID, static::OPTION_ACCESS_NOTIFICATION, false) !== false)
			{
				Option::delete(static::MODULE_ID, ['name' => static::OPTION_ACCESS_NOTIFICATION]);
				Option::delete(static::MODULE_ID, ['name' => static::OPTION_LAST_CHECK_ACCESS_NOTIFICATION]);
			}
		}

		return '\Bitrix\Rest\Marketplace\Notification::checkAgent();';
	}
}