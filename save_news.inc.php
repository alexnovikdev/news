<?php

$title = $news->clearStr($_POST["title"]);
$category = $news->clearInt($_POST["category"]);
$description = $news->clearStr($_POST["description"]);
$source = $news->clearStr($_POST["source"]);

if (empty($title) or empty($description)) {
    $errMsg = "Заполните все поля формы";
} else {
    if (!$news->saveNews($title, $category, $description, $source)) {
        $errMsg = "Произошла ошибка при добавлении новости";
    } else {
        header("Location: news.php");
        exit;
    }
}