<html>
   <head>
      <title><?php echo $html_title; ?></title>
      <meta charset='utf8' />
      <link rel='stylesheet' href='/ui/reset.css' ></link>
      <link rel='stylesheet' href='/ui/vfringe.css' ></link>
      <script src='/resources/jquery.min.js'></script>
    <link rel="shortcut icon" href="http://2013.vfringe.co.uk/favicon.ico" />
   </head>
   <body>
<div style='width:99%;background-color: #fc0;-webkit-box-shadow: -3px 3px 0px 1px #FF0;box-shadow: -3px 3px 0px 1px #FF0;padding:0.5em'>
VENTNOR FRINGE PROGRAMME.
(System under test. THE INFORMATION BELOW IS WRONG and just being used to design this website!  )
</div>
<div style='width:99%;background-color: #fff;-webkit-box-shadow: -3px 3px 0px 1px #CCC;box-shadow: -3px 3px 0px 1px #CCC;margin-top:50px;padding:0.5em'>
<ul class='menu'>
<li><a href='/wed'>Wed</a></li>
<li><a href='/thu'>Thu</a></li>
<li><a href='/fri'>Fri</a></li>
<li><a href='/sat'>Sat</a></li>
<li><a href='/themes'>Themes</a></li>
<li><a href='/acts'>Acts</a></li>
<li><a href='/places'>Places</a></li>
<li><a href='/about'>About</a></li>
<li><a href='/debug'>Debug</a></li>
</ul>
</div>
<div style='width:99%;background-color: #fff;-webkit-box-shadow: -3px 3px 0px 1px #CCC;box-shadow: -3px 3px 0px 1px #CCC;padding:0.5em;margin-top:50px'>
      <?php echo $this->render($content,$this->mime,get_defined_vars()); ?>
</div>
   </body>
</html>
