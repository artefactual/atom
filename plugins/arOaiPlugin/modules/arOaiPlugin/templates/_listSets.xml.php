  <ListSets>
<?php foreach($sets as $set): ?>
    <set>
       <setSpec><?php echo $set->title ?></setSpec>
       <setName><?php echo $set ?></setName>
    </set>
<?php endforeach ?>
   </ListSets>
