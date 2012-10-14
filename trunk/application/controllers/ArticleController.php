<?php
use Zette\UI\BaseController;
/**
 * @author: Misak113
 * @date-created: 2.10.12
 */
class ArticleController extends BaseController
{
	/** @var ArticlesModel */
	protected $articlesModel;

	public function setContext(ArticlesModel $articlesModel) {
		$this->articlesModel = $articlesModel;
	}


	public function indexAction() {
		$this->view->articles = $this->articlesModel->getArticles();
	}

	public function detailAction() {
		$id = $this->getParam('id');

		$article = $this->articlesModel->getArticle($id);

		if ($article !== null) {
			$this->view->article = $article;
		} else {
			$this->_helper->redirector->gotoRoute(array(), 'articleList', true);
		}
	}

}
