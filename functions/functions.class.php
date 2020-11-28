<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Functions
 *
 * @author macpaul
 */
class Functions {

    public static function getFirstMaleAncestor() {
        // walk through individuals and find first male ancestor
    }

    public static function getFiles() {
        global $obj_db;
        $arr_files = array();

        $str_query = 'SELECT * FROM `gedcom_files`';

        $obj_result = mysqli_query($obj_db, $str_query);

        while ($arr_line = mysqli_fetch_array($obj_result, MYSQLI_ASSOC)) {
            $arr_files[] = $arr_line;
        }

        return $arr_files;
    }

    public static function getIndividual($indiv_id = -1, $str_gedcom_file_id = null) {
        global $obj_db;

        $str_query = 'SELECT * FROM `individuals` i WHERE `gedcom_file_id` = "' . $str_gedcom_file_id . '" AND `gedcom_individual_id` = "' . $indiv_id . '"';

        $obj_result = mysqli_query($obj_db, $str_query);

        $arr_individual = mysqli_fetch_array($obj_result, MYSQLI_ASSOC);

        return $arr_individual;
    }

    public static function searchSpouse($family = '', $arr_indiv = array(), $left, $arr_colors, $file_id, $int_multiple = -1) {
        global $obj_db;

        // search family
        $str_query_family = 'SELECT * FROM `families` f WHERE `gedcom_file_id` = ' . $file_id . ' AND `gedcom_family_id` = "' . $family . '"';

        $obj_result_family = mysqli_query($obj_db, $str_query_family);

        $arr_family = mysqli_fetch_array($obj_result_family, MYSQLI_ASSOC);

        if ($arr_indiv['gender'] == 'M' && !empty($arr_family['wife'])) {
            // find the wife
            $str_query_wife = 'SELECT * FROM `individuals` i WHERE `gedcom_file_id` = ' . $file_id . ' AND `gedcom_individual_id` = "' . $arr_family['wife'] . '"';

            $obj_result_wife = mysqli_query($obj_db, $str_query_wife);

            $arr_wife = mysqli_fetch_array($obj_result_wife, MYSQLI_ASSOC);

            if (!is_null($arr_wife)) {
                $arr_result['wife'] = $arr_wife;

                $str_dates2 = '';

                if (!empty($arr_wife['birth_date'])) {
                    $result_birth_date2 = array();
                    preg_match('/[0-9]{4}/', $arr_wife['birth_date'], $result_birth_date2);

                    $str_dates2 = $result_birth_date2[0];
                }
                $str_dates2 .= '-';

                if (!empty($arr_wife['death_date'])) {
                    $result_death_date2 = array();
                    preg_match('/[0-9]{4}/', $arr_wife['death_date'], $result_death_date2);

                    $str_dates2 .= $result_death_date2[0];
                }


                if (!empty($arr_family['marriage_date'])) {
                    echo '<span style="padding-left:' . $left . 'px;">&#9901; ' . $arr_family['marriage_date'] . '</span><br />';
                } else {
                    echo '<span style="padding-left:' . $left . 'px;">-</span><br />';
                }




                echo '<span style="padding-left:' . $left . 'px;">' . ($int_multiple > 0 ? '<span style="text-decoration:underline;">' . $int_multiple . '</span> ' : '');

                echo '<span class="mouseover" style="">' . str_replace('/', ' ', utf8_decode($arr_wife['name'])) . ' (' . $arr_wife['gedcom_individual_id'] . ')<br />* ' .
                $arr_wife['birth_date'] . ' ' . $arr_wife['birth_place'] . '<br />' .
                (!empty($arr_wife['death_date']) ? '† ' . $arr_wife['death_date'] . ' ' . $arr_wife['death_place'] : '') . '<br />' .
                '<br />' . (!empty($arr_wife['occupation']) ? $arr_wife['occupation'] : '') .
                '</span>';

                echo '<span class="tooltiptext" style="cursor:help;">' . ($left == 0 && !empty($arr_wife['child_of_family_id']) ? '<a href="' . CURRENT_URL . '/gedcom_viewer/?c=' . $arr_wife['gedcom_individual_id'] . '"><< </a>' : '') . str_replace('/', ' ', utf8_decode($arr_wife['name'])) . $str_dates2 . '</span></span><br />';
            } else {
                echo '<span style="padding-left:' . $left . 'px;">' . ($int_multiple > 0 ? $int_multiple . ' ' : '') . '?</span><br />';
            }
        } else if ($arr_indiv['gender'] == 'F') {
            // find the husband
            $str_query_husband = 'SELECT * FROM `individuals` i WHERE `gedcom_file_id` = ' . $file_id . ' AND `gedcom_individual_id` = "' . $arr_family['husband'] . '"';

            $obj_result_husband = mysqli_query($obj_db, $str_query_husband);

            $arr_husband = mysqli_fetch_array($obj_result_husband, MYSQLI_ASSOC);

            if (!is_null($arr_husband)) {
                $arr_result['husband'] = $arr_husband;

                $str_dates3 = '';

                if (!empty($arr_husband['birth_date'])) {
                    $result_birth_date3 = array();
                    preg_match('/[0-9]{4}/', $arr_husband['birth_date'], $result_birth_date3);

                    $str_dates3 = $result_birth_date3[0];
                }
                $str_dates3 .= '-';

                if (!empty($arr_husband['death_date'])) {
                    $result_death_date3 = array();
                    preg_match('/[0-9]{4}/', $arr_husband['death_date'], $result_death_date3);

                    $str_dates3 .= $result_death_date3[0];
                }

                if (!empty($arr_family['marriage_date'])) {
                    echo '<span style="padding-left:' . $left . 'px;">&#9901; ' . $arr_family['marriage_date'] . '</span><br />';
                } else {
                    echo '<span style="padding-left:' . $left . 'px;">-</span><br />';
                }



                echo '<span style="padding-left:' . $left . 'px;">' . ($int_multiple > 0 ? '<span style="text-decoration:underline;">' . $int_multiple . '</span> ' : '');

                echo '<span class="mouseover" style="">' . str_replace('/', ' ', $arr_husband['name']) . ' (' . $arr_husband['gedcom_individual_id'] . ')<br />* ' .
                $arr_husband['birth_date'] . ' ' . $arr_husband['birth_place'] . '<br />' .
                (!empty($arr_husband['death_date']) ? '† ' . $arr_husband['death_date'] . ' ' . $arr_husband['death_place'] : '') . '<br />' .
                '<br />' . (!empty($arr_husband['occupation']) ? $arr_husband['occupation'] : '') .
                '</span>';

                echo '<span class="tooltiptext" style="cursor:help;">' . ($left == 0 && !empty($arr_husband['child_of_family_id']) ? '<a href="' . CURRENT_URL . '/gedcom_viewer/?c=' . $arr_husband['gedcom_individual_id'] . '"><< </a>' : '') . str_replace('/', ' ', utf8_decode($arr_husband['name'])) . $str_dates3 . '</span></span><br />';
            } else {
                echo '<span style="padding-left:' . $left . 'px;">' . ($int_multiple > 0 ? $int_multiple . ' ' : '') . '?</span><br />';
            }
//            else if (!empty($arr_family['children'])) {
//
//                if (!strstr($arr_family['children'], ',')) {
//                    // find the child
//                    $str_query_child = 'SELECT * FROM `individuals` i WHERE `gedcom_individual_id` = "' . $arr_family['children'] . '"';
//
//                    $obj_result_child = mysqli_query($obj_db, $str_query_child);
//
//                    $arr_child = mysqli_fetch_array($obj_result_child, MYSQLI_ASSOC);
//
//                    $left += 100;
//                    echo '<span style="color:' . $arr_colors[round($left / 100)] . ';padding-left:' . $left . 'px;">' . $arr_child['name'] . ' (vader niet bekend)<br /><br />';
//                    $left -= 100;
//                } else {
//                    // todo
//                    echo $arr_family['children'];
//                }
//            }
        } else {
            
        }
    }

