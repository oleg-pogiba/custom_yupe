<?php
/**
 * Menu основная модель для menu
 *
 * @package yupe.modules.menu.models
 * @since 0.1
 *
 */

/**
 * This is the model class for table "menu".
 *
 * The followings are the available columns in table 'menu':
 * @property string $id
 * @property string $name
 * @property string $code
 * @property string $lang
 * @property string $description
 * @property integer $status
 *
 * The followings are the available model relations:
 * @property MenuItem[] $menuItems
 */
class Menu extends yupe\models\YModel
{
	const STATUS_DISABLED = 0;
	const STATUS_ACTIVE = 1;

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Menu the static model class
	 */
	public static function model($className = __CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{menu}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array('name, code, lang, description', 'required', 'except' => 'search'),
			array('status', 'numerical', 'integerOnly' => true),
			array('name, code, lang, description', 'filter', 'filter' => array($obj = new CHtmlPurifier(), 'purify')),
			array('name, description', 'length', 'max' => 255),
			array('code', 'length', 'max' => 100),
			array('code', 'yupe\components\validators\YSLugValidator'),
			//array('code', 'unique'),
			array('status', 'in', 'range' => array_keys($this->statusList)),
			array('id, name, code, description, status', 'safe', 'on' => 'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'menuItems' => array(self::HAS_MANY, 'MenuItem', 'menu_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => Yii::t('MenuModule.menu', 'Id'),
			'name' => Yii::t('MenuModule.menu', 'Name'),
			'code' => Yii::t('MenuModule.menu', 'Unified code'),
			'lang' => Yii::t('MenuModule.menu', 'Language'),
			'description' => Yii::t('MenuModule.menu', 'Description'),
			'status' => Yii::t('MenuModule.menu', 'Status'),
		);
	}

