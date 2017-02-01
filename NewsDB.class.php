<?php

require_once "INewsDB.class.php";

class NewsDB implements INewsDB {

    const DB_NAME = "../news.db";
    private $_db = null;

    const RSS_NAME = "rss.xml";
    const RSS_TITLE = "Последние новости";
    const RSS_LINK = "http://mysite.local3/news/news.php";

    function __get($name) {
        //db -> _db
        if ($name == "_db") {
            return $this->_db;
        }
        throw new Exception("Unknown property!");
    }

    function __construct() {

        $this->_db = new SQLite3(self::DB_NAME);

        if (filesize(self::DB_NAME) == 0) {

           try {
               $sql1 = "CREATE TABLE msgs(
                      id INTEGER PRIMARY KEY AUTOINCREMENT,
                      title TEXT,
                      category INTEGER,
                      description TEXT,
                      source TEXT,
                      datetime INTEGER
                    )";

               $sql2 = "CREATE TABLE category(
                      id INTEGER,
                      name TEXT
                    )";

               $sql3 = "INSERT INTO category(id, name)
                      SELECT 1 as id, 'Политика' as name
                      UNION SELECT 2 as id, 'Культура' as name
                      UNION SELECT 3 as id, 'Спорт' as name";

               if (!$this->_db->exec($sql1)) {
                   throw new Exception($this->_db->lastErrorMsg());
               }

               if (!$this->_db->exec($sql2)) {
                   throw new Exception($this->_db->lastErrorMsg());
               }

               if (!$this->_db->exec($sql3)) {
                   throw new Exception($this->_db->lastErrorMsg());
               }

           } catch (Exception $e) {
               file_put_contents("error.log", $e->getMessage(), FILE_APPEND);
               echo "Ошибка!";
           }

        }
    }

    function __destruct() {
        unset($this->_db);
    }

    function saveNews($title, $category, $description, $source) {

        $dt = time();

        $sql = "INSERT INTO msgs (title, category, description, source, datetime)
                  VALUES ('$title', $category, '$description', '$source', $dt)";

        $res = $this->_db->exec($sql);
        if (!$res) return false;
        $this->createRss();
        return true;
    }

    private function db2Array($data) {
        $arr = [];
        while ($row = $data->fetchArray(SQLITE3_ASSOC)) {
            $arr[] = $row;
        }
        return $arr;
    }

    function getNews() {

        $sql = "SELECT msgs.id as id, title, category.name as category,
                description, source, datetime FROM msgs, category WHERE
                category.id = msgs.category ORDER BY msgs.id DESC";

        $result = $this->_db->query($sql);
        if (!$result) return false;
        return $this->db2Array($result);
    }

    function deleteNews($id) {
        $sql = "DELETE FROM msgs WHERE id = $id";
        if (!$this->_db->exec($sql)) {
            return false;
        }
        return true;
    }

    function clearStr($data) {
        $data = strip_tags(trim($data));
        return $this->_db->escapeString($data);
    }

    function clearInt($data) {
        return abs((int)$data);
    }

    private function createRss() {
        $dom = new DomDocument("1.0", "utf-8");
        $dom->formatOutput = true;
        $dom->preserveWhiteSpace = false;
        $rss = $dom->createElement("rss");
        $dom->appendChild($rss);
        $version = $dom->createAttribute("version");
        $version->value = "2.0";
        $rss->appendChild($version);
        $channel = $dom->createElement("channel");
        $rss->appendChild($channel);
        $title = $dom->createElement("title", self::RSS_TITLE);
        $channel->appendChild($title);
        $link = $dom->createElement("link", self::RSS_LINK);
        $channel->appendChild($link);
        $arr = $this->getNews();
        foreach ($arr as $value) {
            $item = $dom->createElement("item");
            $titlenews = $dom->createElement("title", $value["title"]);
            $linknews = $dom->createElement("link", self::RSS_LINK);
            $description = $dom->createElement("description");
            $cdata = $dom->createCDATASection($value["description"]);
            $description->appendChild($cdata);
            $pubDate = $dom->createElement("pubDate", date("d-m-Y H:i:s", $value["datetime"]));
            $category = $dom->createElement("category", $value["category"]);
            $item->appendChild($titlenews);
            $item->appendChild($linknews);
            $item->appendChild($description);
            $item->appendChild($pubDate);
            $item->appendChild($category);
            $channel->appendChild($item);
        }
        $dom->save(self::RSS_NAME);
    }
}