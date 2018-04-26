<?php
    include ('./jdf.php');
    // require_once('jdf.php');
    if ($_GET['key']){
	    $key = str_replace(" ", "+", $_GET['key']);
	    $key_data = explode("d", base64_decode($key));
	    $key_db = $wpdb->get_results( 'SELECT * FROM '.$wpdb->prefix.'sanop_licence WHERE school_code = '.$key_data[0] .' and class_code='. $key_data[4] .' and level= '.$key_data[2].' and academic_year = ' .$key_data[3], OBJECT );
	    if (count($key_db)){
    	    list($date, $time) = explode(' ', $key_db[0]->payment_time);
            list($year, $month, $day) = explode('-', $date);
            list($hour, $minute, $second) = explode(':', $time);
            $timestamp = mktime($hour, $minute, $second, $month, $day, $year);
			echo  active_code($key);
	    }else{
			echo 0;
		}
        
	}else{
		echo 0;
	}
	
?>