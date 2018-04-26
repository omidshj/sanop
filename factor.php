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
	    }
        
	}
?>
<html>
    <head>
        <style>
            @font-face { font-family: IRANSans;	font-style: normal;	font-weight: bold; src:url("http://www.sanop.org/wp-content/themes/hooramat/assets/font/IRANSans/IRANSans_Bold.woff") format("woff"); }
            @font-face { font-family: IRANSans; font-style: normal; font-weight: 500; src:url("http://www.sanop.org/wp-content/themes/hooramat/assets/font/IRANSans/IRANSans_Medium.woff") format("woff"); }
            @font-face { font-family: IRANSans; font-style: normal; font-weight: normal; src:url("http://www.sanop.org/wp-content/themes/hooramat/assets/font/IRANSans/IRANSans.woff") format("woff"); }
            @font-face { font-family: IRANSans; font-style: normal; font-weight: 300; src:url("http://www.sanop.org/wp-content/themes/hooramat/assets/font/IRANSans/IRANSans_Light.woff") format("woff"); }
            @font-face { font-family: IRANSans; font-style: normal; font-weight: 200; src:url("http://www.sanop.org/wp-content/themes/hooramat/assets/font/IRANSans/IRANSans_UltraLight.woff") format("woff"); }
            body{
                direction: rtl;
                font-family: IRANSans;
            }
            .container{
                max-width: 800px;
                margin: 0 auto;
                text-align: right;
            }
            tr{
                
            }
            th, td{
                padding: 12px 15px;
                border-bottom: 1px solid #ddd;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <?php if (count($key_db)): ?>
                <br>
                <h1 style="text-align: center; color: #999">فاکتور خرید کد فعال سازی نرم افزار فرم الف</h1>
                <h3 style="text-align: center;">فروشنده: سانوپ</h3>
                <h3 style="text-align: center;">خریدار: آموزشگاه <?= $key_data[1] ?></h3>
                <br>
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
                        <th>سال تحصیلی </th>
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
                          <th>کد فعال سازی</th>
                          <td>
                            <span class="green-text"><?php echo  active_code($key);?></span>
                          </td>
                      </tr>
                      <tr>
                          <th>تاریخ پرداخت</th>
                          <td><?= jdate("d F Y -  H:i:s",$timestamp) ?></td>
                      </tr>
                      <tr>
                          <th>شماره پیگیری</th>
                          <td><?= $key_db[0]->payment_code ?></td>
                      </tr>
                      <tr>
                          <th>مبلغ</th>
                          <td>۱۰٫۰۰۰ تومان</td>
                      </tr>
                    </tbody>
                </table>
            <?php else: ?>
                <br><br>
                <h1 style="text-align: center; color: #999">اطلاعات اشتباه است</h1>
                <br><br>
            <?php endif; ?>
        </div>
    </body>
</html>