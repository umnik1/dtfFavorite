<?php
require('SimpleXLSXGen.php');

$_POST = json_decode(file_get_contents('php://input'), true);

if (isset($_POST['token'])) {

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,"https://api.dtf.ru/v1.8/user/me");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET"); 
    
    $headers = [
        'X-Device-Token: '.$_POST['token'].'',
        'User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:28.0) Gecko/20100101 Firefox/28.0'
    ];
    
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    $user_info = curl_exec ($ch);
    
    curl_close ($ch);

    $xlsx = new SimpleXLSXGen();
    
    if (isset(json_decode($user_info)->result->id)) {
      $userid = json_decode($user_info)->result->id;
      $fav = json_decode($user_info)->result->counters->favorites;
      $posts =  json_decode($user_info)->result->counters->entries;
      $comments =  json_decode($user_info)->result->counters->comments;
  
      $file = './lists/'.time().'.xlsx';
      $link_file = '/lists/'.time().'.xlsx';
      $xlsx = new SimpleXLSXGen();

      // Посты
      if ($_POST['posts'] == true) {
        $cc = 0;
  
        $posts_data = [['<b>ID</b>', '<b>Link</b>', '<b>Title</b>' ,'<b>Subsite</b>' ,'<b>Post Date</b>', '<b>Likes</b>', '<b>Comments</b>' ]];
    
        for ($i = 0; $i <= round($posts / 50); $i++) {
          $ch = curl_init();
          curl_setopt($ch, CURLOPT_URL,"https://api.dtf.ru/v1.8/user/".$userid."/entries?count=50&offset=".$i*50);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
          curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET"); 
          
          $headers = [
              'X-Device-Token: '.$_POST['token'].'',
              'User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:28.0) Gecko/20100101 Firefox/28.0'
          ];
          
          curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
          
          $server_output = curl_exec ($ch);
          
          curl_close ($ch);
    
          foreach (json_decode($server_output) as $out) {
              if (count((array)$out) > 1) {
                  foreach ($out as $element) {
                    $data = [$element->id, $element->url, $element->title, '<a href="'.$element->subsite->url.'">'.$element->subsite->name.'</a>', date('d.m.Y H:i:s', $element->date), $element->likes->count, $element->commentsCount];
                    array_push($posts_data, $data);
                    $cc += 1;
                  }
              }
          }
        }
        
        $xlsx->addSheet( $posts_data, 'Посты' );
      }

      // Комментарии
      if ($_POST['comments'] == true) {
        $cc = 0;
  
        $comments_data = [['<b>Link</b>', '<b>Post Title</b>' ,'<b>Subsite</b>' ,'<b>Comment Date</b>', '<b>Likes</b>', '<b>Comment Text</b>' ]];
    
        for ($i = 0; $i <= round($comments / 50); $i++) {
          $ch = curl_init();
          curl_setopt($ch, CURLOPT_URL,"https://api.dtf.ru/v1.8/user/".$userid."/comments?count=50&offset=".$i*50);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
          curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET"); 
          
          $headers = [
              'X-Device-Token: '.$_POST['token'].'',
              'User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:28.0) Gecko/20100101 Firefox/28.0'
          ];
          
          curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
          
          $server_output = curl_exec ($ch);
          
          curl_close ($ch);
    
          foreach (json_decode($server_output) as $out) {
              if (count((array)$out) > 1) {
                  foreach ($out as $element) {
                    $data = [$element->entry->url.'?comment='.$element->id, $element->entry->title, '<a href="'.$element->entry->subsite->url.'">'.$element->entry->subsite->name.'</a>', date('d.m.Y H:i:s', $element->date), $element->likes->count, $element->text];
                    array_push($comments_data, $data);
                    $cc += 1;
                  }
              }
          }
        }

        $xlsx->addSheet( $comments_data, 'Комментарии' );
      }

      // Избранное
      if ($_POST['favorites'] == true) {
        $cc = 0;
  
        $fav_data = [['<b>ID</b>', '<b>Link</b>', '<b>Title</b>' ,'<b>Subsite</b>' ,'<b>Author</b>', '<b>Favorite Date</b>', '<b>Post Date</b>', '<b>Likes</b>', '<b>Comments</b>' ]];
    
        for ($i = 0; $i <= round($fav / 50); $i++) {
          $ch = curl_init();
          curl_setopt($ch, CURLOPT_URL,"https://api.dtf.ru/v1.8/user/".$userid."/favorites/entries?count=50&offset=".$i*50);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
          curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET"); 
          
          $headers = [
              'X-Device-Token: '.$_POST['token'].'',
              'User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:28.0) Gecko/20100101 Firefox/28.0'
          ];
          
          curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
          
          $server_output = curl_exec ($ch);
          
          curl_close ($ch);
    
          foreach (json_decode($server_output) as $out) {
              if (count((array)$out) > 1) {
                  foreach ($out as $element) {
                    $data = [$element->id, $element->url, $element->title, '<a href="'.$element->subsite->url.'">'.$element->subsite->name.'</a>', '<a href="'.$element->author->url.'">'.$element->author->name.'</a>', date('d.m.Y H:i:s', $element->date_favorite), date('d.m.Y H:i:s', $element->date), $element->likes->count, $element->commentsCount];
                    array_push($fav_data, $data);
                    $cc += 1;
                  }
              }
          }
        }
        $xlsx->addSheet( $fav_data, 'Избранное');
      }
  
      // Сохранение файла
      $xlsx->saveAs($file);
  
      echo $link_file;
    }
  
  }