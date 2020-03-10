<?php


class RepositorySpeleoSeGrottan {

	private $numbers = [];

    public function __construct() {
		require 'testdata/data.php';
    }
	public function getAllNumbers() {
		return $this->numbers;
	}
	/**
	 * @return string[]
	 */
	public function getAllAuthors() {
		$allAuthors = [];
		foreach ($this->numbers as $year => $numbersYear) {
			foreach ($numbersYear as $numbersYearNumber) {
				foreach ($numbersYearNumber['content'] as $article) {
					foreach(explode(';', $article['author']) as $author) {
						if (isset($allAuthors[trim($author)])) {
							$allAuthors[trim($author)]['earliest']['year'] = min($year, $allAuthors[trim($author)]['earliest']['year']);
							$allAuthors[trim($author)]['latest']['year'] = max($year, $allAuthors[trim($author)]['latest']['year']);
						} else {
							$allAuthors[trim($author)] = [
									'name' => trim($author),
									'earliest' => [
											'year' => $year,
											],
									'latest' => [
											'year' => $year,
											],
									];
						}
					}
				}
			}
		}
		return $allAuthors;
	}
}