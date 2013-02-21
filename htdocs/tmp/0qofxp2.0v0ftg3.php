<ul>
  <?php foreach (($places?:array()) as $place): ?>
    <li><a href='<?php echo $place; ?>'><?php echo $place->label(); ?></a></li>
  <?php endforeach; ?>
</ul>
