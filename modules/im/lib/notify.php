<?php
namespace Bitrix\Im;

use Bitrix\Main\Type\DateTime;

class Notify
{
	private const CONFIRM_TYPE = 1;
	private const SIMPLE_TYPE = 3;
	private const ALL_TYPES = 4;

	private $convertText;
	private $pageLimit;
	private $lastType;
	private $lastId;
	private $chatId;
	private $users = [];
	private $firstPage;
	private $searchText;
	private $searchType;
	private $searchDate;
	private $totalCount;

	public function __construct($options = [])
	{
		$this->convertText = $options['CONVERT_TEXT'];
		$this->searchText = $options['SEARCH_TEXT'];
		$this->searchType = $options['SEARCH_TYPE'];
		$this->searchDate = $options['SEARCH_DATE'];
		$this->pageLimit = $options['LIMIT'];
		$this->lastType = $options['LAST_TYPE'];
		$this->lastId = $options['LAST_ID'];
		$this->firstPage = !$options['LAST_ID'] && !$options['LAST_TYPE'];

		$chatData = $this->getChatData();
		if ($chatData !== null)
		{
			$this->chatId = (int)$chatData['CHAT_ID'];
			$this->totalCount = (int)$chatData['IM_MODEL_RELATION_CHAT_MESSAGE_COUNT'];
		}
	}

	private function getChatData(): ?array
	{
		$userId = \Bitrix\Im\Common::getUserId();
		if (!$userId)
		{
			return null;
		}

		$chatData = \Bitrix\Im\Model\RelationTable::getList([
			'select' => ['CHAT_ID', 'CHAT.MESSAGE_COUNT'],
			'filter' => [
				'=USER_ID' => $userId,
				'=MESSAGE_TYPE' => 'S'
			]
		])->fetch();
		if (!$chatData)
		{
			return null;
		}

		return $chatData;
	}

	public static function getRealCounter($chatId): int
	{
		return self::getCounters($chatId, true)[$chatId];
	}

	public static function getRealCounters($chatId)
	{
		return self::getCounters($chatId, true);
	}

	public static function getCounter($chatId): int
	{
		return self::getCounters($chatId)[$chatId];
	}

	public static function getCounters($chatId, $isReal = false)
	{
		$result = Array();
		$chatList = Array();
		if (is_array($chatId))
		{
			foreach($chatId as $id)
			{
				$id = intval($id);
				if ($id)
				{
					$result[$id] = 0;
					$chatList[$id] = $id;
				}
			}
			$chatList = array_values($chatList);
			$isMulti = count($chatList) > 1;
		}
		else
		{
			$id = intval($chatId);
			if ($id)
			{
				$result[$id] = 0;
				$chatList[] = $id;
			}
			$isMulti = false;
		}

		if (!$chatList)
		{
			return false;
		}

		if ($isReal)
		{
			$query = "
				SELECT CHAT_ID, COUNT(1) COUNTER
				FROM b_im_message
				WHERE CHAT_ID ".($isMulti? ' IN ('.implode(',', $chatList).')': ' = '.$chatList[0])."
					  AND NOTIFY_READ = 'N'
				GROUP BY CHAT_ID
			";
		}
		else
		{
			$query = "
				SELECT CHAT_ID, COUNTER 
				FROM b_im_relation
				WHERE CHAT_ID ".($isMulti? ' IN ('.implode(',', $chatList).')': ' = '.$chatList[0])."
			";
		}

		$orm = \Bitrix\Main\Application::getInstance()->getConnection()->query($query);
		while($row = $orm->fetch())
		{
			$result[$row['CHAT_ID']] = (int)$row['COUNTER'];
		}

		return $result;
	}

