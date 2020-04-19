<?php
    
    $bot_url = "https://api.telegram.org/bot[bot token]";
    //-------------------------------------
    $show_products  = "نمایش محصولات ";
    $get_address    ="لطفا آدرس خود را وارد کنید";
    $get_name       ="لطفا نام خود را وارد کنید";
    $payment        ="درگاه پرداخت";
    
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
        
        if($text == 'دریافت لیست محصولات')
            show_products();
    }
     //-------------------------------------
    // این متد منویی برای نمایش محصولات به مشتری ارائه می کند
    function show_menu() {
        	
        $key1 = 'دریافت لیست محصولات';
    
        $reply_keyboard = [
                              [$key1]
                          ];
                          
        $reply_kb_options = [
                                'keyboard'          => $reply_keyboard ,
                                'resize_keyboard'   => true ,
                                'one_time_keyboard' => false ,
                            ];
                        
        $json_kb = json_encode($reply_kb_options);
        $reply = "با فشردن دکمه زیر، می توانید لیست محصولات ما را مشاهده فرمایید";
        $url = $GLOBALS['bot_url'] . "/sendMessage";
        $post_params = [ 'chat_id' => $GLOBALS['chat_id'] , 'text' => $reply , 'reply_markup' => $json_kb ];
        send_reply($url, $post_params);
    }
    
    //-------------------------------------
    // این متد محصولات را به مشتری عرضه می کند
    function show_products() {
        
        $connection = connect_to_db();
        $result = $connection -> query("SELECT * FROM products_test");
        
        while($row = $result -> fetch_assoc()) {
         
            $id        = $row['id'];
            $name      = $row['name'];
            $price     = $row['price'];
            $image_url = $row['image_url'];
            
            $reply  =  " نام :". $name . "\n". $price . " تومان". "\n";
            $url    =  $GLOBALS['bot_url']."/sendPhoto";
    	    $post_params = [ 
    	                        'chat_id' => $GLOBALS['chat_id'] , 
    	                        'photo'   => new CURLFile(realpath($image_url)) ,
    	                        'caption' => $reply
    	                   ];
    	                   send_reply($url, $post_params);
    	                   $inline_keyboard = [
                                [
                                    [ 'text' => "خرید محصول" , 'callback_data' => "buy_product".$id ]
                                ]
                           ];
    
        $inline_kb_options = [
                                'inline_keyboard' => $inline_keyboard
                             ];
        
        $json_kb = json_encode($inline_kb_options);  
        $reply   = "👇جهت خرید این محصول دکمه زیر را لمس کنید👇";
        $url = $GLOBALS['bot_url']."/sendMessage";
    	$post_params = [ 'chat_id' => $GLOBALS['chat_id'] , 'text' => $reply , 'reply_markup' => $json_kb ];
    	send_reply($url, $post_params);
    }
        
        $connection -> close();
        }

    //-------------------------------------
    // این متد دستورات مربوط به دکمه های شیشه ای را مدیریت می کند
    function detect_callback_received_and_reply() {
 
        $callback_data = $GLOBALS['data'];
        $str = substr($callback_data, 0, 11); 
        if($str == "buy_product") { // در صورتی که کاربر یک محصول را برای خرید انتخاب کند، مشخصات محصول انتخابی دوباره بررسی و مورد تایید کاربر قرار می گیرد
            $product_id = substr($callback_data, 11, strlen($callback_data));//get product id from glass button
            $connection = connect_to_db();//fetch the product from db
            $result = $connection -> query("SELECT * FROM products_test WHERE id = $product_id");
            $row = $result -> fetch_assoc();
            $reply  =  " نام :". $row['name'] . "\n". $row['price'] . " تومان". "\n";
            $url = $GLOBALS['bot_url']."/sendPhoto";
    	    $post_params = [ 
    	                        'chat_id' => $GLOBALS['chat_id'] , 
    	                        'photo'   => new CURLFile(realpath($image_url)) ,
    	                        'caption' => $reply
    	                   ];
    	   $url = $GLOBALS['bot_url']."/sendMessage";
    	$post_params = [ 'chat_id' => $GLOBALS['chat_id'] , 'text' => $reply , 'reply_markup' => $json_fr ];
    	    send_reply($url, $post_params);
        $reply   = "آیا محصول انتخابی شما صحیح است؟";
        $inline_keyboard = [
                                [
                                     [ 'text' => "بله" , 'callback_data' => "confirm_product".$product_id] ,
                                     [ 'text' => "خیر، صحیح نیست"   , 'callback_data' => "decline_product" ]
                                  ]
                           ];
    
            $inline_kb_options = [
                                'inline_keyboard' => $inline_keyboard,
                                'resize_keyboard' => TRUE
                             ];
        
       $json_kb = json_encode($inline_kb_options);
        $url = $GLOBALS['bot_url']."/sendMessage";
    	$post_params = [ 'chat_id' => $GLOBALS['chat_id'] , 'text' => $reply , 'reply_markup' => $json_kb ];
    	send_reply($url, $post_params);
    	$connection->close();
        }
        $str = substr($callback_data, 0, 15); 
        if($str == "confirm_product") {
            $product_id = substr($callback_data, 15, strlen($callback_data));//get product id from glass button
            $force_reply_options = [ 'force_reply' => true ];
            $json_fr = json_encode($force_reply_options);
            $reply = $product_id . "\n" . $GLOBALS['get_name'] ;
            $url = $GLOBALS['bot_url']."/sendMessage";
    	    $post_params = [ 'chat_id' => $GLOBALS['chat_id'] , 'text' => $reply , 'reply_markup' => $json_fr ];
    	    send_reply($url, $post_params);
        }
        $str = substr($callback_data, 0, 15); 
        if($str == "decline_product") {
            show_products();
        }
        $str = substr($callback_data, 0, 11);
        if($str == "payment_key") {
             //اطلاعات درگاه پرداخت
        }
        
    }
    
    //-------------------------------------
    //این متد متن های ارسالی از سمت کاربر به سمت بات را بررسی میکند
    function detect_text_received_and_reply() {
 
        $text         = $GLOBALS['text'];          // پیام دریافتی از کاربر
        $text_replied = $GLOBALS['text_replied'];  // پیام ارسالی اولیه از سوی ربات
        
        $id = strtok($text_replied, "\n");
        switch($text_replied) {
            case ($id. "\n". $GLOBALS['get_name']) :  
                $name = $text;
                $connection = connect_to_db();
                $connection -> query("INSERT INTO client_test (name, product_id) VALUES ('$name', '$id')");//this is product id which id foreign key
                $client_id = $connection -> insert_id;
                $connection -> close();
                
                //--------------------
                $force_reply_options = [ 'force_reply' => true ];
                $json_fr = json_encode($force_reply_options);
                $reply   = $client_id . "\n".$GLOBALS['get_address'];
                $url = $GLOBALS['bot_url'] . "/sendMessage";
                $post_params = [ 'chat_id' => $GLOBALS['chat_id'] , 'text' => $reply , 'reply_markup' => $json_fr ];
                send_reply($url, $post_params);
                
 
                break;
 
            case ($id . "\n".$GLOBALS['get_address']) :  // this is client id
                
                $address = $text;
                $connection = connect_to_db();
                $connection -> query("UPDATE client SET address='$address' where id = $id");
                $product_id = $connection -> insert_id;
                $connection -> close();
                //--------------------
                //check user merchant and info again
    	    $connection = connect_to_db();
    	    $row = $connection -> query("SELECT * FROM client_test")->fetch_assoc();
            $name = $row['name'];
            $address     = $row['address'];
            $product_id  = $row['product_id'];
            $connection->close();
            $connection = connect_to_db();
            $product = $connection -> query("SELECT * FROM products_test WHERE id = $product_id") -> fetch_assoc();
            $selected_product = $product['name'];
            $price = $product['price'];
            $connection ->close;
            $reply  =  " شما قصد خرید محصول ". $selected_product . " را دارید.قیمت این محصول".$price." تومان است". "\n" ."اطلاعات شما به شرح زیر است  :". "\n"."نام :" .$name . "\n" ."آدرس :" ."$address". "\n";
            $url    =  $GLOBALS['bot_url']."/sendMessage";
    	    $post_params = [ 'chat_id' => $GLOBALS['chat_id'] , 'text' => $reply , 'reply_markup' => $json_fr ];
    	    send_reply($url, $post_params);
    	                   $inline_keyboard = [
                                [
                                    [ 'text' => "درگاه پرداخت" .$id , 'callback_data' =>"payment_key" ]
                                ]
                           ];
    
        $inline_kb_options = [
                                'inline_keyboard' => $inline_keyboard
                             ];
        
        $json_kb = json_encode($inline_kb_options);  
        $reply   = "👇در صورت صحیح بودن اطلاعات و تمایل به خرید محصول مورد نظر به درگاه پرداخت متصل شوید.👇";
        $url = $GLOBALS['bot_url']."/sendMessage";
    	$post_params = [ 'chat_id' => $GLOBALS['chat_id'] , 'text' => $reply , 'reply_markup' => $json_kb ];
    	send_reply($url, $post_params);
 
                break;
                
                
    }
    }
     //-------------------------------------
    //شناسایی دستورات خاص که با / به بات ارسال می شود
    function detect_command_and_reply() {
 
        $text = $GLOBALS['text'];
 
        if($text == '/start') {
            
            show_menu();
    	    return;
        }
    }
    
    //-------------------------------------
    
    function connect_to_db() {

        $connection = new mysqli("localhost", "[db username]", "[db password]", "[db name]");
        
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
