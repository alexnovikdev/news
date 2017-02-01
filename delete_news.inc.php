<?php

if (!$id = $_GET["id"]) {
    header("Location: news.php");
    exit;
}

if (!$news->deleteNews($id)) {
    $errMsg = "Произошла ошибка при удалении новости";
} else {
    header("Location: news.php");
}