	public function get()
	{
		if (!$this->chatId || !$this->totalCount)
		{
			return [];
		}
		// fetching confirm notifications
		$confirmCollection = $this->fetchConfirms();

		// fetching simple notifications
		$offset = count($confirmCollection);
		$simpleCollection = $this->fetchSimple($offset);
		$notifications = array_merge($confirmCollection, $simpleCollection);

		$unreadCount = \Bitrix\Im\Model\MessageTable::getList(
			[
				'select' => ['CNT'],
				'filter' => [
					'=CHAT_ID' => $this->chatId,
					'=NOTIFY_READ' => 'N'
				],
				'runtime' => [
					new \Bitrix\Main\ORM\Fields\ExpressionField('CNT', 'COUNT(*)')
				]
			]
		)->fetch();

		$result = [
			'TOTAL_COUNT' => $this->totalCount,
			'TOTAL_UNREAD_COUNT' => (int)$unreadCount['CNT'],
			'CHAT_ID' => $this->chatId,
			'NOTIFICATIONS' => $notifications,
			'USERS' => $this->users,
		];

		foreach ($result['NOTIFICATIONS'] as $key => $value)
		{
			if ($value['DATE'] instanceof DateTime)
			{
				$result['NOTIFICATIONS'][$key]['DATE'] = date('c', $value['DATE']->getTimestamp());
			}

			$result['NOTIFICATIONS'][$key] = array_change_key_case($result['NOTIFICATIONS'][$key], CASE_LOWER);
		}
		$result['NOTIFICATIONS'] = array_values($result['NOTIFICATIONS']);
		$result['USERS'] = array_values($result['USERS']);
		$result = array_change_key_case($result, CASE_LOWER);

		return $result;
	}

	private function requestData(int $requestType, int $limit): array
	{
		$ormParams = $this->prepareGetListParams($requestType, $limit);
		$ormResult = \Bitrix\Im\Model\MessageTable::getList($ormParams);

		$collection = [];
		foreach ($ormResult as $notifyItem)
		{
			if ($notifyItem['NOTIFY_EVENT'] === 'private_system')
			{
				$notifyItem['AUTHOR_ID'] = 0;
			}

			$collection[$notifyItem['ID']] = [
				'ID' => (int)$notifyItem['ID'],
				'CHAT_ID' => $this->chatId,
				'AUTHOR_ID' => (int)$notifyItem['AUTHOR_ID'],
				'TEXT' => $this->convertHtmlToBbCode((string)$notifyItem['MESSAGE']),
				'DATE' => $notifyItem['DATE_CREATE'],
				'NOTIFY_TYPE' => (int)$notifyItem['NOTIFY_TYPE'],
				'NOTIFY_MODULE' => $notifyItem['NOTIFY_MODULE'],
				'NOTIFY_EVENT' => $notifyItem['NOTIFY_EVENT'],
				'NOTIFY_TAG' => $notifyItem['NOTIFY_TAG'],
				'NOTIFY_SUB_TAG' => $notifyItem['NOTIFY_SUB_TAG'],
				'NOTIFY_TITLE' => $notifyItem['NOTIFY_TITLE'],
				'NOTIFY_READ' => $notifyItem['NOTIFY_READ'],
				'SETTING_NAME' => $notifyItem['NOTIFY_MODULE'].'|'.$notifyItem['NOTIFY_EVENT'],
			];
			if ($notifyItem['AUTHOR_ID'] && !isset($this->users[$notifyItem['AUTHOR_ID']]))
			{
				$user = User::getInstance($notifyItem['AUTHOR_ID'])->getArray([
					'JSON' => 'Y',
					'SKIP_ONLINE' => 'Y'
				]);
				$user['last_activity_date'] =
					$notifyItem['USER_LAST_ACTIVITY_DATE']
						? date('c', $notifyItem['USER_LAST_ACTIVITY_DATE']->getTimestamp())
						: false
				;
				$user['desktop_last_date'] =
					$notifyItem['USER_DESKTOP_LAST_DATE']
						? date('c', $notifyItem['USER_DESKTOP_LAST_DATE']->getTimestamp())
						: false
				;
				$user['mobile_last_date'] =
					$notifyItem['USER_MOBILE_LAST_DATE']
						? date('c', $notifyItem['USER_MOBILE_LAST_DATE']->getTimestamp())
						: false
				;
				$user['idle'] = $notifyItem['USER_IDLE'] ?: false;

				$this->users[$notifyItem['AUTHOR_ID']] = $user;
			}
			if ($this->convertText)
			{
				$collection[$notifyItem['ID']]['TEXT_CONVERTED'] = \Bitrix\Im\Text::parse(
					$notifyItem['MESSAGE'],
					[
						'LINK' => 'Y',
						'LINK_TARGET_SELF' => 'Y',
						'SAFE' => 'N',
						'FONT' => 'Y',
					]
				);
			}

			//keyboard creation
			if ($notifyItem['NOTIFY_BUTTONS'])
			{
				$buttons = unserialize($notifyItem['NOTIFY_BUTTONS'], ['allowed_classes' => false]);

				$keyboard = new \Bitrix\Im\Bot\Keyboard(111);
				$command = 'notifyConfirm';
				foreach ($buttons as $button)
				{
					$keyboard->addButton(
						[
							'TEXT' => $button['TITLE'],
							'COMMAND' => $command,
							'COMMAND_PARAMS' => $notifyItem['ID'].'|'.$button['VALUE'],
							'TEXT_COLOR' => '#fff',
							'BG_COLOR' => $button['TYPE'] === 'accept' ? '#8BC84B' : '#ef4b57',
							'DISPLAY' => 'LINE'
						]
					);
				}
				$collection[$notifyItem['ID']]['NOTIFY_BUTTONS'] = $keyboard->getJson();
			}
		}

		$params = \CIMMessageParam::Get(array_keys($collection));

		foreach ($params as $notificationId => $param)
		{
			$collection[$notificationId]['PARAMS'] = empty($param) ? null : $param;
		}

		return $collection;
	}

