<?php

/**
 * this file is for viewing
 * 
 */
$current_path = dirname(realpath(__FILE__));
//$current_path = preg_replace('/\import/i', '', $current_path);

$protocol = (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && !is_null($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') ? 'https://' : 'http://';
$http_host = $protocol . $_SERVER["HTTP_HOST"];

//if(file_exists($current_path.'configs/config.php')) {
//	require_once $current_path.'configs/config.php';
//}


if (session_id() == '') {
    session_start();
}    // moet altijd na de classes

global $obj_db;

require_once $current_path . '/functions/functions.class.php';
require_once $current_path . '/external/smarty/Smarty.class.php';

require_once $current_path . '/configs/db.config.php';
//database_close();
database_connect();

define('AMOUNT_GENERATIONS', 10);
define('CURRENT_URL', $http_host);

global $obj_db;

global $obj_smarty;
$obj_smarty = new Smarty();
$obj_smarty->compile_dir = $current_path . '/templates_c/';

//echo 'view';

if (!isset($_SESSION['gedcom_viewer'])) {
    $_SESSION['gedcom_viewer'] = array();
}
if (!isset($_SESSION['gedcom_viewer']['file_id'])) {
    $_SESSION['gedcom_viewer']['file_id'] = 118;
}

$str_gedcom_file_id = $_SESSION['gedcom_viewer']['file_id'];

$str_individual_id = null;
if (isset($_GET['i'])) {
    $str_individual_id = $_GET['i'];
}

if (isset($_GET['f'])) {
    $str_gedcom_file_id = $_GET['f'];
    $_SESSION['gedcom_viewer']['file_id'] = $str_gedcom_file_id;
}

if (isset($_GET['c'])) {
    $str_child_id = $_GET['c'];

    // zoeken in families (FIND_IN_SET)
    $str_query = 'SELECT * FROM `families` f
                 WHERE `gedcom_file_id` = ' . $str_gedcom_file_id . ' AND FIND_IN_SET("' . $str_child_id . '", `children`)';
    // had ook via famc gekund

    $obj_result = mysqli_query($obj_db, $str_query);

    $arr_line = mysqli_fetch_array($obj_result, MYSQLI_ASSOC);

    if (is_array($arr_line) && !empty($arr_line) && (!empty($arr_line['husband']) || !empty($arr_line['wife']))) {
        if (!empty($arr_line['husband'])) {
            $str_individual_id = $arr_line['husband'];
        } else if (!empty($arr_line['wife'])) {
            $str_individual_id = $arr_line['wife'];
        }
    }
}



// find families incl children
//$str_query = 'SELECT f.* FROM `families` f WHERE 1';

if ($str_gedcom_file_id > 0) {
    if (is_null($str_individual_id)) {
        $str_file_query = 'SELECT * FROM `gedcom_files` WHERE file_id = ' . $str_gedcom_file_id;

        $obj_file_result = mysqli_query($obj_db, $str_file_query);

        $arr_file = mysqli_fetch_array($obj_file_result, MYSQLI_ASSOC);
        //var_dump($arr_file);

        if (is_null($arr_file)) {
            echo 'file with ID ' . $str_gedcom_file_id . ' not found';
        } else {
            $str_individual_id = $arr_file['first'];
        }
    }

    //$str_query .= ' AND f.gedcom_file_id = ' . $str_gedcom_file_id ;
    //$str_query .= ' AND (f.husband = "' . $str_individual_id . '" OR f.wife = "' . $str_individual_id . '")';
}
//echo $str_query;
//$str_query .= ' AND f.gedcom_family_id like "F1261836531"';
//echo $str_query;
//$obj_result = mysqli_query($obj_db, $str_query);

$arr_return = array();

echo '<html><head></head><body>';
echo '<div style="font-family:tahoma,verdana,helvetica;font-size:14px;">';
echo '<div style="padding-left:180px;">';
echo '<style type="text/css">
div.header {
    display: block; text-align: center; 
    position: running(header);
}
div.footer {
    display: block; text-align: center;
    position: running(footer);
}
@page {
    @top-center { content: element(header) }
}
@page { 
    @bottom-center { content: element(footer) }
}    
body {
        padding-top: 60px;
        
    }
   html {background-color:white;}
    .mouseover {
        display: none;
        font-size:14px;
    }
    .tooltiptext {
        cursor:pointer;
    }
    
  </style>
  <script type="text/javascript" src="' . $http_host . '/gedcom_viewer/external/jquery/jquery-1.8.3.js"></script>
  <script type="text/javascript" src="' . $http_host . '/gedcom_viewer/external/qtip/qtip.js"></script>
      <link rel="stylesheet" type="text/css" href="' . $http_host . '/gedcom_viewer/external/qtip/qtip.css" />
          <link rel="stylesheet" type="text/css" href="'.$http_host.'/gedcom_viewer/style/print.css" media="print" />

  <script>
  
  $(document).ready(function()
 {
     // MAKE SURE YOUR SELECTOR MATCHES SOMETHING IN YOUR HTML!!!
     $("span.tooltiptext").each(function() {
         $(this).qtip({
             content: {
                 text: $(this).prev(".mouseover")
             }
         });
     });
 });
//  $( function() {
//    $( document ).tooltip();
//  } );
  </script>';

//$arr_first = mysqli_fetch_array($obj_result, MYSQLI_ASSOC);
//if(is_null($arr_first)) {
//    echo ('niks gevonden: '. $str_query);
//}
//while ($arr_line = mysqli_fetch_array($obj_result, MYSQLI_ASSOC)) {print_r($arr_line);
// find children
//$arr_children = explode(',', $arr_first['children']);
$str_children = '';
$arr_fam = array();
$arr_line = array();

//if (!empty($arr_children) && !empty($arr_children[0])) {
//    $child_id = $arr_first['husband'];
//} else {
$child_id = $str_individual_id;
//}


$str_top_div = '';
$arr_top_divs = array();
$arr_indivs_in_current_tree = array();

// all individuals
//$arr_all_indivs = Functions::getAllIndividualsOfFile($str_gedcom_file_id);

$arr_children = Functions::hasChildren($child_id, 0, $str_gedcom_file_id, $arr_top_divs, $arr_indivs_in_current_tree);
//print_r($arr_children);
echo '</div>';

//print_r($arr_indivs_in_current_tree);
$arr_indivs_not_in_current_tree = Functions::getIndividualsNotInCurrentTree($str_gedcom_file_id, $arr_indivs_in_current_tree);
//print_r($arr_indivs_not_in_current_tree);
$str_query_child = 'SELECT * FROM `individuals` i WHERE `gedcom_file_id` = "' . $str_gedcom_file_id . '" AND `gedcom_individual_id` = "' . $child_id . '"';

$obj_result_child = mysqli_query($obj_db, $str_query_child);

$arr_child = mysqli_fetch_array($obj_result_child, MYSQLI_ASSOC);

$str_children .= ',' . utf8_decode($arr_child['name']);

// is this child a husband or wife
$str_query_fam = 'SELECT * FROM `families` f WHERE `gedcom_file_id` = ' . $str_gedcom_file_id . ' AND (`husband` = "' . $child_id . '" OR `wife` = "' . $child_id . '")';

$obj_result_fam = mysqli_query($obj_db, $str_query_fam);

$arr_fam = mysqli_fetch_array($obj_result_fam, MYSQLI_ASSOC);
// }
$arr_line = $arr_children;
//$arr_line['family'] = $arr_fam;
//$arr_line['children_names'] = $str_children;

$arr_return[] = $arr_line;
//}
//print_r($arr_return);
$previous = -1;
foreach ($arr_top_divs as $birthdate) {
    $results = array();
    preg_match('/[0-9]{4}/', $birthdate, $results);

    if (!empty($results) && !empty($results[0])) {
        if (empty($str_top_div)) {
            $str_top_div .= '<span style="">' . $results[0] . '</span>';
        } else {
            $str_top_div .= '<span style="padding-left:69px;">' . $results[0] . '</span>';
        }
        $previous = $results[0];
    } else {
        if ($previous > 0) {
            $str_top_div .= '<span style="padding-left:69px;">' . ((int) $previous + 27) . '</span>';
        }
    }
}

echo '</div>';

echo '<script>$(document).ready(function() {
    $("select").change(function() {

    window.location.href = "'.$http_host.'/gedcom_viewer/?f="+$(this).val()
  
});
});
</script>';
echo '<span style="position:fixed;left:5;top:0;padding:5px;"><select>';

