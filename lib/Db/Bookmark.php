<?php
/*
 * Copyright (c) 2020-2024. The Nextcloud Bookmarks contributors.
 *
 * This file is licensed under the Affero General Public License version 3 or later. See the COPYING file.
 */

namespace OCA\Bookmarks\Db;

use OCP\AppFramework\Db\Entity;

/**
 * Class Bookmark
 *
 * @package OCA\Bookmarks\Db
 * @method string getUrl()
 * @method setUrl(string $url)
 * @method string getTitle()
 * @method string getDescription()
 * @method int getLastmodified()
 * @method setLastmodified(int $lastmodified)
 * @method int getAdded()
 * @method setAdded(int $added)
 * @method int getClickcount
 * @method setClickcount(int $count)
 * @method int getLastPreview()
 * @method setLastPreview(int $lastpreview)
 * @method bool getAvailable()
 * @method setAvailable(boolean $available)
 * @method int getArchivedFile()
 * @method setArchivedFile(int $fileId)
 * @method string getTextContent()
 * @method setTextContent(string $content)
 * @method string getHtmlContent()
 * @method setHtmlContent(string $content)
 * @method string getUserId()
 * @method setUserId(string $userId)
 */
class Bookmark extends Entity {
	protected $url;
	protected $title;
	protected $userId;
	protected $description;
	protected $public;
	protected $added;
	protected $lastmodified;
	protected $clickcount;
	protected $lastPreview;
	protected $available;
	protected $archivedFile;
	protected $textContent;
	protected $htmlContent;

	public static $columns = ['id', 'url', 'title', 'description', 'lastmodified', 'added', 'clickcount', 'last_preview', 'available', 'archived_file', 'user_id', 'text_content', 'html_content'];
	public static $fields = ['id', 'url', 'title', 'description', 'lastmodified', 'added', 'clickcount', 'lastPreview', 'available', 'archivedFile', 'userId', 'textContent','htmlContent'];

	public static function fromArray($props): self {
		$bookmark = new Bookmark();
		foreach ($props as $prop => $val) {
			if ($prop === 'target') {
				$prop = 'url';
			}
			$bookmark->{'set' . $prop}($val);
		}
		return $bookmark;
	}

	public function __construct() {
		// add types in constructor
		$this->addType('id', 'integer');
		$this->addType('url', 'string');
		$this->addType('title', 'string');
		$this->addType('userId', 'string');
		$this->addType('description', 'string');
		$this->addType('added', 'integer');
		$this->addType('lastmodified', 'integer');
		$this->addType('clickcount', 'integer');
		$this->addType('lastPreview', 'integer');
		$this->addType('available', 'boolean');
		$this->addType('archivedFile', 'integer');
	}

	public function toArray(): array {
		$array = [];
		foreach (self::$fields as $field) {
			if ($field === 'url') {
				if (!preg_match('/^javascript:/i', $this->url)) {
					$array['url'] = $this->url;
				} else {
					$array['url'] = '';
				}
				$array['target'] = $this->url;
				continue;
			}
			$array[$field] = $this->{$field};
		}
		return $array;
	}

	public function markPreviewCreated(): void {
		$this->setLastPreview(time());
	}

	public function incrementClickcount(): void {
		$this->setClickcount($this->clickcount + 1);
	}

	public function setTitle(string $title): void {
		// Cap title length at 1024 because the DB doesn't have more space currently (4096 byte with utf8mb4)
		if (mb_strlen($title) > 1024) {
			$title = mb_substr($title, 0, 1023) . '…';
		}
		$this->setter('title', [$title]);
	}

	public function setDescription(string $desc): void {
		// Cap title length at 1024 because the DB doesn't have more space currently (4096 byte with utf8mb4)
		if (mb_strlen($desc) > 1024) {
			$desc = mb_substr($desc, 0, 1023) . '…';
		}
		$this->setter('description', [$desc]);
	}

	public function isWebLink() {
		return (bool) preg_match('/^https?:/i', $this->getUrl());
	}
}
