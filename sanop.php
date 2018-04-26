<?php
/**
* @package Sanop
* @version 1.0
*/
/*
Plugin Name: Sanop
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
	if ($_GET['key']){
		$key = str_replace(" ", "+", $_GET['key']);
		$key_data = explode("d", base64_decode($key));
		$key_db = $wpdb->get_results( 'SELECT * FROM '.$wpdb->prefix.'sanop_licence WHERE school_code = '.$key_data[0] .' and class_code='. $key_data[4] .' and level= '.$key_data[2].' and academic_year = ' .$key_data[3], OBJECT );
		// print_r($key_db);
	}
	if ( $_GET['key'] && $_GET['agree'] == 'on' ):
		if (count($key_db)){
			echo 'این تراکنش قبلا پرداخت شده';
		}else{
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
				<p class="container small">
					همراه گرامی؛ شما پیشتر نسبت به خرید کد فعالسازی نرم‌افزار تکمیل فرم الف گزارش پیشرفت تحصیلی – تربیتی اقدام نموده اید.
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

// function sanop_api_info($request) {
// 	if ( empty( $request['term'] ) ) {
// 		return 'xxxxxxx';
// 	}
// 	$res =  array();
// 	$results = new WP_Query( array(
// 		'post_type'     => array( 'post', 'page' ),
// 		'post_status'   => 'publish',
// 		'posts_per_page'=> 10,
// 		's'             => $request['term'],
// 	) );
// 	if (  $results->have_posts()  ) {
// 		while ( $results->have_posts() ) {
// 			$results->the_post();
// 			$res[] = array(
// 				'title' => get_the_title(),
// 				'link' => get_permalink(),
// 			);
// 		}
// 		wp_reset_postdata();
// 	}
// 	return $res;
// }
// function sanop_api() {
// 	register_rest_route( 'sanop/', '/api', array(
// 		'methods'  => 'GET',
// 		'callback' => 'sanop_api_info',
// 	) );
// }
// add_action( 'rest_api_init', 'sanop_api' );


function sanop_api() {
	if ($_GET['key']){
		$key = str_replace(" ", "+", $_GET['key']);
		$key_data = explode("d", base64_decode($key));
		if (!$key_data[0] || !$key_data[2] || !$key_data[3] || !$key_data[4])
			wp_send_json(array('has-error' => true, 'error-message' => 'bad key'));
		global $wp;
		global $wpdb;
		$key_db = $wpdb->get_results( 'SELECT * FROM '.$wpdb->prefix.'sanop_licence WHERE school_code = '.$key_data[0] .' and class_code='. $key_data[4] .' and level= '.$key_data[2].' and academic_year = ' .$key_data[3], OBJECT );
		if (count($key_db)){
			if ($_GET['phone']) {
				$phone = $wpdb->update(
					$wpdb->prefix.'sanop_licence',
					array(
						'phone' => $_GET['phone'],	// string
						'phone_verify' => false
					),
					array(
						'school_code' => $key_data[0],
						'class_code' => $key_data[4],
						'level' => $key_data[2],
						'academic_year' => $key_data[3]
					)
				);
				if($phone){
					wp_send_json(array(
						'key' => $key,
						'payed' => true,
						'payment_time' => $key_db[0]->payment_time,
						'phone' => $_GET['phone'],
						'phone_verify' => false,
						'has-error' => false
					));
				}else{
					wp_send_json(array(
						'has-error' => true,
						'error-message' => 'cant save phone'
					));
				}
			}
			elseif ($_GET['phone_verify'] && !$key_db[0]->phone_verify) {
				// $verify = $key_db[0]->phone
				$verify = substr($key_db[0]->phone, -4, -2) . substr($key_db[0]->phone, -1) . substr($key_db[0]->phone, -5, -4);
				if ($verify == $_GET['phone_verify']) {
					$phone_verify = $wpdb->update(
						$wpdb->prefix.'sanop_licence',
						array(
							'phone_verify' => true,
						),
						array(
							'school_code' => $key_data[0],
							'class_code' => $key_data[4],
							'level' => $key_data[2],
							'academic_year' => $key_data[3]
						)
					);
					if($phone_verify){
						wp_send_json(array(
							'key' => $key,
							'payed' => true,
							'payment_time' => $key_db[0]->payment_time,
							'phone' => $key_db[0]->phone,
							'phone_verify' => true,
							'has-error' => false
						));
					}else{
						wp_send_json(array('has-error' => true, 'error-message' => 'cant save phone verify'));
					}
				}else{
					wp_send_json(array('has-error' => true, 'error-message' => 'verify code is not correct'));
				}
			}elseif ($_GET['verify'] && !$key_db[0]->phone_verify) {
				if (!$key_db[0]->phone)
					wp_send_json(array('has-error' => true, 'error-message' => 'phone not specified'));
				wp_send_json(sendSms(
					substr($key_db[0]->phone, -4, -2) . substr($key_db[0]->phone, -1) . substr($key_db[0]->phone, -5, -4),
					$key_db[0]->phone
				));
			}
			wp_send_json(array(
				'key' => $key,
				'payed' => true,
				'payment_time' => $key_db[0]->payment_time,
				'phone' => $key_db[0]->phone,
				'phone_verify' => $key_db[0]->phone_verify? true: false,
				'has-error' => false
			));
		}else{
			wp_send_json(array(
				'key' => $key,
				'payed' => false,
				'has-error' => false
			));
		}
	}
	wp_send_json(array(
		'has-error' => true,
		'error-message' => 'no key specified'
	));
}
add_action( 'wp_ajax_nopriv_sanop_api', 'sanop_api' );

/*
 * Activation code generator
 */
