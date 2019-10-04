<?php

require_once "webauto.php";
require_once "names.php";

use Goutte\Client;

$code = $USER->id+$CONTEXT->id;

$check = webauto_get_check_full();

$meta = '<meta name="wa4e" content="'.$check.'">';

$user1account = 'dj4e_user1';
$user1pw = "Meow_" . substr(getMD5(),1,6). '_41';
$user2account = 'dj4e_user2';
$user2pw = "Meow_42_" . substr(getMD5(),1,6);

$now = date('H:i:s');

line_out("Building Classified Ad Site #3");

?>
<a href="../../assn/dj4e_ads3.md" target="_blank">
https://www.dj4e.com/assn/dj4e_ads3.md</a>
</a>
<p>
You should already have two users and a <b>meta</b> tag.
<pre>
<?= htmlentities($user1account) ?> / <?= htmlentities($user1pw) ?>  
<?= htmlentities($user2account) ?> / <?= htmlentities($user2pw) ?> 
<?= htmlentities($meta) ?>
</pre>
Note that your application should not be at the '/m2' path and should not
have a "Versions" drop-down.  That is just how the sample implementation is written
to support more than one variant of the code at the same time.
</p>
<?php

$url = getUrl('https://chucklist.dj4e.com/m3');
if ( $url === false ) return;

webauto_check_test();
$passed = 0;

// http://symfony.com/doc/current/components/dom_crawler.html
$client = new Client();
$client->setMaxRedirects(5);

// Load the Favicon
// https://en.wikipedia.org/wiki/ICO_(file_format)

line_out("Loading the favicon...");
$favicon_url = $base_url_path . '/favicon.ico';
$crawler = $client->request('GET', $favicon_url);
if ( $crawler === false ) {
    error_out("Unable to load favicon");
    return;
}
$response = $client->getResponse();
$status = $response->getStatus();
if ( $status !== 200 ) {
    error_out("Unable to load favicon status=".$status);
    return;
}
$content = $response->getContent();
$favlen = strlen($content);
$favmd5 = md5($content);
// echo("<pre>\n"); echo("Len = ".strlen($content)); echo(" md5 = ".$favmd5);

if ( $favlen == 15406 && $favmd5 == 'da98cfb3992c3d6985fc031320bde065' ) {
    error_out("Please replace the favicon to be something other than the default.");
    error_out("5 point dediction.");
    $passed = $passed - 5;
} else {
    success_out("Favicon loaded");
    $passed = $passed + 1;
}