    public static function hasChildren($indiv_id = -1, $left = 0, $file_id, &$arr_top_divs = array(), &$arr_indivs_in_current_tree = array()) {
        global $obj_db;

//        if(count($arr_top_divs) > AMOUNT_GENERATIONS) {
//            // do nothing
//            return array();
//        } else {

        $arr_indivs_in_current_tree[] = $indiv_id;

        $arr_colors = array('#000000', '#316fb1', '#28b463', '#e59866', '#c9c744', '#2e86c1', '#dbab23', '#991188', '#4c8b79', '#ff6700', '#81af3b', '#ad074a', '#f69191', '#18b6fd');

        $str_query_fam = 'SELECT * FROM `families` f WHERE `gedcom_file_id` = ' . $file_id . ' AND (`husband` = "' . $indiv_id . '" OR `wife` = "' . $indiv_id . '")';
//echo $str_query_fam;
        $obj_result_fam = mysqli_query($obj_db, $str_query_fam);

        $arr_fam = mysqli_fetch_array($obj_result_fam, MYSQLI_ASSOC);

//var_dump($arr_fam);
        $arr_result = array();

        $arr_result['family'] = $arr_fam;

        // the parent
        $str_query_indiv = 'SELECT * FROM `individuals` i ' .
                ' LEFT JOIN `notes` n ON(n.gedcom_note_id = i.gedcom_note_id) ' .
                ' WHERE i.`gedcom_file_id` = ' . $file_id . ' AND i.`gedcom_individual_id` = "' . $indiv_id . '"';
//echo $str_query_indiv;
        $obj_result_indiv = mysqli_query($obj_db, $str_query_indiv);

        $arr_indiv = mysqli_fetch_array($obj_result_indiv, MYSQLI_ASSOC);
//var_dump($arr_indiv);

        $str_dates = '';

        if (!empty($arr_indiv['birth_date'])) {
            $result_birth_date = array();
            preg_match('/[0-9]{4}/', $arr_indiv['birth_date'], $result_birth_date);

            $str_dates = $result_birth_date[0];
        }
        $str_dates .= '-';

        if (!empty($arr_indiv['death_date'])) {
            $result_death_date = array();
            preg_match('/[0-9]{4}/', $arr_indiv['death_date'], $result_death_date);

            $str_dates .= $result_death_date[0];
        }

//        // zoeken in families (FIND_IN_SET)
//        $str_query = 'SELECT * FROM `families` f
//                     WHERE FIND_IN_SET("'.$str_child_id.'", `children`)';
//        // had ook via famc gekund
//
//        $obj_result = mysqli_query($obj_db, $str_query);
//
//        $arr_line = mysqli_fetch_array($obj_result, MYSQLI_ASSOC);

        echo '<div style="color:' . $arr_colors[round($left / 100)] . ';">';

        echo '<div style="padding-left:' . $left . 'px;">';
        echo '<span class="mouseover" style="">' . str_replace('/', ' ', utf8_decode($arr_indiv['name'])) . ' (' . $arr_indiv['gedcom_individual_id'] . ')<br />* ' .
        $arr_indiv['birth_date'] . ' ' . $arr_indiv['birth_place'] . '<br />' .
        (!empty($arr_indiv['death_date']) ? '† ' . $arr_indiv['death_date'] . ' ' . $arr_indiv['death_place'] : '') . '<br />' .
        (!empty($arr_indiv['occupation']) ? '<br />' . $arr_indiv['occupation'] : '') .
        (!empty($arr_indiv['note']) ? '<br />' . $arr_indiv['note'] : '') .
        '</span>';
        //echo '<span class="tooltiptext" >' . str_replace('/',' ',$arr_indiv['name']).'</span>' . (!empty($arr_indiv['nickname']) ? ' (' . $arr_indiv['nickname'] . ') ' : '') . '<span style="color:lightgray;">(* ' . $arr_indiv['birth_date'] . ') ' . (!empty($arr_indiv['death_date']) ? ' († ' . $arr_indiv['death_date'] . ')' : '') . '</span></div>';
        echo '<span class="tooltiptext" style="cursor:help;">' . ($left == 0 && !empty($arr_indiv['child_of_family_id']) ? '<a href="' . CURRENT_URL . '/gedcom_viewer/?c=' . $arr_indiv['gedcom_individual_id'] . '"><< </a>' : '') . str_replace('/', ' ', utf8_decode($arr_indiv['name'])) . (!empty($arr_indiv['nickname']) ? ' (' . utf8_decode($arr_indiv['nickname']) . ') ' : '') . $str_dates . '</span>';

        // arrow to go to children
        if (round($left / 100) + 1 == AMOUNT_GENERATIONS) {
            echo ' <a href="' . CURRENT_URL . '/gedcom_viewer/?i=' . $arr_indiv['gedcom_individual_id'] . '"> >></a>';
        }

        echo '</div>';

        if (!isset($arr_top_divs)) {
            $arr_top_divs = array();
        }
        if (!isset($arr_top_divs[$left])) {
            $arr_top_divs[$left] = '';
        }
        if (empty($arr_top_divs[$left]) && !empty($arr_indiv['birth_date'])) {
            $arr_top_divs[$left] = $arr_indiv['birth_date'];
        }


        if (!empty($arr_indiv['spouse_of_family_id'])) {
//            if($arr_indiv['gender'] == 'M') {
//                $arr_result['husband'] = $arr_indiv;
//            } else if($arr_indiv['gender'] == 'F') {
//                $arr_result['wife'] = $arr_indiv;
//            } else {
            $arr_result['indiv'] = $arr_indiv;
            //           }
            //    var_dump($arr_indiv['spouse_of_family_id']);
            if (strstr($arr_indiv['spouse_of_family_id'], ',')) {
                // married more than 1 time

                $arr_families = explode(',', $arr_indiv['spouse_of_family_id']);

                foreach ($arr_families as $key => $family) {

                    self::searchSpouse($family, $arr_indiv, $left, $arr_colors, $file_id, $key + 1);


                    // if ($key + 1 !== count($arr_families)) {
                    echo '<br />';
                    // }

                    $arr_children_with_birthyear = array();

                    $str_query_fam = 'SELECT * FROM `families` f WHERE `gedcom_file_id` = ' . $file_id . ' AND `gedcom_family_id` = "' . $family . '"';
//echo $str_query_fam;
                    $obj_result_fam = mysqli_query($obj_db, $str_query_fam);

                    $arr_fam = mysqli_fetch_array($obj_result_fam, MYSQLI_ASSOC);

                    if (round($left / 100) + 1 == AMOUNT_GENERATIONS) {
                        $arr_fam['children'] = '';
                    }

                    if (!empty($arr_fam['children'])) {
                        $arr_children = explode(',', $arr_fam['children']);
                        $int_no_replaced_birthdate = 1;
                        foreach ($arr_children as $child) {
                            $arr_child = self::getIndividual($child, $file_id);

                            if (!empty($arr_child['birth_date'])) {
                                $results = array();
                                preg_match('/[0-9]{4}/', $arr_child['birth_date'], $results);

                                $arr_children_with_birthyear[$results[0]] = $child;
                            } else {
                                $arr_children_with_birthyear[$int_no_replaced_birthdate] = $child;
                                $int_no_replaced_birthdate ++;
                            }
                        }

                        ksort($arr_children_with_birthyear);

                        $arr_children = $arr_children_with_birthyear;

                        foreach ($arr_children as $child_id) {

                            // $str_indiv_name = $arr_indiv['name'];
                            // the child
                            $str_query_child = 'SELECT * FROM `individuals` i WHERE `gedcom_file_id` = ' . $file_id . ' AND `gedcom_individual_id` = "' . $child_id . '"';

                            $obj_result_child = mysqli_query($obj_db, $str_query_child);

                            $arr_child = mysqli_fetch_array($obj_result_child, MYSQLI_ASSOC);
                            $str_name = $arr_child['name'];
                            $str_name = str_replace("\r", '', $str_name);


                            $child_id = str_replace("\r", '', $child_id);


                            $left += 100;



                            $arr_result['children'][$child_id . '-' . $str_name][] = self::hasChildren($child_id, $left, $file_id, $arr_top_divs, $arr_indivs_in_current_tree);
                            echo '</div>';
                            $left -= 100;
                        }
                    }
                }
            } else {

                self::searchSpouse($arr_indiv['spouse_of_family_id'], $arr_indiv, $left, $arr_colors, $file_id);

                if (round($left / 100) + 1 == AMOUNT_GENERATIONS) {
                    $arr_fam['children'] = '';
                }

                if (!empty($arr_fam['children'])) {
                    echo '<br />';

                    $arr_children = explode(',', $arr_fam['children']);
                    $int_no_replaced_birthdate = 1;
                    foreach ($arr_children as $child) {
                        $arr_child = self::getIndividual($child, $file_id);

                        if (!empty($arr_child['birth_date'])) {
                            $results = array();
                            preg_match('/[0-9]{4}/', $arr_child['birth_date'], $results);

                            $arr_children_with_birthyear[$results[0]] = $child;
                        } else {
                            $arr_children_with_birthyear[$int_no_replaced_birthdate] = $child;
                            $int_no_replaced_birthdate ++;
                        }
                    }

                    ksort($arr_children_with_birthyear);

                    $arr_children = $arr_children_with_birthyear;

                    foreach ($arr_children as $child_id) {

                        // $str_indiv_name = $arr_indiv['name'];
                        // the child
                        $str_query_child = 'SELECT * FROM `individuals` i WHERE `gedcom_file_id` = ' . $file_id . ' AND `gedcom_individual_id` = "' . $child_id . '"';

                        $obj_result_child = mysqli_query($obj_db, $str_query_child);

                        $arr_child = mysqli_fetch_array($obj_result_child, MYSQLI_ASSOC);
                        $str_name = $arr_child['name'];
                        $str_name = str_replace("\r", '', $str_name);


                        $child_id = str_replace("\r", '', $child_id);
                        echo '</div>';

                        $left += 100;


                        $arr_result['children'][$child_id . '-' . $str_name][] = self::hasChildren($child_id, $left, $file_id, $arr_top_divs, $arr_indivs_in_current_tree);

                        $left -= 100;
                    }
                }
            }

            //echo '</div>';
        } else {
            $arr_result['indiv'] = $arr_indiv;
        }

        echo '<br />';

        if (strstr($arr_indiv['spouse_of_family_id'], ',')) {
            foreach ($arr_families as $fam_id) {
                
            }
        } else {
            
        }




        return $arr_result;
    }