	private function fetchConfirms(): array
	{
		$confirmCollection = [];

		$nextPageIsConfirm = $this->lastType === self::CONFIRM_TYPE;
		if ($this->firstPage || $nextPageIsConfirm)
		{
			$confirmCollection = $this->requestData(self::CONFIRM_TYPE, $this->pageLimit);
		}

		return $confirmCollection;
	}

	private function fetchSimple(int $offset): array
	{
		$simpleCollection = [];
		$nextPageIsSimple = $this->lastType === self::SIMPLE_TYPE;
		$needMoreOnFirstPage = $this->firstPage && $offset < $this->pageLimit;
		$notEnoughFromPreviousStep = $this->lastType === self::CONFIRM_TYPE && $offset < $this->pageLimit;

		if ($needMoreOnFirstPage || $notEnoughFromPreviousStep || $nextPageIsSimple)
		{
			$simpleCollection = $this->requestData(self::SIMPLE_TYPE, $this->pageLimit - $offset);
		}

		return $simpleCollection;
	}

	public function search(): array
	{
		if (!$this->chatId)
		{
			return [];
		}

		if (!$this->searchText && !$this->searchType && !$this->searchDate)
		{
			return [];
		}

		if ($this->lastId > 0)
		{
			$this->lastType = self::ALL_TYPES;
			$this->firstPage = false;
		}

		// fetching searched notifications
		$collection = $this->requestData(self::ALL_TYPES, $this->pageLimit);

		$result = [
			'CHAT_ID' => $this->chatId,
			'NOTIFICATIONS' => $collection,
			'USERS' => $this->users,
		];

		if (!$this->lastId)
		{
			$result['TOTAL_RESULTS'] = $this->requestSearchTotalCount();
		}

		foreach ($result['NOTIFICATIONS'] as $key => $value)
		{
			if ($value['DATE'] instanceof DateTime)
			{
				$result['NOTIFICATIONS'][$key]['DATE'] = date('c', $value['DATE']->getTimestamp());
			}

			$result['NOTIFICATIONS'][$key] = array_change_key_case($result['NOTIFICATIONS'][$key], CASE_LOWER);
		}
		$result['NOTIFICATIONS'] = array_values($result['NOTIFICATIONS']);
		$result['USERS'] = array_values($result['USERS']);
		$result = array_change_key_case($result, CASE_LOWER);

		return $result;
	}

