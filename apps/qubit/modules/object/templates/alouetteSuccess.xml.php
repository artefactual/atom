<add>
<?php foreach (array_merge(array($baseObject), $baseObject->getDescendants()) as $object): ?>
  <doc>
    <field name="id"><?php echo esc_entities($object->getIdentifier() ? $object->getIdentifier() : $object) ?></field>
    <field name="localid"><?php echo esc_entities($object->id) ?></field>
    <field name="accessionNo"/>

    <!--Dublin Core fields -->
    <field name="title"><?php echo esc_entities($object->getTitle()) ?></field>

    <?php foreach ($object->getCreators() as $creator): ?>
      <field name="creator"><?php echo esc_entities($creator->getAuthorizedFormOfName()) ?></field>
    <?php endforeach; ?>

    <field name="publisher"/><!-- how to distinguish creator from publisher? -->
    <field name="source"><?php echo esc_entities($object->getSources()) ?></field>
    <field name="language"><?php $i18nObject = $object->getInformationObjecti18ns(); echo $i18nObject[0]->getCulture(); ?></field>
    <field name="description"><?php echo esc_entities($object->getScopeAndContent()) ?></field>

    <?php foreach ($object->getSubjectAccessPoints() as $subject): ?>
      <field name="subject"><?php echo esc_entities($subject->getTerm()) ?></field>
    <?php endforeach; ?>

    <!-- the next two are based on dc.date-->
    <field name="created"><?php echo esc_entities($object->getCreatedAt()) ?></field>
    <field name="modified"><?php echo esc_entities($object->getUpdatedAt()) ?></field>

    <!-- the next two are based on dc.coverage-->
    <field name="spatial"/>
    <field name="temporal"><?php echo esc_entities($object->getRevisionHistory()) ?></field>

    <!-- this is based on dc.identifier-->
    <field name="url"><?php echo esc_entities(sfContext::getInstance()->getController()->genUrl('informationobject/show?id='.$object->id, true)) ?></field>
    <field name="mediaType"/><!-- dc.type: digitalobject -->
    <field name="type"/><!-- refines mediatype somewhat -->

    <!--variations on Dublin core fields for presentation and/or sorting-->
    <field name="thumbnail"/><!-- url to thumbnail image: digitalobject -->
    <field name="titleSort"><?php echo esc_entities(preg_replace('/[\[\]]/', '', strtolower($object->getTitle()))) ?></field>
    <field name="bibliographicCitation"/><!-- eg. Department of the Interior, and Keyes, Perley G., [Letter] 1904 July 19, Ottawa [to] Messrs. Munson and Allan, Barristers, Winnipeg, Manitoba, 1904 -->
    <field name="fSpatial"/>
    <field name="fSubject"/>
    <field name="abstract"/>

    <!-- fields used in OurOntario toolkit for discovery or faceting -->
    <field name="itemLatitude"/>
    <field name="itemLongitude"/>
    <field name="dateOldest"/>
    <field name="dateNewest"/>
    <!-- associated with eliciting and managing user comments -->
    <field name="mystery"/>
    <field name="featureMystery"/>
    <field name="comment"/>
    <field name="featureComment"/>

    <field name="site"><?php echo esc_entities($object->getRepository()) ?></field>
    <field name="recordOwner"><?php echo esc_entities($object->getInstitutionResponsibleIdentifier()) ?></field>
    <field name="searchSet">Alouette</field><!--  multiple eg. Alouette, BritishColumbia, Ontario -->
  </doc>
<?php endforeach; ?>
</add>
