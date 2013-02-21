
<h1><?php echo $theme->label(); ?></h1>

<!-- 
  loops over events AND activities 
  skips events if they realise an activity.
-->
<ul>
  <?php foreach (($theme->all('-dcterms:subject' )->sort('rdfs:label')?:array()) as $thing): ?>
    <?php if ($thing->isType('event:Event') && ! $thing->get('prog:realises')): ?>
      <li><a href='<?php echo $thing; ?>'><?php echo $thing->label(); ?></a> (event)</li>
    <?php endif; ?>
    <?php if ($thing->isType('prog:Activity')): ?>
      <li><a href='<?php echo $thing; ?>'><?php echo $thing->label(); ?></a> (activity)</li>
    <?php endif; ?>
  <?php endforeach; ?>
</ul>