function active_code($key) {
	$key_data = explode("d", base64_decode($key));
	$schoolcode = 10000 * $key_data[0];
	$classcode = 10000 * $key_data[4];
	$basecode = 100 * $key_data[3];
	return dechex(
		( (10000 * $key_data[0]) + (10000 * $key_data[4]) + (100 * $key_data[2]) + $key_data[3] ) / 0.66964817
	);
}

/*
 * Admin area pages
 */
function sanop_menu() {
	add_menu_page('My Cool Plugin Settings', 'پرداختی ها', 'edit_posts', __FILE__, 'sanop_settings_page' , plugins_url('/images/icon.png', __FILE__) );
}
add_action('admin_menu', 'sanop_menu');
function sanop_settings_page() {
	global $wpdb;
	$pays = $wpdb->get_results( 'SELECT * FROM '.$wpdb->prefix.'sanop_licence ORDER BY payment_time DESC LIMIT 500', OBJECT );
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

/*
 * FACTOR and CHECK pages
 */
function sanop_page_template( $page_template ){
	if ( is_page( 'factor' ) )  $page_template = dirname( __FILE__ ) . '/factor.php';
	if ( is_page( 'check' ) )  $page_template = dirname( __FILE__ ) . '/check.php';
	return $page_template;
}
add_filter( 'page_template', 'sanop_page_template' );

/*
 * Initial database
 */
function sanop_create_db() {
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
		phone varchar(20) NULL DEFAULT NULL,
		phone varchar(20) NULL DEFAULT NULL,
		phone_verify BOOLEAN NULL DEFAULT NULL,
		UNIQUE KEY id (id)
	) $charset_collate;";
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );
}
register_activation_hook( __FILE__, 'sanop_create_db' );



function getSmsIrToken(){
	$postData = array(
		'UserApiKey' => 'bfcc99d57f2f37edff689d2a',
		'SecretKey' => 'uiy3@d9@#%FI4?>D_+2^!xG}|&',
		'System' => 'php_rest_v_1_1'
	);
	$postString = json_encode($postData);
	$ch = curl_init("http://RestfulSms.com/api/Token");
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($ch, CURLOPT_POST, count($postString));
	curl_setopt($ch, CURLOPT_POSTFIELDS, $postString);
	$result = curl_exec($ch);
	curl_close($ch);
	$response = json_decode($result);
	if(is_object($response)){
		$resultVars = get_object_vars($response);
		if(is_array($resultVars)){
			@$IsSuccessful = $resultVars['IsSuccessful'];
			if($IsSuccessful == true){
				@$TokenKey = $resultVars['TokenKey'];
				$resp = $TokenKey;
			} else {
				$resp = false;
			}
		}
	}
	return $resp;
}
function sendSms($Code, $MobileNumber){
	$token = getSmsIrToken();
	if($token != false){
		$postData = array(
			'Code' => $Code,
			'MobileNumber' => $MobileNumber,
		);
		$url = "http://RestfulSms.com/api/VerificationCode";

		$postString = json_encode($postData);
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json',
			'x-sms-ir-secure-token: '.$token
		));
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_POST, count($postString));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postString);
		$VerificationCode = curl_exec($ch);
		curl_close($ch);

		// return $result;

		// $VerificationCode = $this->execute($postData, $url, $token);
		$object = json_decode($VerificationCode);

		if(is_object($object)){
			$array = get_object_vars($object);
			if(is_array($array)){
				$result = $array['Message'];
			} else {
				$result = false;
			}
		} else {
			$result = false;
		}
	} else {
		$result = false;
	}
	return $result;
}

// function hash_digit ($digit) {
//     $digit = bcdiv($digit, '0.66964817', 13);
//     list($whole, $digit) = sscanf( $digit, '%d.%d');
// 	$digit = round ( '0.'.$digit, 12);
// 	list($whole, $digit) = sscanf( $digit, '%d.%d');
// 	switch (strlen($digit)){
//         case 1: $digit .= '00000000000'; break;
//         case 2: $digit .= '0000000000'; break;
//         case 3: $digit .= '000000000'; break;
//         case 4: $digit .= '00000000'; break;
//         case 5: $digit .= '0000000'; break;
//         case 6: $digit .= '000000'; break;
//         case 7: $digit .= '00000'; break;
//         case 8: $digit .= '0000'; break;
//         case 9: $digit .= '000'; break;
//         case 10: $digit .= '00'; break;
//         case 11: $digit .= '0'; break;
//     }
//     return $digit;
// }
?>
