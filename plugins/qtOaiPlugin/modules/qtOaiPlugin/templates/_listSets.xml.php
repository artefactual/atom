  <ListSets>
<?php
   $formatedSets='';
   foreach($sets as $set){
     $formatedSets.="     <set>
       <setSpec>dummy</setSpec>
       <setName>".$set."</setName>
     </set>\n";
   }
   echo $formatedSets;
?>
   </ListSets>
