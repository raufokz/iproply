<?php
/**
 * Properties Redirect
 * Forwards to listings.php for consistency
 */

$queryString = $_SERVER['QUERY_STRING'];
$redirectUrl = 'listings.php';

if (!empty($queryString)) {
    $redirectUrl .= '?' . $queryString;
}

header('Location: ' . $redirectUrl, true, 301);
exit;
?>