	/**
	 * Agent for deleting old notifications.
	 *
	 * @return string
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function cleanNotifyAgent(): string
	{
		$dayCount = 60;
		$limit = 2000;
		$step = 1000;

		$batches = [];
		$result = \Bitrix\Im\Model\MessageTable::getList([
			'select' => ['ID', 'CHAT_ID', 'NOTIFY_READ', 'RELATION_ID' => 'RELATION.ID'],
			'filter' => [
				'=NOTIFY_TYPE' => [IM_NOTIFY_CONFIRM, IM_NOTIFY_FROM, IM_NOTIFY_SYSTEM],
				'<DATE_CREATE' => ConvertTimeStamp((time() - 86400 * $dayCount), 'FULL')
			],
			'limit' => $limit
		]);

		$relationIdToCounters = [];
		$batch = [];
		$i = 0;

		while ($row = $result->fetch())
		{
			if ($row['NOTIFY_READ'] === 'N')
			{
				$relationId = (int)$row['RELATION_ID'];
				if (isset($relationIdToCounters[$relationId]))
				{
					$relationIdToCounters[$relationId] = 0;
				}
				$relationIdToCounters[$relationId]++;
			}

			if ($i++ === $step)
			{
				$i = 0;
				$batches[] = $batch;
				$batch = [];
			}
			$batch[] = (int)$row['ID'];
		}
		if (!empty($batch))
		{
			$batches[] = $batch;
		}

		foreach ($batches as $batch)
		{
			\Bitrix\Im\Model\MessageTable::deleteBatch([
				'=ID' => $batch
			]);
			\Bitrix\Im\Model\MessageParamTable::deleteBatch([
				'=MESSAGE_ID' => $batch
			]);
		}

		$countersToRelationIds = [];
		foreach ($relationIdToCounters as $relationId => $unreadCounter)
		{
			$countersToRelationIds[$unreadCounter][] = $relationId;
		}

		$needToCleanCountersCache = false;
		$connection = \Bitrix\Main\Application::getConnection();
		foreach ($countersToRelationIds as $counter => $relationIds)
		{
			$needToCleanCountersCache = true;
			$relationIds = '(' . implode(',', $relationIds) . ')';
			$sql = "UPDATE b_im_relation SET COUNTER = IF(COUNTER > {$counter}, COUNTER - {$counter}, 0) WHERE ID IN {$relationIds};";
			$connection->queryExecute($sql);
		}
		if ($needToCleanCountersCache)
		{
			\Bitrix\Im\Counter::clearCache();
		}

		return '\Bitrix\Im\Notify::cleanNotifyAgent();';
	}

	private function requestSearchTotalCount(): int
	{
		$ormParams = [
			'select' => ['CNT'],
			'filter' => [
				'=CHAT_ID' => $this->chatId,
			],
			'runtime' => [
				new \Bitrix\Main\ORM\Fields\ExpressionField('CNT', 'COUNT(*)')
			]
		];

		if ($this->searchText)
		{
			$ormParams['filter']['*%MESSAGE'] = $this->searchText;
		}
		if ($this->searchType)
		{
			$options = explode('|', $this->searchType);
			$ormParams['filter']['=NOTIFY_MODULE'] = $options[0];
			if (isset($options[1]))
			{
				$ormParams['filter']['=NOTIFY_EVENT'] = $options[1];
			}
		}
		if ($this->searchDate)
		{
			$dateStart = new DateTime(
				$this->searchDate,
				\DateTimeInterface::RFC3339,
				new \DateTimeZone('UTC')
			);
			$dateEnd = (
				new DateTime(
					$this->searchDate,
					\DateTimeInterface::RFC3339,
					new \DateTimeZone('UTC')
				)
			)->add('1 DAY');

			$ormParams['filter']['><DATE_CREATE'] = [$dateStart, $dateEnd];
		}

		$totalSearchCount = \Bitrix\Im\Model\MessageTable::getList($ormParams)->fetch();
		if (!$totalSearchCount)
		{
			return 0;
		}

		return (int)$totalSearchCount['CNT'];
	}

	private function prepareGetListParams(int $requestType, int $limit = 0): array
	{
		$ormParams = [
			'filter' => ['=CHAT_ID' => $this->chatId],
			'order' => ['DATE_CREATE' => 'DESC']
		];

		if ($limit <= 0)
		{
			$ormParams['select'] = ['ID'];
		}
		else
		{
			$ormParams['select'] = [
				'ID',
				'AUTHOR_ID',
				'MESSAGE',
				'DATE_CREATE',
				'NOTIFY_TYPE',
				'NOTIFY_EVENT',
				'NOTIFY_MODULE',
				'NOTIFY_TAG',
				'NOTIFY_SUB_TAG',
				'NOTIFY_TITLE',
				'NOTIFY_READ',
				'NOTIFY_BUTTONS',
				'USER_LAST_ACTIVITY_DATE' => 'AUTHOR.LAST_ACTIVITY_DATE',
				'USER_IDLE' => 'STATUS.IDLE',
				'USER_MOBILE_LAST_DATE' => 'STATUS.MOBILE_LAST_DATE',
				'USER_DESKTOP_LAST_DATE' => 'STATUS.DESKTOP_LAST_DATE',
			];
			$ormParams['limit'] = $limit;
		}

		if ($requestType === self::CONFIRM_TYPE)
		{
			$ormParams['filter']['=NOTIFY_TYPE'] = IM_NOTIFY_CONFIRM;
		}
		elseif ($requestType === self::SIMPLE_TYPE)
		{
			$ormParams['filter']['!=NOTIFY_TYPE'] = IM_NOTIFY_CONFIRM;
		}
		elseif ($requestType === self::ALL_TYPES)
		{
			if ($this->searchText)
			{
				$ormParams['filter']['*%MESSAGE'] = $this->searchText;
			}
			if ($this->searchType)
			{
				$options = explode('|', $this->searchType);
				$ormParams['filter']['=NOTIFY_MODULE'] = $options[0];
				if (isset($options[1]))
				{
					$ormParams['filter']['=NOTIFY_EVENT'] = $options[1];
				}
			}
			if ($this->searchDate)
			{
				$dateStart = new DateTime(
					$this->searchDate,
					\DateTimeInterface::RFC3339,
					new \DateTimeZone('UTC')
				);
				$dateEnd = (
					new DateTime(
						$this->searchDate,
						\DateTimeInterface::RFC3339,
						new \DateTimeZone('UTC')
					)
				)->add('1 DAY');
				$ormParams['filter']['><DATE_CREATE'] = [$dateStart, $dateEnd];
			}
		}

		if (!$this->firstPage)
		{
			if (
				$requestType === self::CONFIRM_TYPE
				|| ($requestType === self::SIMPLE_TYPE && $this->lastType === self::SIMPLE_TYPE)
				|| ($requestType === self::ALL_TYPES && $this->lastType === self::ALL_TYPES)
			)
			{
				$ormParams['filter']['<ID'] = $this->lastId;
			}
		}

		return $ormParams;
	}

	public function getLastId(): ?int
	{
		if (!$this->chatId)
		{
			return null;
		}

		$ormParams = [
			'select' => ['ID'],
			'filter' => ['=CHAT_ID' => $this->chatId],
			'order' => ['ID' => 'DESC'],
			'limit' => 1,
		];

		$getListResult = \Bitrix\Im\Model\MessageTable::getList($ormParams)->fetch();
		if (!$getListResult)
		{
			return null;
		}

		if (count($getListResult) === 1)
		{
			return (int)$getListResult['ID'];
		}

		return null;
	}

	/**
	 * Cleans html from all the tags, except some which we accept in notifications.
	 *
	 * @param string $html String with HTML code.
	 *
	 * @return string
	 */
	private function cleanHtml(string $html): string
	{
		$sanitizer = new \CBXSanitizer();
		$sanitizer->AddTags([
			'a' => array('href'),
			'b' => array(),
			'u' => array(),
			'i' => array(),
			's' => array(),
			'br' => array(),
			'font' => array('color')
		]);
		$sanitizer->ApplyDoubleEncode(false);
		$html = $sanitizer->SanitizeHtml($html);

		return htmlspecialcharsback($html);
	}

