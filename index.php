<?
require('SimpleXLSXGen.php');

// Данные с API прилетают в JSONe, не забываем декодить

// Вызываем, только если отправлен токен
if (isset($_POST['token'])) {

  // Получаем данные пользователя
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL,"https://api.dtf.ru/v1.8/user/me");
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET"); 
  
  // Добавлятем к заголовку токен, т.к этого требует API
  $headers = [
      'X-Device-Token: '.$_POST['token'].'',
      'User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:28.0) Gecko/20100101 Firefox/28.0'
  ];
  
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  
  $user_info = curl_exec ($ch);
  
  curl_close ($ch);
  if (isset(json_decode($user_info)->result->id)) {
    $userid = json_decode($user_info)->result->id;
    $fav = json_decode($user_info)->result->counters->favorites;

    $file = './lists/'.time().'.xlsx';

    // Генерируем xlsx файл - https://github.com/shuchkin/simplexlsxgen
    $cc = 0;

    $sheet_data = [['<b>ID</b>', '<b>Link</b>', '<b>Title</b>' ,'<b>Subsite</b>' ,'<b>Author</b>', '<b>Favorite Date</b>', '<b>Post Date</b>', '<b>Likes</b>', '<b>Comments</b>' ]];
    $top_subsite = [];
    $top_author = [];

    // Так как максимальный лимит по записям на пользовтеля 50 штук, вешаем запрос в цикл
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

      // Генерируем строки и добавляем в наш файл
      foreach (json_decode($server_output) as $out) {
          if (count((array)$out) > 1) {
              foreach ($out as $element) {
                $data = [$element->id, $element->url, $element->title, '<a href="'.$element->subsite->url.'">'.$element->subsite->name.'</a>', '<a href="'.$element->author->url.'">'.$element->author->name.'</a>', date('d.m.Y H:i:s', $element->date_favorite), date('d.m.Y H:i:s', $element->date), $element->likes->count, $element->commentsCount];
                array_push($sheet_data, $data);

                if (isset($top_subsite[$element->subsite->id])) {
                  $top_subsite[$element->subsite->id]['count'] = $top_subsite[$element->subsite->id]['count'] + 1;
                } else {
                  $top_subsite += array($element->subsite->id => ['count' => 1, 'name' => $element->subsite->name, 'link' => $element->subsite->url]);
                }

                if (isset($top_author[$element->author->id])) {
                  $top_author[$element->author->id]['count'] = $top_author[$element->author->id]['count'] + 1;
                } else {
                  $top_author += array($element->author->id => ['count' => 1, 'name' => $element->author->name, 'link' => $element->author->url]);
                }

                $cc += 1;
              }
          }
      }
    }

    // Сортировка по убыванию, для топа подсайтов и авторов
    array_multisort($top_subsite, SORT_DESC, $top_subsite);
    array_multisort($top_author, SORT_DESC, $top_author);

    SimpleXLSXGen::fromArray( $sheet_data )->saveAs($file);
  }

}
?>

<html lang="en" data-lt-installed="true"><head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Парсер закладок для DTF">
    <meta name="author" content="Maksim Chingin">
    <title>DTF - Парсер закладок</title>

    <link href="css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KyZXEAg3QhqLMpG8r+8fhAXLRk2vvoC2f3B09zVXn8CA5QIVfZOJ3BCsw2P0p/We" crossorigin="anonymous">


    <meta name="theme-color" content="#7952b3">

    <style>
      .bd-placeholder-img {
        font-size: 1.125rem;
        text-anchor: middle;
        -webkit-user-select: none;
        -moz-user-select: none;
        user-select: none;
      }

      @media (min-width: 768px) {
        .bd-placeholder-img-lg {
          font-size: 3.5rem;
        }
      }
      html,
      body {
        height: 100%;
      }

      body {
        display: flex;
        align-items: center;
        padding-top: 40px;
        padding-bottom: 40px;
        background-color: #f5f5f5;
      }

      .form-signin {
        width: 100%;
        max-width: 400px;
        padding: 15px;
        margin: auto;
      }

      .form-signin .checkbox {
        font-weight: 400;
      }

      .form-signin .form-floating:focus-within {
        z-index: 2;
      }

      .form-signin input[type="email"] {
        margin-bottom: -1px;
        border-bottom-right-radius: 0;
        border-bottom-left-radius: 0;
      }

      .form-signin input[type="password"] {
        margin-bottom: 10px;
        border-top-left-radius: 0;
        border-top-right-radius: 0;
      }
    </style>
</head>
<body class="text-center">
  <main class="form-signin">
    <?php if (!isset($_POST['token'])) { ?>
    <form action="#" method="POST">
      <img class="mb-4" src="https://upload.wikimedia.org/wikipedia/commons/thumb/0/01/DTF_logo.svg/1200px-DTF_logo.svg.png" alt="" width="150" height="50">
      <h1 class="h3 mb-3 fw-normal">Парсер закладок</h1>

      <div class="form-floating">
        <input type="text" name="token" class="form-control" id="floatingInput" placeholder="">
        <label for="floatingInput">Свой Token</label>
      </div>
      <small><a href="https://www.notion.so/API-dd3f2ae5a4bc4a7fbeb4598f86eb37aa#7734c1a41c834da5b7425eac905f39ba">Как получить токен?</a></small>
      <br><br>
      <button class="w-100 btn btn-lg btn-primary" type="submit">Получить список закладок</button>
      <p class="mt-5 mb-3 text-muted">© <a href="https://dtf.ru/u/52199-maksim-chingin/">Maksim Chingin</a></p>
    </form>
    <? } else { ?>
      <form>
        <p>Найдено <b><?php echo $cc ?></b> закладок</p>
        <div class="card">
          <div class="card-body">
            <div class="container">
                <div class="row">
                    <div class="col" style="text-align: left;">
                      <b>Топ подсайтов</b>
                      <br><br>
                        <?php for ($i = 0; $i < min(5, count($top_subsite)); $i++) { ?>
                          <p><small><a href="<?php echo $top_subsite[$i]['link'] ?>"><?php echo $top_subsite[$i]['name'] ?></a> - <b><?php echo $top_subsite[$i]['count'] ?></b></small></p>
                        <?php } ?>
                    </div>
                    <div class="col" style="text-align: left;">
                      <b>Топ авторов</b>
                      <br><br>
                        <?php for ($i = 0; $i < min(5, count($top_author)); $i++) { ?>
                          <p><small><a href="<?php echo $top_author[$i]['link'] ?>"><?php echo $top_author[$i]['name'] ?></a> - <b><?php echo $top_author[$i]['count'] ?></b></small></p>
                        <?php } ?>
                    </div>
                </div>
              </div>
            </div>
        </div>
        <hr>
        <a href="<?php echo $file ?>" download>Скачать список</a>
      </form>
    <? } ?>
  </main>
</body>
</html>