    public static function getFamilyWithChildren() {
        
    }

    public static function getAllIndividualsOfFile($int_file_id = -1, $bln_complete = false) {
        global $obj_db;
        $str_query = 'SELECT * FROM `individuals` i WHERE `gedcom_file_id` = "' . $int_file_id . '"';

        $obj_result = mysqli_query($obj_db, $str_query);

        while ($arr_indiv = mysqli_fetch_array($obj_result, MYSQLI_ASSOC)) {
            if ($bln_complete) {
                $arr_result[] = $arr_indiv;
            } else {
                $arr_result[] = $arr_indiv['name'];
            }
        }
        return $arr_result;
    }

    public static function getIndividualsNotInCurrentTree($int_file_id = -1, $arr_indivs_in_current_tree) {
        global $obj_db;
        $str_query = 'SELECT * FROM `individuals` i WHERE `gedcom_file_id` = ' . $int_file_id . ' AND `gedcom_individual_id` NOT IN("' . implode('","', $arr_indivs_in_current_tree) . '") ';

        $obj_result = mysqli_query($obj_db, $str_query);

        $arr_result = array();
        while ($arr_indiv = mysqli_fetch_array($obj_result, MYSQLI_ASSOC)) {
            $year = null;
            if (!empty($arr_indiv['birth_date'])) {
                $results = array();
                preg_match('/[0-9]{4}/', $arr_indiv['birth_date'], $results);
                $year = $results[0];
            }
            $str_name = $arr_indiv['name'];
            if (substr($str_name, 0, 1) == '/') {
                $str_name = substr($str_name, 1);
            }
            $arr_result[] = array('gedcom_individual_id' => $arr_indiv['gedcom_individual_id'], 'name' => trim(str_replace('/', ' ', $str_name)),
                'birth_year' => $year);
        }

        $arr_result = Functions::sortTwodimArrayByKey($arr_result, 'name');
        return $arr_result;
    }