	/**
	 * Converts some html tags to BB codes.
	 *
	 * @param string $html String with HTML code.
	 *
	 * @return string
	 */
	public function convertHtmlToBbCode(string $html): string
	{
		$html = $this->cleanHtml($html);

		$replaced = 0;
		do
		{
			$html = preg_replace(
				"/<([busi])[^>a-z]*>(.+?)<\\/(\\1)[^>a-z]*>/is".BX_UTF_PCRE_MODIFIER,
				"[\\1]\\2[/\\1]",
				$html, -1, $replaced);
		}
		while($replaced > 0);

		$html = preg_replace("/\\<br\s*\\/*\\>/is".BX_UTF_PCRE_MODIFIER,"[br]", $html);
		$html = preg_replace(
			[
				"#<a[^>]+href\\s*=\\s*('|\")(.+?)(?:\\1)[^>]*>(.*?)</a[^>]*>#is".BX_UTF_PCRE_MODIFIER,
				"#<a[^>]+href(\\s*=\\s*)([^'\">]+)>(.*?)</a[^>]*>#is".BX_UTF_PCRE_MODIFIER
			],
			"[url=\\2]\\3[/url]", $html
		);
		$html = preg_replace(
			[
				"/\\<font[^>]+color\\s*=[\\s'\"]*(\\#[a-f0-9]{6})[^>]*\\>(.+?)\\<\\/font[^>]*>/is".BX_UTF_PCRE_MODIFIER
			],
			[
				"[color=\\1]\\2[/color]",
			],
			$html
		);

		return $html;
	}
}