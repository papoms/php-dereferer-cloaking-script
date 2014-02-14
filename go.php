<?php 
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * A php cloaked redirect script based on a form submit. Use it to reset the referer to your page before you redirect
 * @author     André Cimander <cimander@email.de>
 * @author     Paul Porzky <paulporzky@gmail.com>
 **/

$config = array();

// path to go.php (this script). Leave emtpy for default setups.
$config['hostnameAndPath'] = '';

// Default redirect URL. Used in case we have Problems resetting the referer.
$config['redirectToIfWrongReferer'] = '';


// Handles different Failures.
// Use it to show a default message / Page in Case anything goes wrong
function onFailure(){
	die('');
	// or show something instead
	// include('nolink.php'); exit...;
}

// Check redirect link
if(!isset($_REQUEST['link'])) {
    onFailure();
}

// Sanitize Link & Check
$link = urlencode(filter_var($_REQUEST['link'], FILTER_SANITIZE_URL));
if(!strlen($link)) {
    onFailure();
}

// Redirect Sequence Part 1 - This is where we end up in our first redirect loop in go.php
if(!isset($_REQUEST['go'])) { 
	
    $redirect_link_form  = $config['hostnameAndPath'].'go.php'; // redirect to go first
    $redirect_link       = $redirect_link_form.'?go=1&link='.$link;
	// Redirect Page handling down below.
	

// Redirect Sequence Part 2 - second redirect to target link	
} else { 
	
    $link = $redirect_link = $redirect_link_form = urldecode(filter_var($_REQUEST['link'], FILTER_SANITIZE_URL));

    // Sometimes our method failes. Here you can handle edge cases with an empty referer
    if (!isset($_SERVER['HTTP_REFERER']) || !strlen($_SERVER['HTTP_REFERER']) ) {
        // do something with emtpy Referers if you like.
		onFailure();
    }
    
	// Second security check. We do have a referrer, but maybe its not what we wanted.
	// if you  want to make sure not to leak any "foreign" referers, handle it here.
    if (isset($_SERVER['HTTP_REFERER']) && strlen($_SERVER['HTTP_REFERER']) && stripos( $_SERVER['HTTP_REFERER'], $config['hostnameAndPath'].'go.php' ) === false )
    {
        header("Location: ".$config['redirectToIfWrongReferer']);
        exit;
    }
    
	// Everything turned out perfect. Redirect your user to the target.
    header("Location: ".$redirect_link);
    die();
}


// Sequence 1 - Create DMR Form

// get parameters and their values for hidden form fields
$arrParts = explode('?', $redirect_link);
$arrParts = explode('&', $arrParts[1]);

$arrHiddenInputs = array();
foreach($arrParts as $part) {
    $item = explode('=', $part);
    if(count($item) != 2) {
        continue;
    }
    $arrHiddenInputs[filter_var($item[0], FILTER_SANITIZE_STRING)] = filter_var($item[1], FILTER_SANITIZE_STRING);
}

// add go parameter for real redirect
$arrHiddenInputs['go'] = 1;

header('content-type: text/html; charset=utf-8');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="de">

    <head>
        <title>Redirect Title</title>
        <meta name="robots" content="noindex" />
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    </head>
    <body>
        <h4>Redirect Label</h4>
        <form name="redirform" id="redirform" method="get" action="<?php echo $redirect_link_form; ?>">
        <?php foreach($arrHiddenInputs as $name => $value): ?> <input type="hidden" name="<?php echo $name ?>" value="<?php echo $value; ?>" /> <?php endforeach; ?>
        </form>
    
        <div style="padding: 30px; text-align: center;">
            <a href="<?php echo $redirect_link; ?>">Redirect Message Lorem Ipsum Blub - click here if you are fracking impatient or have no js</a>
        </div>
        
        <script type="text/javascript">
            document.redirform.submit();
        </script>
    </body>
</html>