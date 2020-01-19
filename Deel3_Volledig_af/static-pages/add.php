<?php

/**
 * Includes
 * ----------------------------------------------------------------
 */

// config & functions
require_once 'includes/config.php';
require_once 'includes/functions.php';


// vars
$basePath = __DIR__ . DIRECTORY_SEPARATOR . 'images'; // C:\wamp\www\vn.an\labo03\images
$baseUrl = 'images'; // images


/*
 *
 * Twig
 */
require_once __DIR__ . '/includes/Twig-1.35.0/lib/Twig/Autoloader.php';
Twig_Autoloader::register();
$loader = new Twig_Loader_Filesystem(__DIR__ . '/templates');
$twig = new Twig_Environment($loader);
$template = $twig->loadTemplate('add.twig');
$formErrors = [];

/**
 *
 * Session
 */

session_start();
//var_dump($_SESSION);

/**
 * Database Connection
 * ----------------------------------------------------------------
 */

$db = getDatabase();


if (isset($_POST['moduleAction']) && ($_POST['moduleAction'] == 'add')) {


    var_dump($_POST);


    if (isset($_FILES['coverphoto']) && trim($_FILES['coverphoto']['name'] != '')
        && trim($_POST['title'] != '')
        && trim($_POST['numpages'] != '')
        && trim($_POST['topic_id'] != '0')
    ) {


        $f = new SplFileInfo($_FILES['coverphoto']['name']);
        $extension = $f->getExtension();


        // check file extension
        if (!in_array((new SplFileInfo($_FILES['coverphoto']['name']))->getExtension(), array('jpeg', 'jpg', 'png', 'gif'))) {
            $formErrors[] = 'De bestand Extentie is fout';
        }

        $b = getBooks();
        $e = end($b);
        $idName = $e['id'];
        var_dump($idName);
        $idName = ((int)$idName) + 1;


        // file in folder steken
        @move_uploaded_file(
            $_FILES['coverphoto']['tmp_name'],
            __DIR__ . DIRECTORY_SEPARATOR . 'files/covers' . DIRECTORY_SEPARATOR . $idName . '.' . $extension
        ) or die('<p>Error while saving file in the uploads folder</p>');


        $title = $_POST['title'];
        $numpages = $_POST['numpages'];
        $topic_id = $_POST['topic_id'];
        $userName = $_SESSION['user']['username'];
        $id = getUserId($userName);


        $stmt = $db->prepare('    INSERT INTO books (title, numpages, user_id,topic_id,cover_extension,added_on) VALUES (?,?,?,?,?,?)');
        $stmt->execute(array($title, $numpages, $id, $topic_id, $extension, '2019-10-16 15:12:44'));
        echo 'Uploaded Cover ' . $db->lastInsertId();
        var_dump($stmt);


    } else {

        //  echo 'its NOT set';
        $formErrors[] = 'Er ging iets fout met uploaden gelieve alle gegevens in te vullen';

    }

}

function getUserId($userName)
{
    $db = getDatabase();
    $stmt = $db->prepare('SELECT * FROM users WHERE username Like ?');
    $stmt->execute(array($userName));
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $items[0]['id'];
}


function getBooks()
{
    $db = getDatabase();
    $stmt = $db->prepare('SELECT * FROM books');
    $stmt->execute();
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $items;
}


$url = $_SERVER['PHP_SELF'];
echo $template->render(array(
    'action' => $_SERVER['PHP_SELF'],
    'url' => $url,
    'errors' => $formErrors
));




