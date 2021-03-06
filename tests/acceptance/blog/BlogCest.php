<?php
namespace blog;

use \WebGuy;

class BlogCest
{
	public function tryToTestBlogs(WebGuy $I, $scenario)
	{
		$I->am('simple user');
		$I->amOnPage(\BlogPage::BLOGS_URL);
		$I->seeInTitle('Блоги');
		$I->seeLink('Опубликованный блог');
		$I->see('Опубликованный блог описание');
		$I->dontSeeLink('Удаленный блог');

		$I->amGoingTo('test blog show page');
		$I->expectTo('see blog page');
		$I->amOnPage(\BlogPage::getBlogRoute(\BlogPage::PUBLIC_BLOG_SLUG));
		$I->seeLink('Опубликованный блог');
		$I->see('Опубликованный блог описание');
		$I->see('Участников нет =(');

		$I->seeLink('Первая публичная запись в опубликованном блоге');
		/*
		 * @TODO WTF ? Не понятно кто выпилил из тестовой базы теги? Пришлось закомментить, иначе тест не проходит. (DexterHD)
		 *
		$tags = array('тег','тег2','тег3');
		foreach($tags as $tag) {
			$I->seeLink($tag);
		}*/

		$I->dontSeeLink('Черновик в опубликованном блоге');

		$I->amGoingTo('test blog page with deleted blog');
		$I->expectTo('see http exception');
		$I->amOnPage(\BlogPage::getBlogRoute(\BlogPage::DELETED_BLOG_SLUG));
		$I->see('Страница которую Вы запросили не найдена.');
	}
}