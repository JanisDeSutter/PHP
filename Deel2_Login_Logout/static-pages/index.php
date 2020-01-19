<?php

/**
 * Includes
 * ----------------------------------------------------------------
 */

// config & functions
require_once 'includes/config.php';
require_once 'includes/functions.php';


/*
 *
 * Twig
 */
require_once __DIR__ . '/includes/Twig-1.35.0/lib/Twig/Autoloader.php';
Twig_Autoloader::register();
$loader = new Twig_Loader_Filesystem(__DIR__ . '/templates');
$twig = new Twig_Environment($loader);
$template = $twig->loadTemplate('index.twig');


/**
 * Database Connection
 * ----------------------------------------------------------------
 */


$db = getDatabase();


/**
 * start session
 * ----------------------------------------------------------------
 */

session_start();

if (!isset($_SESSION['user'])) {
    $_SESSION['user'] = '';
}


/**
 * Get the topic id of the url
 */

$topicid = isset($_GET['topic']) ? (int)$_GET['topic'] : 0; // The passed topic in the URL


/**
 * Database request data functions
 */

function getAllItems()
{
    $db = getDatabase();
    $stmt = $db->prepare('SELECT * FROM books ORDER BY title ASC');
    $stmt->execute();
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $items;
}

function getTopicId($topicName)
{
    $db = getDatabase();
    $stmt = $db->prepare('SELECT * FROM topics WHERE title Like ?');
    $stmt->execute(array($topicName));
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $items[0]['id'];
}

function getTopicName($topicId)
{
    $db = getDatabase();
    $stmt = $db->prepare('SELECT * FROM topics WHERE id Like ?');
    $stmt->execute(array($topicId));
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $items[0]['title'];
}

function getItemsFromTopic($topicId)
{
    $db = getDatabase();
    $stmt = $db->prepare('SELECT * FROM books WHERE topic_id Like ?');
    $stmt->execute(array($topicId));
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $items;
}

function getUsers()
{
    $db = getDatabase();
    $stmt = $db->prepare('SELECT * FROM users');
    $stmt->execute();
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $items;
}

function getTopics()
{
    $db = getDatabase();
    $stmt = $db->prepare('SELECT * FROM topics');
    $stmt->execute();
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $items;

}

//alle items weergeven als topic id 0 is
if ($topicid === 0) {
    $items = getAllItems();
} //items by ID weergeven als topic id anders dan 0 is
else {
    $items = getItemsFromTopic($topicid);
}

$users = getUsers();
$topics = getTopics();

$userSet = [];
$topicSet = [];

foreach ($users as $user) {
    $userSet[$user['id']] = $user['username'];
}

foreach ($topics as $topic) {
    $topicSet[$topic['id']] = $topic['title'];
}

$url = $_SERVER['PHP_SELF'];
echo $template->render(array(
    'url' => $url,
    'items' => $items,
    'users' => $users,
    'userSet' => $userSet,
    'topicSet' => $topicSet,
    'user' => $_SESSION['user']
));



