<h1>Themes</h1>
<ul>
  <?php foreach (($themes?:array()) as $theme): ?>
    <li><a href='<?php echo $theme; ?>'><?php echo $theme->label(); ?></a></li>
  <?php endforeach; ?>
</ul>
