<?
require('SimpleXLSXGen.php');

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

  if (isset(json_decode($user_info)->result->id)) {
    $result = json_decode($user_info)->result;

    $username = $result->name;
    $avatar = $result->avatar_url;
    $karma = $result->karma;
    $posts = $result->counters->entries;
    $comments = $result->counters->comments;
    $favorites = $result->counters->favorites;
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
        background-color: #2f3032;
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
      <h1 class="h3 mb-3 fw-normal text-white">Парсер закладок</h1>

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
      <div class="card text-white bg-dark mb-3">
        <div class="card-header"><?php echo $username ?></div>
        <div class="card-body">
          <div class="row">
            <div class="col-3">
              <img src="<?php echo $avatar ?>" class="img-thumbnail">
            </div>
            <div class="col-9" style="text-align: left;">
              <p class="card-text">
                <b>Постов:</b> <?php echo $posts ?> <br>
                <b>Комментариев:</b> <?php echo $comments ?> <br>
                <b>Закладки:</b> <?php echo $favorites ?> <br>
                <hr>
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" value="" id="posts">
                  <label class="form-check-label" for="posts">
                    Экспортировать посты
                  </label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" value="" id="comments">
                  <label class="form-check-label" for="comments">
                    Экспортировать комментарии
                  </label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" value="" id="favorites">
                  <label class="form-check-label" for="favorites">
                    Экспортировать закладки
                  </label>
                </div>
              </p>
            </div>
          </div>
          <hr>
          <button type="button" id="generate" class="btn btn-success">Сгенерировать файл</button>
          <div id="generating" class="alert alert-warning" role="alert" style="display: none;">
            Генерация файла началась, она может занять <b>от 2 до 30 минут</b>, взависимости от вашего кол-ва данных.<br><br>
            Пожалуйста, не закрывайте страницу и дождитесь появления кнопки для загрузки данных.<br>
            <img src="https://mir-s3-cdn-cf.behance.net/project_modules/max_632/04de2e31234507.564a1d23645bf.gif" style="width: 80px;">
          </div>
          <div id="suc" class="alert alert-success" role="alert" style="display: none;">
            Генерация файла завершена
          </div>
          <a href="/" id="link" style="display: none"><button type="button" class="btn btn-primary">Скачать файл</button></a>
        </div>
      </div>

      </form>
    <? } ?>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>

  <?php if (isset($_POST['token'])) { ?>
  <script>
    $( "#generate" ).click(function() {
      var posts  =$('#posts').is(":checked");
      var comments  =$('#comments').is(":checked");
      var favorites  =$('#favorites').is(":checked");

      $('#generate').css('display', 'none');
      $('#generating').css('display', 'block');

      axios.post('/engine.php', {
        posts: posts,
        comments: comments,
        favorites: favorites,
        token: '<?php echo $_POST['token'] ?>'
      })
      .then(function (response) {
        $('#generating').css('display', 'none');
        $('#suc').css('display', 'block');
        $('#link').css('display', 'block');
        $("#link").attr("href", response.data)
      })
      .catch(function (error) {
        console.log(error);
      });
    });
  </script>
  <?php } ?>

</body>
</html>