for($test_no=0; $test_no<2; $test_no++) {
if ( $test_no == 0 ) {
    $useraccount = $user1account;
    $userpw = $user1pw;
} else {
    line_out('');
    line_out('----------------------');
    success_out("Rerunning tests with the second user");
    $useraccount = $user2account;
    $userpw = $user2pw;
}

// Start the actual test
line_out("Loading web site...");
$crawler = webauto_get_url($client, $url);
if ( $crawler === false ) return;

$html = webauto_get_html($crawler);

line_out("Checking meta tag...");
$retval = webauto_search_for($html, $meta);
$meta_good = true;
if ( $retval === False ) {
    error_out('You seem to be missing the required meta tag.  Check spacing.');
    error_out('Assignment will not be scored.');
    $meta_good = false;
}
$login_url = webauto_get_url_from_href($crawler,'Login');


$crawler = webauto_get_url($client, $login_url, "Logging in as $useraccount");
$html = webauto_get_html($crawler);

// Use the log_in form
$form = webauto_get_form_with_button($crawler,'Login Locally');
webauto_change_form($form, 'username', $useraccount);
webauto_change_form($form, 'password', $userpw);

$crawler = $client->submit($form);
$html = webauto_get_html($crawler);

if ( webauto_dont_want($html, "Your username and password didn't match. Please try again.") ) return;

// Cleanup old ads
$saved = $passed;
preg_match_all("'\"([a-z0-9/]*/[0-9]+/delete)\"'",$html,$matches);
// echo("\n<pre>\n");var_dump($matches);echo("\n</pre>\n");

if ( is_array($matches) && isset($matches[1]) && is_array($matches[1]) ) {
    foreach($matches[1] as $match ) {
        $crawler = webauto_get_url($client, $match, "Loading delete page for old record");
        $html = webauto_get_html($crawler);
        $form = webauto_get_form_with_button($crawler,'Yes, delete.');
        $crawler = $client->submit($form);
        $html = webauto_get_html($crawler);
    } 
}
$passed = $saved;

$create_ad_url = webauto_get_url_from_href($crawler,"Create Ad");
$crawler = webauto_get_url($client, $create_ad_url, "Retrieving create ad page...");
$html = webauto_get_html($crawler);

if ( ! webauto_search_for_not($html, "owner") ) {
    error_out('The owner field is not supposed to appear in the create form.');
    return;
}

if ( ! webauto_search_for_not($html, "comment") ) {
    error_out('The comments field is not supposed to appear in the create form.');
    return;
}

// TODO: Make this required
// Sanity check the new ad page
if ( strpos($html, 'type="file"') < 1 ) {
    error_out("Create Ad form cannot upload a file");
}

if ( strpos($html, 'window.File') < 1 ) {
    error_out("Create Ad page appears to be missing JavaScript to check the size of the uploaded file");
}

if ( strpos($html, 'multipart/form-data') < 1 ) {
    error_out('Create Ad form requires enctype="multipart/form-data"');
}

// Add a record
$title = 'HHGTTG_41 '.$now;
$form = webauto_get_form_with_button($crawler,'Submit');
webauto_change_form($form, 'title', $title);
webauto_change_form($form, 'price', '0.41');
webauto_change_form($form, 'text', 'Low cost Vogon poetry.');

$crawler = $client->submit($form);
$html = webauto_get_html($crawler);

if ( ! webauto_search_for($html, $title) ) {
    error_out('Tried to create a record and cannot find the record in the list view');
    return;
}

// ad/3/favorite

preg_match_all("#'([a-z0-9/]*/[0-9]+/favorite)'#",$html,$matches);
// echo("\n<pre>\n");var_dump($matches);echo("\n</pre>\n");

if ( is_array($matches) && isset($matches[1]) && is_array($matches[1]) && count($matches[1]) > 0 ) {
    foreach($matches[1] as $match ) {
        line_out("Retrieving $match");
        $crawler = $client->request('POST', $match);
        if ( $crawler === false ) {
            error_out("Error POSTING to favorite url: ".$match);
            return;
        }
        $status = $response->getStatus();
        if ( $status !== 200 ) {
            error_out("Error posting to favorite url: ".$match." status=".$status);
            return;
        }
        success_out("Favorited success");
        break;
    } 
} else {
    error_out("Could not find link to favorite 'ad/nnn/favorite'");
    return;
}

// ad/3/unfavorite

preg_match_all("#'([a-z0-9/]*/[0-9]+/unfavorite)'#",$html,$matches);

if ( is_array($matches) && isset($matches[1]) && is_array($matches[1]) && count($matches[1]) > 0 ) {
    foreach($matches[1] as $match ) {
        $crawler = $client->request('POST', $match);
        if ( $crawler === false ) {
            error_out("Error POSTING to unfavorite url: ".$match);
            return;
        }
        $status = $response->getStatus();
        if ( $status !== 200 ) {
            error_out("Error posting to unfavorite url: ".$match." status=".$status);
            return;
        }
        success_out("UnFavorite success");
        break;
    } 
} else {
    error_out('Could not find link to favorite ad/nnn/unfavorite');
    return;
}

line_out('');
line_out('Test completed... Going to the main page and Logging out.');
$crawler = webauto_get_url($client, $url);
if ( $crawler === false ) return;
$html = webauto_get_html($crawler);
$logout_url = webauto_get_url_from_href($crawler,'Logout');
$crawler = webauto_get_url($client, $logout_url, "Logging out...");
$html = webauto_get_html($crawler);

} // End of the for

// -------
line_out(' ');
echo("<!-- Raw score $passed -->\n");
// echo("  -- Raw score $passed \n");
$perfect = 13;
if ( $passed < 0 ) $passed = 0;
$score = webauto_compute_effective_score($perfect, $passed, $penalty);

// if ( $score < 1.0 ) autoToggle();

if ( ! $meta_good ) {
    error_out("Not graded - missing meta tag");
    return;
}
if ( webauto_testrun($url) ) {
    error_out("Not graded - sample solution");
    return;
}

// Send grade
if ( $score > 0.0 ) webauto_test_passed($score, $url);