// vanuit sessie  $_SESSION['gedcom_viewer']['files']
// nu eerst vanuit de db
$arr_files = Functions::getFiles();

foreach ($arr_files as $file) {
    echo '<option value="' . $file['file_id'] . '" '.($file['file_id'] == $_SESSION['gedcom_viewer']['file_id'] ? 'selected="selected"' : '').'>' .  $file['path'] . '</option>';
}

echo '</select></span>';

echo '<div style="background-color:yellow;height:20px;width:98%;padding:5px;top:30;padding-left:180px;margin-bottom:50px;position:fixed;">' . $str_top_div . '</div>';
echo '<div class="left_block no-print" style="background-color:light-gray;border:1px solid gray;width:150px;height:100%;overflow-y:scroll;padding:5px;top:58px;position:fixed;"><div style="font-weight:bold;">Others:</div>';

// left block
foreach ($arr_indivs_not_in_current_tree as $indiv_id => $arr_indiv) {
    echo '<div style="padding-bottom:6px;"><a href="' . $http_host . '/gedcom_viewer/?i=' . $arr_indiv['gedcom_individual_id'] . '">' . utf8_decode($arr_indiv['name']) . (!empty($arr_indiv['birth_year']) ? ' (' . $arr_indiv['birth_year'] . ')' : '') . '</a></div>';
}


echo '</div>';
echo '</body></html>';


//$obj_smarty->assign('results', $arr_return); 

//$obj_smarty->display( $current_path. '/view/test.tpl');



// find first ancestor


// find children


// find husband / wife

