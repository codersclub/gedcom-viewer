<?php


$current_path = dirname ( realpath ( __FILE__ ) );
$current_path = preg_replace('/select/i', '', $current_path);

$protocol = (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && !is_null($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') ? 'https://' : 'http://';
$http_host = $protocol . $_SERVER["HTTP_HOST"];

//if(file_exists($current_path.'configs/config.php')) {
//	require_once $current_path.'configs/config.php';
//}


if (session_id() == '') { session_start(); }    // moet altijd na de classes

require_once $current_path.'/configs/db.config.php';
//database_close();
database_connect();

global $obj_db;




echo '<link href="'.$http_host.'/gedcom_viewer/external/bootstrap/bootstrap.min.css" rel="stylesheet"/>
   <style>



body, html{
     height: 100%;
 	background-repeat: no-repeat;
 	background-color: #d3d3d3;
 	font-family: "Lucida Grande",Helvetica,Arial,Verdana,sans-serif, sans-serif;
}

.main{
 	margin-top: 70px;
}



hr{
	width: 100%;
	color: #fff;
}

.form-group{
	margin-bottom: 15px;
}

label{
	margin-bottom: 15px;
}

input,
input::-webkit-input-placeholder {
    font-size: 11px;
    padding-top: 3px;
}


.main-center{
 	margin-top: 30px;
 	margin: 0 auto;
 	max-width: 380px;
    padding: 40px 40px;

}

.login-button{
	margin-top: 5px;
}

.login-register{
	font-size: 12px;
	text-align: center;
}

hr {
    margin-bottom: 0;
}    
    
  </style>
 
<div class="main-center">
    
  <div id="form-div" style="padding-top:50px;">
    <form class="montform form-horizontal" id="reused_form" enctype="multipart/form-data" method="POST" action="'.$http_host.'/gedcom_viewer/import/?action=upload">
      <div class="form-group" style="margin-left:0;">
            <label for="username" class="cols-sm-2 control-label" style="margin-bottom:20px;" id="login_username_label">Select your GEDCOM file</label><br />
            <div class="cols-sm-2">
                    <div class="file">
                            <input name="gedcom" type="file" id="file" class="feedback-input">
                    </div>
            </div>
    </div>
       
            <div class="submit">
        <button type="submit" class="button-blue" style="color:blue;font-weight:bold;">CONTINUE</button>
        <div class="ease"></div>
      </div>
    </form>
          <div id="error_message" style="width:100%; height:100%; display:none; ">
                <h4>Error</h4>
                Sorry there was an error sending your form.
          </div>
          <div id="success_message" style="width:100%; height:100%; display:none; ">
          <h2>Success! Your Message was Sent Successfully.</h2>
          </div>
    </div>
</div>';

exit;
$path_to_file = '/Applications/XAMPP/htdocs/gedcom_viewer/myown.ged';
//$path_to_file = '/Applications/XAMPP/htdocs/gedcom_viewer/stamboomnov2013_bewerktaug2018.ged';
//$path_to_file = '/Applications/XAMPP/htdocs/gedcom_viewer/example.ged';

//$str_gedcom_contents = file_get_contents('http://gedcom.idavid.hu/LincolnFamily.ged');
//$str_gedcom_contents = file_get_contents('/Applications/XAMPP/htdocs/gedcom_viewer/mijn_tak_incl_Mebben.ged');
$str_gedcom_contents = file_get_contents($path_to_file);


//myown.ged

$str_gedcom_contents = mb_convert_encoding($str_gedcom_contents, 'UTF-8', 'Windows-1252');

//print_r($str_gedcom_contents);

$arr_lines = explode("\n", $str_gedcom_contents);

//print_r($arr_lines);

$arr_result = array();
$arr_set = array();

foreach($arr_lines as $line) {
    
    if(substr($line, 0, 1) == 0) {
        $indi_id = $line;
        
        $indi_id = str_replace("\r",'',$indi_id);
        
        $arr_result[$indi_id] = $arr_set;
        
        
        // start new array 
        //$arr_set[$indi_id] = array();
        
        
    } else {
        array_push($arr_result[$indi_id], $line);
    }
}

//print_r($arr_result);

// todo 2 FILE


$arr_individuals = array();
$arr_families = array();
$bln_reached_individuals = false;
$bln_reached_families = false;
$gedcom_file_id = -1;

foreach($arr_result as $key => $res_line) {
    if(!$bln_reached_families && !$bln_reached_families && strstr($key, 'HEAD')) {
        
        $str_path = '';
        
        foreach($res_line as $head_part) {
            if(strstr($head_part, 'FILE')) {
                $str_path = str_replace('1 FILE ','',$head_part);
                break;
            }
        }
      
        if(!empty($str_path)) {
            $obj_db->query('INSERT INTO `gedcom_files` SET `path` = "'.addslashes($str_path).'"');
        
        } else {
            $obj_db->query('INSERT INTO `gedcom_files` SET `path` = "'.$path_to_file.'"');
        
        }
        
        $gedcom_file_id = $obj_db->insert_id;
        
        if($gedcom_file_id > 0) {
            if (!isset($_SESSION['gedcom_viewer'])) {
                $_SESSION['gedcom_viewer'] = array();
            }
            $_SESSION['gedcom_viewer']['file_id'] = $gedcom_file_id;
            
            // continue
        } else {
            die('something went wrong while inserting the path in the gedcom_files table.<br />A file with the same name was already imported.<br />Rename the file and try again.');
        }
    }
    
    if(strstr($key, 'NOTE')) {
        $note_id = str_replace('0 @','',$key);
        $note_id = str_replace('@ NOTE','',$note_id);
        $note_id = str_replace("\r",'',$note_id);
        
        $arr_notes[$note_id] = $res_line;
        $str_note = '';
        
        foreach($res_line as $note_part) {
            $line_note = str_replace('1 CONC ', ' ', $note_part);
            $line_note = str_replace('1 CONT ', '', $line_note);
          
            $str_note .= $line_note;
            

        }
        
        $obj_db->query('INSERT INTO `notes` SET `note` = "'.$str_note.'", `gedcom_note_id` = "'.$note_id.'",'.
                    ' `file_id` = '. $gedcom_file_id);
    }
    
    if(strstr($key, 'INDI')) {
        $ind_id = str_replace('0 @','',$key);
        $ind_id = str_replace('@ INDI','',$ind_id);
        $ind_id = str_replace("\r",'',$ind_id);
        
        $arr_individuals[$ind_id] = $res_line;
        $str_name = '';
        $str_nickname = '';
        $str_gender = '';
        $str_occupation = '';
        $str_note_id = '';
        $str_religion = '';
        $str_birth_date = '';
        $str_birth_place = '';
        $str_baptism_date = '';
        $str_baptism_place = '';
        $str_death_date = '';
        $str_death_place = '';
        $str_death_cause = '';
        $str_buried_date = '';
        $str_buried_place = '';
        $str_fam_spouse = '';
        $str_fam_child = '';
        $bln_line_is_birth_date = false;
        $bln_line_is_death_date = false;
        $bln_line_is_baptism_date = false;
        $bln_line_is_buried_date = false;
        $bln_line_is_save_date = false;
        $bln_line_is_channel_date = false;
        
        foreach($res_line as $ind_part) {
            if(strstr($ind_part, '1 NAME')) {
                $str_name = str_replace('1 NAME ','',$ind_part);
                $str_name = str_replace("\r",'',$str_name);
            }
            if(strstr($ind_part, '2 NICK')) {
                $str_nickname = str_replace('2 NICK ','',$ind_part);
                $str_nickname = str_replace("\r",'',$str_nickname);
            }
            if(strstr($ind_part, '1 SEX')) {
                $str_gender = str_replace('1 SEX ','',$ind_part);
                if(substr($str_gender, 0,1) == 'M') {
                    $str_gender = 'M';
                } else if(substr($str_gender, 0,1) == 'F') {
                    $str_gender = 'F';
                } else {
                    $str_gender = 'U';
                }
            }
            if(strstr($ind_part, '_NEW')) {
                $bln_line_is_save_date = true;
                continue;
            }
            if($bln_line_is_save_date && strstr($ind_part, '2 DATE')) {
                $bln_line_is_save_date = false;
                continue;
            }
            if(strstr($ind_part, 'CHAN')) {
                $bln_line_is_channel_date = true;
                continue;
            }
            if($bln_line_is_channel_date && strstr($ind_part, '2 DATE')) {
                $bln_line_is_channel_date = false;
                continue;
            }
            
            if(strstr($ind_part, '1 OCCU')) {
                $str_occupation = str_replace('1 OCCU ','',$ind_part);
            }
            if(strstr($ind_part, '1 NOTE')) {
                // example: 1 NOTE @N34@
                $str_note_id = str_replace('1 NOTE @','',$ind_part);
                $str_note_id = str_replace('@','',$str_note_id);
                $str_note_id = str_replace("\r",'',$str_note_id);
            }
            if(strstr($ind_part, '1 RELI')) {
                $str_religion = str_replace('1 RELI ','',$ind_part);
            }
            if(strstr($ind_part, '1 FAMC')) {
                $str_fam_child = str_replace('1 FAMC ','',$ind_part);
                $str_fam_child = str_replace('@','',$str_fam_child);
                $str_fam_child = str_replace("\r",'',$str_fam_child);
            }
            if(strstr($ind_part, '1 FAMS')) {
                $str_fam_spouse = $str_fam_spouse . (!empty($str_fam_spouse) ? ',' : '') . str_replace('1 FAMS ','',str_replace('@','',$ind_part));
                $str_fam_spouse = str_replace("\r",'',$str_fam_spouse);
            }
            if(strstr($ind_part, '1 BIRT')) {
                $bln_line_is_birth_date = true;
                continue;
            }
            if($bln_line_is_birth_date && strstr($ind_part, '2 DATE')) {
                $str_birth_date = str_replace('2 DATE ','',$ind_part);
                $str_birth_date = str_replace("\r",'',$str_birth_date);
            }
            if($bln_line_is_birth_date && strstr($ind_part, '2 PLAC')) {
                $str_birth_place = str_replace('2 PLAC ','',$ind_part);
                $str_birth_place = str_replace("\r",'',$str_birth_place);
                $bln_line_is_birth_date = false;
            }
            if(strstr($ind_part, '1 BAPM') || strstr($ind_part, '1 CHR')) {
                $bln_line_is_birth_date = false;
                $bln_line_is_baptism_date = true;
                continue;
            }
            if($bln_line_is_baptism_date && strstr($ind_part, '2 DATE')) {
                $str_baptism_date = str_replace('2 DATE ','',$ind_part);
                $str_baptism_date = str_replace("\r",'',$str_baptism_date);
            }
            if($bln_line_is_baptism_date && strstr($ind_part, '2 PLAC')) {
                $str_baptism_place = str_replace('2 PLAC ','',$ind_part);
                $str_baptism_place = str_replace("\r",'',$str_baptism_place);
                $bln_line_is_baptism_date = false;
            }
            if(strstr($ind_part, '1 DEAT')) {
                $bln_line_is_birth_date = false;
                $bln_line_is_baptism_date = false;
                $bln_line_is_death_date = true;
                continue;
            }
            if($bln_line_is_death_date && strstr($ind_part, '2 DATE')) {
                $str_death_date = str_replace('2 DATE ','',$ind_part);
                $str_death_date = str_replace("\r",'',$str_death_date);
            }
            if($bln_line_is_death_date && strstr($ind_part, '2 PLAC')) {
                $str_death_place = str_replace('2 PLAC ','',$ind_part);
                $str_death_place = str_replace("\r",'',$str_death_place);
            }
            if($bln_line_is_death_date && strstr($ind_part, '2 CAUS')) {
                $str_death_cause = str_replace('2 CAUS ','',$ind_part);
                $bln_line_is_death_date = false;
            }
            if(strstr($ind_part, '1 BURI')) {
                $bln_line_is_death_date = false;
                $bln_line_is_birth_date = false;
                $bln_line_is_baptism_date = false;
                $str_death_date = '';
                
                $bln_line_is_buried_date = true;
                continue;
            }
            if($bln_line_is_buried_date && strstr($ind_part, '2 DATE')) {
                $str_buried_date = str_replace('2 DATE ','',$ind_part);
            }
            if($bln_line_is_buried_date && strstr($ind_part, '2 PLAC')) {
                $str_buried_place = str_replace('2 PLAC ','',$ind_part);
                $bln_line_is_buried_date = false;
            }
        }
        $obj_db->query('INSERT INTO `individuals` SET `name` = "'.$str_name.'", `gedcom_individual_id` = "'.$ind_id.'", '.
                '`nickname` = "'.$str_nickname.'", `gedcom_file_id` = "'.$gedcom_file_id.'", '.
                '`gender` = "'.$str_gender.'", `birth_date` = "'.$str_birth_date.'", `birth_place` = "'.$str_birth_place.'"'.
                ', `baptism_date` = "'.$str_baptism_date.'", `baptism_place` = "'.$str_baptism_place.'"'.
                ', `death_date` = "'.$str_death_date.'", `death_place` = "'.$str_death_place.'"'.
                ', `gedcom_note_id` = "'.$str_note_id.'"'.
                ', `buried_date` = "'.$str_buried_date.'", `buried_place` = "'.$str_buried_place.'"'.
                ', `death_cause` = "'.$str_death_cause.'", `occupation` = "'.$str_occupation.'", `religion` = "'.$str_religion.'"'.
                ', `spouse_of_family_id` = "'.$str_fam_spouse.'", `child_of_family_id` = "'.$str_fam_child.'"');
        
        $str_name = '';
        $str_nickname = '';
        $str_gender = '';
        $str_occupation = '';
        $str_note_id = '';
        $str_religion = '';
        $str_birth_date = '';
        $str_birth_place = '';
        $str_baptism_date = '';
        $str_baptism_place = '';
        $str_death_date = '';
        $str_death_place = '';
        $str_death_cause = '';
        $str_buried_date = '';
        $str_buried_place = '';
        $str_fam_spouse = '';
        $str_fam_child = '';
        
        $bln_reached_individuals = true;
        
    }
    
    if(strstr($key, 'FAM')) {
        $fam_id = str_replace('0 @','',$key);
        $fam_id = str_replace('@ FAM','',$fam_id);
        $fam_id = str_replace("\r",'',$fam_id);
      
        $arr_families[$fam_id] = $res_line;
        $str_husband = '';
        $str_wife = '';
        $str_child_id = '';
        $str_marriage_date = '';
        $str_marriage_place = '';
        $bln_line_is_marriage_date = false;
        
        foreach($res_line as $fam_part) {
            if(strstr($fam_part, 'HUSB') && !strstr($fam_part, 'QUAYHUSB')) {
                $str_husband = str_replace('1 HUSB ','',$fam_part);
                $str_husband = str_replace('@','',$str_husband);   
                $str_husband = str_replace("\r\n",'',$str_husband);
       
            }
            
     // todo       //[9] => 1 CHIL @I18@
            //[10] => 2 _QUAYHUSB 1    (0,1,2,3)
            
            if(strstr($fam_part, 'WIFE')) {
                $str_wife = str_replace('1 WIFE ','',$fam_part);
                $str_wife = str_replace('@','',$str_wife);   
                $str_wife = str_replace("\r\n",'',$str_wife);
        
            }
            if(strstr($fam_part, 'CHIL')) {
                $str_child_id = $str_child_id . (!empty($str_child_id) ? ',' : '') . str_replace('1 CHIL ','',str_replace('@','',$fam_part));
            
                $str_child_id = str_replace("\r",'',$str_child_id);
                        
            }
            if(strstr($fam_part, 'MARR')) {
                $bln_line_is_marriage_date = true;
                continue;
            }
            if($bln_line_is_marriage_date && strstr($fam_part, '2 DATE')) {
                $str_marriage_date = str_replace('2 DATE ','',$fam_part);
            }
            if($bln_line_is_marriage_date && strstr($fam_part, '2 PLAC')) {
                $str_marriage_place = str_replace('2 PLAC ','',$fam_part);
                $bln_line_is_marriage_date = false;
            }
//   todo         if(strstr($ind_part, '1 NOTE')) {
//                // example: 1 NOTE @N34@
//                $str_note_id = str_replace('1 NOTE @','',$ind_part);
//                $str_note_id = str_replace('@','',$str_note_id);
//                $str_note_id = str_replace("\r",'',$str_note_id);
//            }
            
        }
        $obj_db->query('INSERT INTO `families` SET `husband` = "'.trim($str_husband).'", `wife` = "'.trim($str_wife).'", '.
                '`gedcom_file_id` = "'.$gedcom_file_id.'", `gedcom_family_id` = "'.trim($fam_id).'", '.
                '`marriage_date` = "'.trim($str_marriage_date).'", `marriage_place` = "'.trim($str_marriage_place).'"'.
                ', `children` = "'.$str_child_id.'"');
        
        $bln_reached_families = true;
    }
}

print_r($arr_individuals);

// stamoudste opgeven



//$arr_families = array();
//foreach($arr_result as $key => $res_line) {
//    if(strstr($key, 'FAM')) {
//        $fam_id = str_replace('0 @','',$key);
//        $fam_id = str_replace('@ FAM','',$fam_id);
//        
//        $arr_families[$fam_id] = $res_line;
//    }
//}

print_r($arr_families);

// find first ancestor


// find children


// find husband / wife