    public static function calculateColumns($arr_categories, $cnt_columns) {

		$cnt_items = count($arr_categories);

		foreach($arr_categories as $key => &$cat) {
  	    	$cat['cnt_questions']	= rand(1,20);

			// misschien ook nog een if($cnt_columns == 1)
			if($cnt_columns == 2) {
				if($key == floor(($cnt_items - 1)/2) OR $key == ($cnt_items - 1)) {
					$cat['column_close'] = true;
				}
			}
			if($cnt_columns == 3) {
				if($key == floor(($cnt_items - 1)/3) OR $key == ($cnt_items - 1) - floor($cnt_items/3) OR $key == ($cnt_items - 1)) {
					$cat['column_close'] = true;
				}
			}
			// key van de op 1 na laatste kolom: key_laatste_kolom - (cnt_items:cnt_columns (hele getal))
			// key van de eerste kolom: (cnt_items-1):cnt_columns (hele getal)
			// key van tweede kolom is wat lastiger, kijk maar hieronder bij 4 kolommen :o)
			if($cnt_columns == 4) {
				$rest = $cnt_items % 4;
				$sec_key = ($rest > 1) ? floor(($cnt_items - 1)/4)+floor($cnt_items/4)+1 : floor(($cnt_items - 1)/4)+floor($cnt_items/4);
				if($key == floor(($cnt_items - 1)/4) OR $key == $sec_key OR $key == ($cnt_items - 1) - floor($cnt_items/4) OR $key == ($cnt_items - 1)) {
					$cat['column_close'] = true;
				}
			}
//			if($arr_display_options['faq_vragen_positie'] == 'tussen_cats') {
//				$cat['questions'] = $this->getQuestions($cat['cat_id']);
//			}
		}
		return $arr_categories;
	}
        
