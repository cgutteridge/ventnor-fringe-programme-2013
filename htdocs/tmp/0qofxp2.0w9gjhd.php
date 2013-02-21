<html>
   <head>
      <title><?php echo $html_title; ?></title>
      <meta charset='utf8' />
   </head>
   <body>
      <?php echo $this->render($content,$this->mime,get_defined_vars()); ?>
<?php echo (int)27*3; ?>
   </body>
</html>
