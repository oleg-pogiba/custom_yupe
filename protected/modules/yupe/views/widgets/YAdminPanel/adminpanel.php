<?php
/**
 * Отображение для виджета YAdminPanel:
 *
 * @category YupeView
 * @package  yupe
 *
 **/

/**
 * Добавляем нужные CSS и JS:
 */

$this->widget(
	'bootstrap.widgets.TbNavbar', array(
		'htmlOptions' => array('class' => 'navbar-inverse'),
		'fluid' => true,
		//po_logo
		'brand' => CHtml::image(
				Yii::app()->baseUrl . "/web/images/logo.png", Yii::app()->name, array(
					'width' => '38',
					'height' => '38',
					'title' => Yii::app()->name,
				)
			),
		'brandUrl' => CHtml::normalizeUrl(array("/yupe/backend/index")),
		'items' => array(
			array(
				'class' => 'bootstrap.widgets.TbMenu',
				'items' => $this->controller->yupe->getModules(true),
			),
			array(
				'class' => 'bootstrap.widgets.TbMenu',
				'htmlOptions' => array('class' => 'pull-right'),
				'encodeLabel' => false,
				'items' => array_merge(
					array(
						//array(
						//	'icon'  => 'question-sign white',
						//	'label' => Yii::t('YupeModule.yupe', 'Help'),
						//	'url'   => CHtml::normalizeUrl(array('/yupe/backend/help')),
						//	'items' => array(
						//		array(
						//			'icon'  => 'icon-globe',
						//			'label' => Yii::t('YupeModule.yupe', 'Official site'),
						//			'url'   => 'http://yupe.ru?from=help',
						//			'linkOptions' => array('target' => '_blank'),
						//		),
						//		array(
						//			'icon'  => 'icon-book',
						//			'label' => Yii::t('YupeModule.yupe', 'Official docs'),
						//			'url'   => 'http://yupe.ru/docs/index.html?from=help',
						//			'linkOptions' => array('target' => '_blank'),
						//		),
						//		array(
						//			'icon'  => 'icon-th-large',
						//			'label' => Yii::t('YupeModule.yupe', 'Additional modules'),
						//			'url'   => 'https://github.com/yupe/yupe-ext',
						//			'linkOptions' => array('target' => '_blank'),
						//		),
						//		array(
						//			'icon'  => 'icon-comment',
						//			'label' => Yii::t('YupeModule.yupe', 'Forum'),
						//			'url'   => 'http://yupe.ru/talk/?from=help',
						//			'linkOptions' => array('target' => '_blank'),
						//		),
						//		array(
						//			'icon'  => 'icon-globe',
						//			'label' => Yii::t('YupeModule.yupe', 'Community on github'),
						//			'url'   => 'https://github.com/yupe/yupe',
						//			'linkOptions' => array('target' => '_blank'),
						//		),
						//		array(
						//			'icon'  => 'icon-thumbs-up',
						//			'label' => Yii::t('YupeModule.yupe', 'Order development and support'),
						//			'url'   => 'http://yupe.ru/contacts?from=help-support',
						//			'linkOptions' => array('target' => '_blank'),
						//		),
						//		array(
						//			'icon'  => 'icon-warning-sign',
						//			'label' => Yii::t('YupeModule.yupe', 'Report a bug'),
						//			'url'   => CHtml::normalizeUrl(array('/yupe/backend/reportBug/')),
						//			'linkOptions' => array('target' => '_blank'),
						//		),
						//		array(
						//			'icon'  => 'exclamation-sign',
						//			'label' => Yii::t('YupeModule.yupe', 'About Yupe!'),
						//			'url'   => array('/yupe/backend/help'),
						//		),
						//	)
						//),
						array(
							'icon' => 'home white',
							'label' => Yii::t('YupeModule.yupe', 'Go home'),
							'linkOptions' => array('target' => '_blank'),
							'visible' => Yii::app()->controller instanceof yupe\components\controllers\BackController === true,
							'url' => array('/' . Yii::app()->defaultController . '/index/'),
						),
						array(
							'label' => '
                                <div style="float: left; line-height: 16px; text-align: center; margin-top: -10px;">
                                    <small style="font-size: 80%;">' . Yii::t('YupeModule.yupe', 'Administrator') . '</small><br />
                                    <span class="label">' . Yii::app()->user->nick_name . '</span>
                                </div>',
							'encodeLabel' => false,
							'items' => array(
								array(
									'icon' => 'user',
									'label' => Yii::t('YupeModule.yupe', 'Profile'),
									'url' => CHtml::normalizeUrl((array('/user/userBackend/update', 'id' => Yii::app()->user->getId()))),
								),
								array(
									'icon' => 'off',
									'label' => Yii::t('YupeModule.yupe', 'Exit'),
									'url' => CHtml::normalizeUrl(array('/user/account/logout')),
								),
							),
						),
					), $this->controller->yupe->getLanguageSelectorArray()
				),
			),
		),
	)
);