	/**
	 * @return array customized attribute descriptions (name=>description)
	 */
	public function attributeDescriptions()
	{
		return array(
			'id' => Yii::t('MenuModule.menu', 'Menu Id'),
			'name' => Yii::t('MenuModule.menu', 'Menu name'),
			'code' => Yii::t('MenuModule.menu', 'Unified code is using in widget, as identifier for menu printing.'),
			'description' => Yii::t('MenuModule.menu', 'Short description'),
			'status' => Yii::t('MenuModule.menu', 'Choose menu status: <br /><br /><span class="label label-success">active</span> &ndash; Will be visible on site.<br /><br /><span class="label label-warning">not active</span> &ndash; Will be hidden.'),
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria = new CDbCriteria;

		$criteria->compare('id', $this->id, true);
		$criteria->compare('name', $this->name, true);
		$criteria->compare('code', $this->code, true);
		$criteria->compare('description', $this->description, true);
		$criteria->compare('status', $this->status);
		$criteria->compare('lang', $this->lang);

		return new CActiveDataProvider(get_class($this), array(
			'criteria' => $criteria,
			'sort' => array('defaultOrder' => 'status DESC, id'),
		));
	}


	public function scopes()
	{
		return array(
			'active' => array(
				'condition' => 'status = :status',
				'params' => array(
					':status' => self::STATUS_ACTIVE
				)
			)
		);
	}


	protected function afterSave()
	{
		Yii::app()->cache->clear($this->code);

		return parent::afterSave();
	}

	protected function afterDelete()
	{
		Yii::app()->cache->clear($this->code);

		return parent::afterDelete();
	}

	public function getStatusList()
	{
		return array(
			self::STATUS_DISABLED => Yii::t('MenuModule.menu', 'not active'),
			self::STATUS_ACTIVE => Yii::t('MenuModule.menu', 'active'),
		);
	}

	public function getStatus()
	{
		$data = $this->statusList;
		return isset($data[$this->status]) ? $data[$this->status] : Yii::t('MenuModule.menu', '*unknown*');
	}

	// @todo добавить кэширование
	public function getItems($code, $parent_id = 0)
	{
		$userId = Yii::app()->user->getId();
		$items = Yii::app()->cache->get("Menu::{$code}{$parent_id}::user_{$userId}" . Yii::app()->language);

		if ($items === false) {
			$alias = $this->getDbConnection()->getSchema()->quoteTableName('menuItems');
			$results = self::model()->with(
				array(
					'menuItems' => array(
						'on' => $alias . '.parent_id = :parent_id AND ' . $alias . '.status = 1',
						'params' => array('parent_id' => (int)$parent_id),
						'order' => $alias . '.sort ASC, ' . $alias . '.id ASC',
					)
				)
			)->findByAttributes(
					array(
						'code' => $code,
						'lang' => Yii::app()->language,
					)
				);

			$items = array();

			if (empty($results))
				return $items;

			$resultItems = $results->menuItems;

			foreach ($resultItems as $result) {
				$childItems = $this->getItems($code, $result->id);

				// @TODO Если не ставить url и присутствует items, пункт не выводится, возможно баг yii
				if ($result->href) {
					// если адрес надо параметризовать через роутер
					if (!$result->regular_link) {

						$url = @unserialize($result->href) ? : $result->href;

						$param = array();

						if (!is_array($url)) {

							strstr($url, '?') ? list($url, $param) = explode("?", $url) : $param = array();

							if ($param) {
								parse_str($param, $param);
							}

						}

						$url = array('url' => (array)$url + $param, 'items' => $childItems);
					} else {
						// если обычная ссылка
						$url = array('url' => $result->href, 'items' => $childItems);
					}

				} else if ($childItems)
					$url = array('url' => array('#'), 'items' => $childItems);
				else
					$url = array();

				$class = (($childItems) ? ' submenuItem' : '') .
					(($result->class) ? ' ' . $result->class : '');
				$title_attr = ($result->title_attr) ? array('title' => $result->title_attr) : array();
				$target = ($result->target && $url) ? array('target' => $result->target) : array();
				$rel = ($result->rel && $url) ? array('rel' => $result->rel) : array();

				$items[] = array(
						'label' => $result->title,
						'template' => $result->before_link . '{menu}' . $result->after_link,
						'itemOptions' => array('class' => 'listItem' . $class),
						'linkOptions' => array(
								'class' => 'listItemLink',
							) + $title_attr + $target + $rel,
						'visible' => MenuItem::model()->getConditionVisible($result->condition_name, $result->condition_denial),
					) + $url;
			}

			Yii::app()->cache->set("Menu::{$code}{$parent_id}::user_{$userId}" . Yii::app()->language, $items, 0, new TagsCache('menu', $code, 'loggedIn' . $userId));
		}
		return $items;
	}

	public function addItem($title, $href, $parentId)
	{
		$menuItem = new MenuItem;
		$menuItem->parent_id = (int)$parentId;
		$menuItem->menu_id = $this->id;
		$menuItem->title = $title;
		$menuItem->href = $href;
		$menuItem->condition_name = '';
		$menuItem->class = '';
		$menuItem->title_attr = '';
		$menuItem->before_link = '';
		$menuItem->after_link = '';
		$menuItem->target = '';
		$menuItem->rel = '';
		if ($menuItem->save()) {
			Yii::app()->cache->clear(array('menu', $this->code));
			return true;
		}

		return false;
	}

	/**
	 * Метод изменения пункта меню.
	 * @param $oldTitle Старое название элемента (по нему осуществяется поиск)
	 * @param $newTitle Новое название
	 * @param $href Новая ссылка
	 * @param $parentId id меню
	 * @return bool статус выполнения
	 */
	public function changeItem($oldTitle, $newTitle, $href, $parentId)
	{
		$menuItem = MenuItem::model()->findByAttributes(array("title" => $oldTitle));

		if ($menuItem === null) {
			return $this->addItem($newTitle, $href, $parentId);
		}

		$menuItem->parent_id = (int)$parentId;
		$menuItem->menu_id = $this->id;
		$menuItem->title = $newTitle;
		$menuItem->href = $href;

		if ($menuItem->save()) {
			Yii::app()->cache->clear(array('menu', $this->code));
			return true;
		}

		return false;
	}

	//{ author="Pogiba" date="2014-05-31" desc=""
	public function getLangs()
	{
		$langs = array();
		Yii::app()->urlManager->getLangs();
		foreach(Yii::app()->urlManager->languages as $language)
		{
			$langs[$language] = $language;
		}
		return $langs;
	}
	//}
}