    public static function sortTwodimArrayByKey($two_dim_array, $key_to_sort_with, $dir = 'ASC', $case_sensitive = false) {

        if (!empty($two_dim_array)) {

            $arr_result = array();
            $arr_values = array();
            $bln_third_dim = false;

            if (strstr($key_to_sort_with, '/')) {
                $arr_dims = explode('/', $key_to_sort_with);
                $sec_dim_key = $arr_dims[0];
                $third_dim_key = $arr_dims[1];
                $bln_third_dim = true;
            }

            // array maken met de waardes waarop gesorteerd moet worden
            foreach ($two_dim_array as $arr_second_dim) {
                if (is_array($arr_second_dim[$key_to_sort_with])) {
                    echo 'opgegeven key is een array. Gebruik key1/key2';
                    break;
                }
                $arr_values[] = $bln_third_dim ? $arr_second_dim[$sec_dim_key][$third_dim_key] : $arr_second_dim[$key_to_sort_with];
            }

            // sorteren ( de key's krijgen de nieuwe volgorde )
            if ($case_sensitive) {
                sort($arr_values);
            } else {
                natcasesort($arr_values);
            }

            // nieuwe array maken met de juiste volgorde
            foreach ($arr_values as $value) {
                foreach ($two_dim_array as $key => $val) {
                    if ($value == ($bln_third_dim ? $val[$sec_dim_key][$third_dim_key] : $val[$key_to_sort_with])) {
                        $arr_result[] = $two_dim_array[$key];
                        unset($two_dim_array[$key]);
                    }
                }
            }

            if ($dir == 'DESC') {
                $arr_result = array_reverse($arr_result);
            }

            return $arr_result;
        } else {
            return array();
        }
    }

}
