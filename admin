<?php
    
    $bot_url    = "https://api.telegram.org/bot[botToken]";
    
    $bot_dl_url = "https://api.telegram.org/file/bot[botToken]";
    
    //-------------------------------------
    
    $enter_admin_name    = "لطفا نام خود را وارد کنید";
    $enter_admin_pass    = "لطفا رمز عبور خود را وارد کنید";
    
    $enter_product_name  = "نام محصول را وارد کنید";
    $enter_product_price = "قیمت محصول را وارد کنید";
    $enter_product_image  = "تصویر محصول را ارسال کنید";
    $product_registered  = "محصول جدید ثبت شد";
    
    $enter_new_name      = "نام جدید محصول را وارد کنید";
    $name_updated        = "نام محصول ویرایش شد";
    
    $enter_new_price     = "قیمت جدید محصول را وارد کنید";
    $price_updated       = "قیمت محصول ویرایش شد";
    
    $enter_new_image      = "تصویر جدید محصول را ارسال کنید";
    $image_updated       = "تصویر محصول ویرایش شد";

    $product_delete_msg  = "⁉️ محصول مورد نظر را حذف می کنید؟ ⁉️";
    $product_del_cancel  = "انصراف از حذف محصول";
    $product_deleted     = "محصول مورد نظر حذف شد";
    
    $order_delete_msg    = "سفارش مورد نظر را حذف می کنید؟";
    $order_deleted       = "سفارش حذف شد";
    $order_del_cancel    = "انصراف از حذف سفارش";
    
    //-------------------------------------
    
    $update = file_get_contents("php://input");
    
    $update_array = json_decode($update, true);
    
    if(isset($update_array["callback_query"])) {
 
        $data       = $update_array["callback_query"]["data"];
        $chat_id    = $update_array["callback_query"]["message"]["chat"]["id"];
        $message_id = $update_array["callback_query"]["message"]["message_id"];
 
        detect_callback_received_and_reply();
    }
    else if(isset($update_array["message"])) {
        
        $text    = $update_array["message"]["text"];
        $chat_id = $update_array["message"]["chat"]["id"];
        
        if(isset($update_array["message"]["reply_to_message"])) {
 
            $text_replied = $update_array["message"]["reply_to_message"]["text"];
 
            detect_text_received_and_reply();
        }
        
        $ch = substr($text, 0, 1);
        if($ch == '/') {
            detect_command_and_reply();
        }
        
    }
    
    //-------------------------------------
    //این متد دو گزینه را به ادمین فروشگاه نمایش می دهد. ادمین می تواند محصولات موجود در پایگاه داده را مشاهده کند. همچنین ادمین قادر است سفارشات جدید کاربران که در پایگاه داده ذخیره شده اند را مشاهده کند
    function show_menu() {
        
        $inline_keyboard = [
                                  [
                                     [ 'text' => "دریافت لیست محصولات" , 'callback_data' => "get_product_list"] ,
                                     [ 'text' => "دریافت لیست سفارشات"   , 'callback_data' => "get_orders_list" ]
                                  ]
                               ];
    
            $inline_kb_options = [
                                    'inline_keyboard' => $inline_keyboard
                                 ];
        
            $json_kb = json_encode($inline_kb_options);  
        $reply = "گزینه مورد نظر خود را انتخاب کنید";
        $url = $GLOBALS['bot_url'] . "/sendMessage";
        $post_params = [ 'chat_id' => $GLOBALS['chat_id'] , 'text' => $reply , 'reply_markup' => $json_kb ];
        send_reply($url, $post_params);
    }
    
    //-------------------------------------
    //این متد محصولات موجود در پایگاه داده را به ادمین نمایش می دهد. این دستورات لازم برای ویرایش یا حذف هر محصول به ادمین نمایش داده شده است و بدین طریق می تواند جدول محصولات در پایگاه داده را مدیریت کند 
    //همچنین دکمه افزودن محصولات جدید در این قسمت اضافه می شود
    function show_products() {
        
        $connection = connect_to_db();
        $result = $connection -> query("SELECT * FROM products");
        while($row = $result -> fetch_assoc()) {
         
            $id        = $row['id'];
            $name      = $row['name'];
            $price     = $row['price'];
            $image_url = $row['image_url'];
            
            $reply  =  " نام :". $name . "\n". $price . " تومان" . "\n\n";
            
            
            $reply .= "/edit"   . $id . "  👈  " . "ویرایش" . "\n";
            $reply .= "/delete" . $id . "  👈  " . "حذف"    . "\n\n";
            
            $url = $GLOBALS['bot_url']."/sendPhoto";
    	    $post_params = [ 
    	                        'chat_id' => $GLOBALS['chat_id'] , 
    	                        'photo'   => new CURLFile(realpath($image_url)) ,
    	                        'caption' => $reply
    	                   ];
    	    send_reply($url, $post_params);
        }
        
        $connection -> close();
     $inline_keyboard = [
                                [
                                    [ 'text' => "ثبت محصول جدید" , 'callback_data' => "add_new_product" ]
                                ]
                           ];
    
        $inline_kb_options = [
                                'inline_keyboard' => $inline_keyboard
                             ];
        
        $json_kb = json_encode($inline_kb_options);  
        $reply   = "👇جهت افزودن محصول جدید دکمه زیر را لمس کنید👇";
        $url = $GLOBALS['bot_url']."/sendMessage";
    	$post_params = [ 'chat_id' => $GLOBALS['chat_id'] , 'text' => $reply , 'reply_markup' => $json_kb ];
    	send_reply($url, $post_params);
    }
        //-------------------------------------
    //این متد سفارشات کاربران را به ادمین نمایش میدهد. در کنار هر سفارش، آدرس و نام کاربر جهت ارسال محصول نمایش داده میشود. ادمین قادر است پس از ارسال محصولات، سفارش مورد نظر را از جدول حذف کند
    function show_orders() {
        
        $reply = "show products";
        $url = $GLOBALS['bot_url']."/sendMessage";
    	$post_params = [ 'chat_id' => $GLOBALS['chat_id'] , 'text' => $reply , 'reply_markup' => $json_fr ];
        $connection = connect_to_db();
        
        $result = $connection -> query("SELECT * FROM client");
        
        while($row = $result -> fetch_assoc()) {
         
            $id        = $row['id'];
            $name      = $row['name'];
            $address     = $row['address'];
            $product_id      = $row['product_id'];
            $product = $connection -> query ("SELECT image_url FROM products where id = $product_id")->fetch_assoc();
            $image_url = $product['image_url'];
            
            $reply  =  " نام :". $name . "\n" . "آدرس :". $address ."\n\n";
            $reply .= "/OrderDelete" . $id . "  👈  " . "حذف"    . "\n\n";
            
            $url = $GLOBALS['bot_url']."/sendPhoto";
    	    $post_params = [ 
    	                        'chat_id' => $GLOBALS['chat_id'] , 
    	                        'photo'   => new CURLFile(realpath($image_url)) ,
    	                        'caption' => $reply
    	                   ];
    	    send_reply($url, $post_params);
        }
        
        $connection -> close();
    }
    //-------------------------------------
    //این متد برای شناسایی دستورات دکمه های شیشه ای و انجام عملیات مرتبط با هر دکمه نوشته شده است.
    function detect_callback_received_and_reply() {
 
        $callback_data = $GLOBALS['data'];
 
        if($callback_data == "add_new_product") {  //دکمه شیشه ای اضافه کردن محصول جدید
 
            $force_reply_options = [ 'force_reply' => true ];
            $json_fr = json_encode($force_reply_options);
            $reply = $GLOBALS['enter_product_name'];
            $url = $GLOBALS['bot_url'] . "/sendMessage";
            $post_params = [ 'chat_id' => $GLOBALS['chat_id'] , 'text' => $reply , 'reply_markup' => $json_fr ];
            send_reply($url, $post_params);
            
            return;
        }
        if($callback_data == "get_product_list") { //دکمه شیشه ای نمایش محصولات
            show_products();
            return;
        }
        
         if($callback_data == "get_orders_list") { //دکمه شیشه ای نمایش سفارشات
            show_orders();
            return;
        }
        
        if($callback_data == "delete_cancel") { //دکمه شیشه ای برای کنسل کردن حذف محصول انتخابی
            
            $reply = $GLOBALS['product_del_cancel'];  
            $url = $GLOBALS['bot_url'] . "/editMessageText";
            $post_params = [ 'chat_id' => $GLOBALS['chat_id'] , 'text' => $reply , 'message_id' => $GLOBALS['message_id'] ];
            send_reply($url, $post_params);
            
            return;
        }
        
        if($callback_data == "delete_order_cancel") { //دکمه شیشه ای برای کنسل کردن حذف سفارش مورد نظر
            
            $reply = $GLOBALS['order_del_cancel'];  
            $url = $GLOBALS['bot_url'] . "/editMessageText";
            $post_params = [ 'chat_id' => $GLOBALS['chat_id'] , 'text' => $reply , 'message_id' => $GLOBALS['message_id'] ];
            send_reply($url, $post_params);
            
            return;
        }
        
        $str = substr($callback_data, 0, 14);
        if($str == 'delete_product') { //دکمه شیشه ای برای تایید حذف محصول
            
            $product_id = substr($callback_data, 14, strlen($callback_data));
            
            $connection = connect_to_db();
            $result = $connection -> query("SELECT * FROM products_test WHERE id = $product_id");
            $row = $result -> fetch_assoc();
            $image_url = $row['image_url'];
            
            // حذف عکس از روی هاست
            unlink($image_url);  
            
            // حذف عکس از دیتابیس
            $connection->query("DELETE FROM products WHERE id = $product_id");
            
            $connection -> close();
            //---------------------
            $reply = $GLOBALS['product_deleted'];  
            $url = $GLOBALS['bot_url'] . "/editMessageText";
            $post_params = [ 'chat_id' => $GLOBALS['chat_id'] , 'text' => $reply , 'message_id' => $GLOBALS['message_id'] ];
            send_reply($url, $post_params);
            
            return;
        }
        
        $str = substr($callback_data, 0, 12);
        if($str == 'delete_order') { //دکمه شیشه ای برای تایید حذف سفارش
            
            $order_id = substr($callback_data, 12, strlen($callback_data));
            
            $connection = connect_to_db();
            // حذف از دیتابیس
            $connection->query("DELETE FROM client_test WHERE id = $order_id");
            
            $connection -> close();
            //---------------------
            $reply = $GLOBALS['order_deleted'];  
            $url = $GLOBALS['bot_url'] . "/editMessageText";
            $post_params = [ 'chat_id' => $GLOBALS['chat_id'] , 'text' => $reply , 'message_id' => $GLOBALS['message_id'] ];
            send_reply($url, $post_params);
            
            return;
        }
    }
    
    //-------------------------------------
    // از این متد برای شناسایی پاسخ های متنی کاربر به بات استفاده میشود
    function detect_text_received_and_reply() {
 
        $text         = $GLOBALS['text'];          // پیام دریافتی از کاربر
        $text_replied = $GLOBALS['text_replied'];  // پیام ارسالی اولیه از سوی ربات
        
        $product_id = strtok($text_replied, "\n");
 
        switch($text_replied) {
            
            
            
            case ($GLOBALS['enter_admin_name']) :  // احراز هویت ادمین
                
                $name = $text;
                if($name == '[admin name]'){
                $force_reply_options = [ 'force_reply' => true ];
                $json_fr = json_encode($force_reply_options);
                $reply   = $GLOBALS['enter_admin_pass'];
                $url = $GLOBALS['bot_url'] . "/sendMessage";
                $post_params = [ 'chat_id' => $GLOBALS['chat_id'] , 'text' => $reply , 'reply_markup' => $json_fr ];
                send_reply($url, $post_params);
                }
                else{
                     $reply   = "نام شما مورد تایید نمی باشد";
                $url = $GLOBALS['bot_url'] . "/sendMessage";
                $post_params = [ 'chat_id' => $GLOBALS['chat_id'] , 'text' => $reply , 'reply_markup' => $json_fr ];
                send_reply($url, $post_params);
                }
 
                break;
 
            
            
            case ($GLOBALS['enter_admin_pass']) :  // احراز هویت ادمین
                
                $pass = $text;
                if($pass == '[password for admin]'){
                show_menu();
                }
                else{
                     $reply   = "رمز عبور وارد شده صحیح نیست";
                $url = $GLOBALS['bot_url'] . "/sendMessage";
                $post_params = [ 'chat_id' => $GLOBALS['chat_id'] , 'text' => $reply , 'reply_markup' => $json_fr ];
                send_reply($url, $post_params);
                }
 
                break;
 
            
            
 
            case ($GLOBALS['enter_product_name']) :  // ثبت محصول جدید - دریافت نام محصول
                
                $product_name = $text;
 
                $connection = connect_to_db();
                $connection -> query("INSERT INTO products_test (name) VALUES ('$product_name')");
                $product_id = $connection -> insert_id;
                $connection -> close();
                //--------------------
                $force_reply_options = [ 'force_reply' => true ];
                $json_fr = json_encode($force_reply_options);
                $reply   = $product_id . "\n" . $GLOBALS['enter_product_price'];
                $url = $GLOBALS['bot_url'] . "/sendMessage";
                $post_params = [ 'chat_id' => $GLOBALS['chat_id'] , 'text' => $reply , 'reply_markup' => $json_fr ];
                send_reply($url, $post_params);
 
                break;
 
            case ($product_id . "\n" . $GLOBALS['enter_product_price']) :  // ثبت محصول جدید - دریافت قیمت محصول
                
                $product_price = $text;
 
                $connection = connect_to_db();
                $connection -> query("UPDATE products_test SET price = '$product_price' WHERE id = '$product_id'");
                $connection -> close();
                //--------------------
                $force_reply_options = [ 'force_reply' => true ];
                $json_fr = json_encode($force_reply_options);
                $reply   = $product_id ."\n" . $GLOBALS['enter_product_weight'];
                $url = $GLOBALS['bot_url'] . "/sendMessage";
                $post_params = [ 'chat_id' => $GLOBALS['chat_id'] , 'text' => $reply , 'reply_markup' => $json_fr ];
                send_reply($url, $post_params);
 
                break;
                
            case ($product_id . "\n" . $GLOBALS['enter_product_image']) :  // ثبت محصول جدید - دریافت تصویر محصول
                
                $update_array = $GLOBALS['update_array'];
                
                if( isset($update_array["message"]["photo"]) )
                    save_product_image($product_id);
                    
                $reply = $GLOBALS['product_registered'];
                $url = $GLOBALS['bot_url'] . "/sendMessage";
                $post_params = [ 'chat_id' => $GLOBALS['chat_id'] , 'text' => $reply ];
                send_reply($url, $post_params);
 
                break;
                
            case ($product_id . "\n" . $GLOBALS['enter_new_name']) :  // ویرایش محصول - دریافت نام جدید
                
                $new_name = $text;
 
                $connection = connect_to_db();
                $connection -> query("UPDATE products_test SET name = '$new_name' WHERE id = $product_id");
                $connection -> close();
                //--------------------
                $reply = $GLOBALS['name_updated'];
                $url = $GLOBALS['bot_url'] . "/sendMessage";
                $post_params = [ 'chat_id' => $GLOBALS['chat_id'] , 'text' => $reply ];
                send_reply($url, $post_params);

                break;
                
            case ($product_id . "\n" . $GLOBALS['enter_new_price']) :  // ویرایش محصول - دریافت قیمت جدید
                
                $new_price = $text;
 
                $connection = connect_to_db();
                $connection -> query("UPDATE products_test SET price = '$new_price' WHERE id = '$product_id'");
                $connection -> close();
                //--------------------
                $reply = $GLOBALS['price_updated'];
                $url = $GLOBALS['bot_url'] . "/sendMessage";
                $post_params = [ 'chat_id' => $GLOBALS['chat_id'] , 'text' => $reply ];
                send_reply($url, $post_params);
 
                break;
                
            
            case ($product_id . "\n" . $GLOBALS['enter_new_image']) :  // ویرایش محصول - دریافت تصویر جدید
                
                $update_array = $GLOBALS['update_array'];
                
                if(isset($update_array["message"]["photo"]))
                    save_product_image($product_id);
                
                $reply = $GLOBALS['image_updated'];
                $url = $GLOBALS['bot_url'] . "/sendMessage";
                $post_params = [ 'chat_id' => $GLOBALS['chat_id'] , 'text' => $reply ];
                send_reply($url, $post_params);
 
                break;
        }
    }
    
    //-------------------------------------
    //از این متد برای ذخیره عکس در پایگاه داده و بر روی هاست استفاده می شود
    function save_product_image($product_id) {
 
        $update_array = $GLOBALS['update_array'];
 
        $diff_size_count = sizeof($update_array["message"]["photo"]);
 
        for($i = $diff_size_count - 1 ; $i >= 0 ; $i--) {
 
            $file_size = $update_array["message"]["photo"][$i]["file_size"];
 
            if($file_size < 1000000) {  // 1 MB
 
                $file_id = $update_array["message"]["photo"][$i]["file_id"];
                break;
            }
        }
        
        $url = $GLOBALS['bot_url'] . "/getFile";
        $post_params = [ 'file_id' => $file_id ];
        $result = send_reply($url, $post_params);
 
        $result_array = json_decode($result, true);
        $file_path    = $result_array["result"]["file_path"];
 
        $url = $GLOBALS['bot_dl_url'] . "/$file_path";
        $file_data = file_get_contents($url);
 
        $img_path = "../images/" . $product_id . ".jpg";
        $my_file  = fopen($img_path, 'w');
        fwrite($my_file, $file_data);
        fclose($my_file);
        //--------------------------
        $connection = connect_to_db();
        $connection -> query("UPDATE products_test SET image_url = '$img_path' WHERE id = $product_id");
        $connection -> close();
    }
    
    //-------------------------------------
    // از این متد برا شناسایی و پاسخ به دستورات بات که با / شروع میشوند  استفاده میشود
    function detect_command_and_reply() {
 
        $text = $GLOBALS['text'];
 
        if($text == '/start') {
            $force_reply_options = [ 'force_reply' => true ];
            $json_fr = json_encode($force_reply_options);
            $reply = $GLOBALS['enter_admin_name'];
            $url = $GLOBALS['bot_url']."/sendMessage";
    	    $post_params = [ 'chat_id' => $GLOBALS['chat_id'] , 'text' => $reply , 'reply_markup' => $json_fr ];
    	    send_reply($url, $post_params);
            return;
        }
        //------------------------
        $str = substr($text, 0, 5);
    
        if($str == '/edit') {  // ویرایش محصول
        
            $product_id = substr($text, 5, strlen($text));
            
            $reply .= "/e_name"  . $product_id . "  👈  " . "ویرایش نام"   . "\n";
            $reply .= "/e_price" . $product_id . "  👈  " . "ویرایش قیمت"  . "\n";
            $reply .= "/e_image" . $product_id . "  👈  " . "ویرایش تصویر" . "\n\n";
            
            $url = $GLOBALS['bot_url']."/sendMessage";
    	    $post_params = [ 'chat_id' => $GLOBALS['chat_id'] , 'text' => $reply ];
    	    send_reply($url, $post_params);
    	    
    	    return;
        }
        //------------------------
        $str = substr($text, 0, 7);
    
        if($str == '/e_name') {  // ویرایش نام محصول
        
            $product_id = substr($text, 7, strlen($text));
            
            $force_reply_options = [ 'force_reply' => true ];
            $json_fr = json_encode($force_reply_options);
            $reply   = $product_id . "\n" . $GLOBALS['enter_new_name'];
            $url = $GLOBALS['bot_url'] . "/sendMessage";
            $post_params = [ 'chat_id' =>  $GLOBALS['chat_id'] , 'text' => $reply , 'reply_markup' => $json_fr ];
            send_reply($url, $post_params);
    	    
    	    return;
        }
               
        //------------------------
        $str = substr($text, 0, 8);
    
        if($str == '/e_price') {  // ویرایش قیمت محصول
        
            $product_id = substr($text, 8, strlen($text));
            
            $force_reply_options = [ 'force_reply' => true ];
            $json_fr = json_encode($force_reply_options);
            $reply   = $product_id . "\n" . $GLOBALS['enter_new_price'];
            $url = $GLOBALS['bot_url'] . "/sendMessage";
            $post_params = [ 'chat_id' =>  $GLOBALS['chat_id'] , 'text' => $reply , 'reply_markup' => $json_fr ];
            send_reply($url, $post_params);
    	    
    	    return;
        }
        //------------------------
        $str = substr($text, 0, 8);
    
        if($str == '/e_image') {  // ویرایش تصویر محصول
        
            $product_id = substr($text, 8, strlen($text));
            
            $force_reply_options = [ 'force_reply' => true ];
            $json_fr = json_encode($force_reply_options);
            $reply   = $product_id . "\n" . $GLOBALS['enter_new_image'];
            $url = $GLOBALS['bot_url'] . "/sendMessage";
            $post_params = [ 'chat_id' =>  $GLOBALS['chat_id'] , 'text' => $reply , 'reply_markup' => $json_fr ];
            send_reply($url, $post_params);
    	    
    	    return;
        }
        //------------------------
        $str = substr($text, 0, 7);
    
        if($str == '/delete') {  // حذف محصول
        
            $product_id = substr($text, 7, strlen($text));
            
            $inline_keyboard = [
                                  [
                                     [ 'text' => "خیر، منصرف شدم" , 'callback_data' => "delete_cancel"] ,
                                     [ 'text' => "بله، حذفش کن"   , 'callback_data' => "delete_product" . $product_id ]
                                  ]
                               ];
    
            $inline_kb_options = [
                                    'inline_keyboard' => $inline_keyboard
                                 ];
        
            $json_kb = json_encode($inline_kb_options);  
            $reply   = $GLOBALS['product_delete_msg'];
            $url = $GLOBALS['bot_url']."/sendMessage";
        	$post_params = [ 'chat_id' => $GLOBALS['chat_id'] , 'text' => $reply , 'reply_markup' => $json_kb ];
        	send_reply($url, $post_params);
    	    
    	    return;
        }
        //------------------------
        $str = substr($text, 0, 12);
    
        if($str == '/OrderDelete') {  // حذف سفارش
        
            $order_id = substr($text, 12, strlen($text));
            
            $inline_keyboard = [
                                  [
                                     [ 'text' => "خیر، حذف نشود" , 'callback_data' => "delete_order_cancel"] ,
                                     [ 'text' => "بله، حذفش کن"   , 'callback_data' => "delete_order"  .$order_id ]
                                  ]
                               ];
    
            $inline_kb_options = [
                                    'inline_keyboard' => $inline_keyboard
                                 ];
        
            $json_kb = json_encode($inline_kb_options);  
            $reply   = $GLOBALS['order_delete_msg'];
            $url = $GLOBALS['bot_url']."/sendMessage";
        	$post_params = [ 'chat_id' => $GLOBALS['chat_id'] , 'text' => $reply , 'reply_markup' => $json_kb ];
        	send_reply($url, $post_params);
    	    
    	    return;
        }
    }
    
    //-------------------------------------
    
    function connect_to_db() {

        $connection = new mysqli("localhost", "[username in db]", "[password for db]", "[db name]");
        
        if ($connection -> connect_error)
            echo "Failed to connect to db: " . $connection -> connect_error;
            
        $connection -> query("SET NAMES utf8");
        
        return $connection;
    }
    
    //-------------------------------------
    
    function send_reply($url, $post_params) {
        
        $cu = curl_init();
        curl_setopt($cu, CURLOPT_URL, $url);
        curl_setopt($cu, CURLOPT_POSTFIELDS, $post_params);
        curl_setopt($cu, CURLOPT_RETURNTRANSFER, true);  // get result
        $result = curl_exec($cu);
        curl_close($cu);
        return $result;
    }
    
    //-------------------------------------
?>
