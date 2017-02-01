<?php

if(!$arr = $news->getNews()) {
    $errMsg = "Произошла ошибка при выводе новостной ленты";
};

foreach ($arr as $value) {
    echo "<p>{$value['title']}</p><br/>";
    echo "<p>{$value['description']}</p><br/>";
    echo "<p>{$value['source']}</p><a href = 'news.php?id={$value['id']}'>Удалить</a><br/>";
}