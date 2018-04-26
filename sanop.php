<?php
/**
 * @package Sanop
 * @version 1.0
 */
/*
Plugin Name: Sanop
Plugin URI: https://wordpress.org/plugins/hello-dolly/
Description: a custom plugin for sanop for sale software. develop by hooraweb.
Author: hooraweb
Version: 1.0
Author URI: http://hooraweb.com
Text Domain: Sanop
*/

function sanop_licence( $atts ){
    global $wp;
    global $wpdb;
    $MerchantID = 'f4bbf982-e310-11e7-b3d6-000c295eb8fc';
    $PayAmount = 10000;
    $payed = false;
    //echo 'getttttttttttt:';
	//print_r($_GET);
	//echo '<br/><br/>posttttttttttttt';
	//print_r($_POST);
	//echo '<br/>';
	//echo home_url( $wp->request );
	if ($_GET['key']){
	    $key = str_replace(" ", "+", $_GET['key']);
	    $key_data = explode("d", base64_decode($key));
	    $key_db = $wpdb->get_results( 'SELECT * FROM '.$wpdb->prefix.'sanop_licence WHERE school_code = '.$key_data[0] .' and class_code='. $key_data[4] .' and level= '.$key_data[2].' and academic_year = ' .$key_data[3], OBJECT );
	}

	if ( $_GET['key'] && $_GET['agree'] == 'on' ):
	    if (count($key_db)){
			echo 'این تراکنش قبلا پرداخت شده';
		}else{
		    //echo'ffffffffffff';
			$client = new SoapClient('https://www.zarinpal.com/pg/services/WebGate/wsdl', ['encoding' => 'UTF-8']);
			$result = $client->PaymentRequest(
				[
					'MerchantID' => $MerchantID,
					'Amount' => $PayAmount,
					'Description' => '' . $key_data[1] . ' - ' . $key_data[4] . ' - ' . $key_data[3],
					'Email' => '',
					'Mobile' => '',
					'CallbackURL' => home_url( $wp->request ).'?key='.$_GET['key'],
				]
			);
			if ($result->Status == 100) {
				Header('Location: https://www.zarinpal.com/pg/StartPay/'.$result->Authority.'/Bpm');
			} else {
				echo'ERR: '.$result->Status;
			}
		}
	elseif ($_GET['key']):
	    if ( $_GET['Authority']  ){
            if ($_GET['Status'] == 'OK') {
                $client = new SoapClient('https://www.zarinpal.com/pg/services/WebGate/wsdl', ['encoding' => 'UTF-8']);
                $result = $client->PaymentVerification(
                    [
                        'MerchantID' => $MerchantID,
                        'Authority' => $_GET['Authority'],
                        'Amount' => $PayAmount,
                    ]
                );
                if ($result->Status == 100) {
                    //echo 'Transaction success. RefID:'.$result->RefID;
                    echo '<p class="title green-text">پرداخت شما با موفقیت انجام شد</p>';
                    $wpdb->insert( 
                    	'wp_sanop_licence', 
                    	array(
                    		'school_code' => $key_data[0],
                    		'class_code' => $key_data[4],
                    		'level' => $key_data[2],
                    		'academic_year' => $key_data[3],
                    		'software_key' => $key,
                    		'software_licence' => active_code($key),
                    		'payment_time' => date ("Y-m-d H:i:s"),
                    		'payment_code' => $result->RefID,
                    	)
                    ); $payed = true;
                } else {
                    echo 'Transaction failed. Status:'.$result->Status;
                }
            } else {
                echo 'Transaction canceled by user';
            }
        }else{
    	    if (count($key_db)): ?>
                <p class="container small">همراه گرامی؛ شما پیشتر نسبت به خرید کد فعالسازی نرم‌افزار تکمیل فرم الف گزارش پیشرفت تحصیلی – تربیتی اقدام نموده اید.
                اطلاعات و همچنین کد فعالسازی شما در زیر آمده است</p>
            <?php else: ?>
                <p class="container small">همراه گرامی؛ شما به صفحه خرید کد فعالسازی نرم‌افزار تکمیل فرم الف گزارش پیشرفت تحصیلی – تربیتی هدایت شده اید.
                در صورت تایید اطلاعات زیر، لطفا با کلیک بر روری دکمه پرداخت نسبت به خریداری کد فعالسازی اقدام  نمایید.
    	        </p>    
            <?php endif;
        } ?>
	    <br/><br/>
	    <div class="caption"><?php
	    //echo str_replace(" ","+",$key);
	      ?></div>
	    
	    <table class="bordered body-1 container small">
            <tbody>
              <tr>
                <th>کد آموزشگاه</th>
                <td><?= $key_data[0] ?></td>
              </tr>
              <tr>
                <th>نام آموزشگاه</th>
                <td><?= $key_data[1] ?></td>
              </tr>
              <tr>
                <th>سال تحصیلی (نوبت اول،دوم و تابستان)</th>
                <td><?= $key_data[3] ?></td>
              </tr>
              <tr>
                <th>پایه تحصیلی</th>
                <td><?php switch($key_data[2]){
                    case '1': echo 'اول'; break;
                    case '2': echo 'دوم'; break;
                    case '4': echo 'سوم'; break;
                    case '8': echo 'چهارم'; break;
                    case '16': echo 'پنجم'; break;
                    case '32': echo 'ششم'; break;
                } ?></td>
              </tr>
              <tr>
                <th>کد کلاس</th>
                <td><?= $key_data[4] ?></td>
              </tr>
              <tr>
                  <th>وضعیت</th>
                  <td>
                  <?php if (count($key_db)): ?>
                    <span class="green-text">پرداخت شده</span>
                  <?php else: ?>
                    <span class="red-text">پرداخت نشده</span>
                  <?php endif; ?>
                  </td>
              </tr>
            </tbody>
        </table>

		<?php if (count($key_db) || $payed): ?>
    		<br/><br/><br/>
		    <div class="center headline">
    			کد فعال سازی<br/>
    			<span class="green-text"><?php echo  active_code($key);?></span><br><br>
    			<a class="waves-effect waves-light btn" href="/factor?key=<?= $key ?>">مشاهده فاکتور</a>
			</div><br/><br/>
		<?php else: ?>
		    <?php if (intval($key_data[0]) > 0 && intval($key_data[2]) > 0 && intval($key_data[3]) > 0 && intval($key_data[4]) > 0): ?>    
    		    <div class="center headline">
    		        <br/><br/><br/>
        			پرداخت و دریافت کد فعالسازی<br/>
        			<span class="green-text">قبل از خرید دقت کنید نسخه نرم افزار ۱.۴۳ باشد</span><br/>
        			<span class="green-text">۱۰٫۰۰۰ تومان</span>
    			</div><br/>
    			<form class="center-align" action="./" method="get">
    			    <p style="text-align: center;">
                        <input type="checkbox" id="test5" name="agree" />
                        <label for="test5">تمام اطلاعات صحیح بوده و قبول دارم</label>
                    </p>
    			    <input type="hidden" name="payn" value="1" />
    				<input type="hidden" name="key" value="<?=$key?>" />
    				<input type="submit" value="پرداخت">
    			</form><br/><br/>
    		<?php else: ?>
    		    <p class="center-align headline red-text">کد ارسالی شما اشتباه است </p>
    		    <p class="center-align red-text">برای رفع مشکل از نرم افزار به روز فرم الف استفاده کنید<br/><br/>
    		    <a class="waves-effect waves-light btn" href="/form-alef">دریافت نرم افزار</a>
    		    </p>
    		<?php endif; ?>
		<?php endif;
	else:?>
	    <br/><br/>
	    <p class="title cneter-align">برای دریافت کد فعال سازی ابتدا کلید نرم افزار خود را وارد کنید</p>
		<form class="center-align" action="./" method="get">
		    <div class="input-field">
                <input id="key" type="text" name="key" class="validate">
            </div>
			<input type="submit" value="بررسی">
		</form>
	<?php endif;
}
add_shortcode( 'sanop_licence', 'sanop_licence' );

