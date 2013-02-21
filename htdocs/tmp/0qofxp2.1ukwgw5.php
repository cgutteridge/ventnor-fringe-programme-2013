<html>
   <head>
      <title><?php echo $html_title; ?></title>
      <meta charset='utf8' />
      <link rel='stylesheet' href='/ui/reset.css' ></link>
      <link rel='stylesheet' href='/ui/vfringe.css' ></link>
      <script src='/resources/jquery.min.js'></script>
   </head>
   <body>
VENTNOR FRINGE PROGRAMME.
(this site is under test. Correct information will be added later)
<hr />
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
<hr />
      <?php echo $this->render($content,$this->mime,get_defined_vars()); ?>
<hr style='margin-top: 50px;'/>
(c)2013Vfringe
   </body>
</html>
