
<h1><?php echo $event->label(); ?></h1>

<?php if ($event->has( 'event:time' )): ?>
  <?php if ($event->get( 'event:time' )->has( 'tl:start' )): ?>
     <p>Start: <?php echo $event->get( 'event:time' )->get( 'tl:start' ); ?></p>
  <?php endif; ?>
  <?php if ($event->get( 'event:time' )->has( 'tl:end' )): ?>
     <p>End: <?php echo $event->get( 'event:time' )->get( 'tl:end' ); ?></p>
  <?php endif; ?>
<?php endif; ?>

<?php foreach (($event->all('prog:speaker' )?:array()) as $agent): ?>
   <p>Speaker: <?php echo $agent->prettyLink(); ?></p>
<?php endforeach; ?>

<?php if ($event->has( 'event:place' )): ?>
   <p>Place: <?php echo $event->get( 'event:place' )->prettyLink(); ?></p>
<?php endif; ?>

<?php if ($event->has( 'dcterms:description' )): ?>
  <?php echo Base::instance()->raw($event->get( 'dcterms:description' )); ?>
<?php endif; ?>

<?php if ($event->has( 'prog:realises' )): ?>
   <p>Activity: <?php echo $event->get( 'prog:realises' )->prettyLink(); ?></p>
<?php endif; ?>

<?php foreach (($event->all('dcterms:subject' )?:array()) as $theme): ?>
   <p>Theme: <?php echo $theme->prettyLink(); ?></p>
<?php endforeach; ?>

