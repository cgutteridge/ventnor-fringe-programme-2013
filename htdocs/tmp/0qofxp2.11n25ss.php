<ul>
  <?php foreach (($events?:array()) as $event): ?>
    <li><a href='<?php echo $event; ?>'><?php echo $event->label(); ?></a></li>
  <?php endforeach; ?>
</ul>
