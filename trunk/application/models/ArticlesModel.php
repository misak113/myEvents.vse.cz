<?php
/**
 * @author: Misak113
 * @date-created: 2.10.12
 */
class ArticlesModel
{

	/** @var array */
	protected $articles;

	public function __construct() {
		$this->articles = array(
			new Article('Nové iPody', 'Něco o nových iPodech'),
			new Article('Nové iMac', 'Informace o iMac počítačích'),
			new Article('Staré ACER', 'Jak jsou na tom staré PC ACER'),
			new Article('Levné Lenovo', 'Nejlevnější jsou Lenovo'),
		);
	}

	public function getArticles() {
		return $this->articles;
	}

	public function getArticle($id) {
		foreach ($this->articles as $article) {
			if ($article->getId() == $id) {
				return $article;
			}
		}
		return null;
	}

}