register_activation_hook( __FILE__, 'my_plugin_create_db' );
function my_plugin_create_db() {
	global $wpdb;
	$charset_collate = $wpdb->get_charset_collate();
	$table_name = $wpdb->prefix . 'sanop_licence';

	$sql = "CREATE TABLE $table_name (
		id int NOT NULL AUTO_INCREMENT,
		school_code int NOT NULL,
		class_code int NOT NULL,
		level int NULL,
		academic_year smallint NOT NULL,
		software_key text NOT NULL,
		software_licence text NOT NULL,
		payment_time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		payment_code int NOT NULL,
		UNIQUE KEY id (id)
	) $charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );
}
// create custom plugin settings menu
add_action('admin_menu', 'my_cool_plugin_create_menu');

function my_cool_plugin_create_menu() {

	//create new top-level menu
	add_menu_page('My Cool Plugin Settings', 'پرداختی ها', 'editor', __FILE__, 'my_cool_plugin_settings_page' , plugins_url('/images/icon.png', __FILE__) );

}
function my_cool_plugin_settings_page() {
    global $wpdb;
    $pays = $wpdb->get_results( 'SELECT * FROM '.$wpdb->prefix.'sanop_licence ORDER BY payment_time DESC LIMIT 500', OBJECT );
    // $level = $wpdb->get_results( 'SELECT * FROM '.$wpdb->prefix.'sanop_licence WHERE level IS NULL LIMIT 200', OBJECT );
    // echo'<br/><br/><br/>';
    // foreach($level as $id=>$l){
    //     $level_data = explode("d", base64_decode($l->software_key));
    //     echo '<br/>' . $l->id;
    //     $wpdb->update( 
    //     	$wpdb->prefix.'sanop_licence', 
    //     	array( 
    //     		'level' => $level_data[2]
    //     	), 
    //     	array( 'id' => $l->id ), 
    //     	array(
    //     		'%d'
    //     	), 
    //     	array( '%d' ) 
    //     );
    // }
    ?>
    <div class="wrap">
        <br/><br/>
        <table class="wp-list-table widefat fixed striped posts">
            <tr>
                <th>آموزشگاه</th>
                <th>کلاس</th>
                <th>کاربر</th>
                <th>پرداخت</th>
                <th>کد فعال سازی</th>
            </tr>
            <?php foreach($pays as $index => $pay): $key_data=explode("d", base64_decode($pay->software_key)); ?>
            <tr>
                <td>
                    (<?= $pay->id ?>) کد: <?= $pay->school_code ?><br/>
                    نام: <?= $key_data[1] ?>
                </td>
                <td>
                    سال تحصیلی: <?= $key_data[2] ?> - 
                    کد پایه: <?= $key_data[2] ?> <br/>
                    کد کلاس: <?= $pay->class_code ?> (<?= $key_data[6] ?> نفر)
                    
                </td>
                <td>
                    نام دبیر: <?=  $key_data[5] ?><br/>
                    نام مدیر: <?=  $key_data[7] ?>
                </td>
                <td>
                    تاریخ: <?=  date( 'Y-m-d h:m', strtotime( $pay->payment_time))?><br/>
                    کد پرداخت: <?=  $pay->payment_code ?>
                </td>
                <td>
                    <?= $pay->software_licence ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
    <?php
}
function active_code($key) {
    $key_data = explode("d", base64_decode($key));
    $schoolcode = 10000 * $key_data[0];
    $classcode = 10000 * $key_data[4];
    $basecode = 100 * $key_data[3];
    return dechex(
       ( (10000 * $key_data[0]) + (10000 * $key_data[4]) + (100 * $key_data[2]) + $key_data[3] ) / 0.66964817
    );
    
    // $key_data = explode("d", base64_decode($key));
    // $schoolcode = hash_digit($key_data[0]);
    // $classcode = hash_digit($key_data[4]);
    // $yearcode = hash_digit($key_data[2]);
    // return dechex($schoolcode + $classcode + $yearcode);
}
function hash_digit ($digit) {
    $digit = bcdiv($digit, '0.66964817', 13);
    list($whole, $digit) = sscanf( $digit, '%d.%d');
	$digit = round ( '0.'.$digit, 12);
	list($whole, $digit) = sscanf( $digit, '%d.%d');
	switch (strlen($digit)){
        case 1: $digit .= '00000000000'; break;
        case 2: $digit .= '0000000000'; break;
        case 3: $digit .= '000000000'; break;
        case 4: $digit .= '00000000'; break;
        case 5: $digit .= '0000000'; break;
        case 6: $digit .= '000000'; break;
        case 7: $digit .= '00000'; break;
        case 8: $digit .= '0000'; break;
        case 9: $digit .= '000'; break;
        case 10: $digit .= '00'; break;
        case 11: $digit .= '0'; break;
    }
    return $digit;
}

add_filter( 'page_template', 'wpa3396_page_template' );
function wpa3396_page_template( $page_template )
{
    if ( is_page( 'factor' ) )  $page_template = dirname( __FILE__ ) . '/factor.php';
	if ( is_page( 'check' ) )  $page_template = dirname( __FILE__ ) . '/check.php';    
    return $page_template;
}
?>
