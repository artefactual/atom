<?php echo '<?xml version="1.0" encoding="'.sfConfig::get('sf_charset', 'UTF-8').'" ?>'; ?>

<rdf:RDF
  xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
  xmlns:rdfs="http://www.w3.org/2000/01/rdf-schema#"
  xmlns:skos="http://www.w3.org/2004/02/skos/core#"
  xmlns:dc="http://purl.org/dc/elements/1.1/">

  <skos:ConceptScheme rdf:about="<?php echo url_for([$taxonomy, 'module' => 'taxonomy'], true); ?>">

    <?php foreach ($taxonomy->taxonomyI18ns as $i18n) { ?>
      <?php if (isset($i18n->name)) { ?>
        <dc:title xml:lang="<?php echo $i18n->culture; ?>"><?php echo $i18n->name; ?></dc:title>
      <?php } ?>
    <?php } ?>

    <?php foreach ($topLevelTerms as $term) { ?>
      <skos:hasTopConcept rdf:resource="<?php echo url_for([$term, 'module' => 'term'], true); ?>"/>
    <?php } ?>

  </skos:ConceptScheme>

  <?php foreach ($terms as $term) { ?>

    <skos:Concept rdf:about="<?php echo url_for([$term, 'module' => 'term'], true); ?>">

      <?php foreach ($term->termI18ns as $i18n) { ?>
        <?php if (null != $i18n->name) { ?>
          <skos:prefLabel xml:lang="<?php echo $i18n->culture; ?>"><?php echo $i18n->name; ?></skos:prefLabel>
        <?php } ?>
      <?php } ?>

      <?php if (0 < count($term->otherNames)) { ?>
        <?php foreach ($term->otherNames as $altLabel) { ?>
          <?php foreach ($altLabel->otherNameI18ns as $i18n) { ?>
            <?php if (null != $i18n->name) { ?>
              <skos:altLabel xml:lang="<?php echo $i18n->culture; ?>"><?php echo $i18n->name; ?></skos:altLabel>
            <?php } ?>
          <?php } ?>
        <?php } ?>
      <?php } ?>

      <skos:inScheme rdf:resource="<?php echo url_for([$taxonomy, 'module' => 'taxonomy'], true); ?>"/>

      <?php if (0 < count($scopeNotes = $term->getNotesByType(['noteTypeId' => QubitTerm::SCOPE_NOTE_ID]))) { ?>
        <?php foreach ($scopeNotes as $scopeNote) { ?>
          <?php foreach ($scopeNote->noteI18ns as $i18n) { ?>
            <?php if (isset($i18n->content)) { ?>
              <skos:scopeNote xml:lang="<?php echo $i18n->culture; ?>"><?php echo $i18n->content; ?></skos:scopeNote>
            <?php } ?>
          <?php } ?>
        <?php } ?>
      <?php } ?>

      <?php if (0 < count($sourceNotes = $term->getNotesByType(['noteTypeId' => QubitTerm::SOURCE_NOTE_ID]))) { ?>
        <?php foreach ($sourceNotes as $sourceNote) { ?>
          <?php foreach ($sourceNote->noteI18ns as $i18n) { ?>
            <?php if (isset($i18n->content)) { ?>
              <skos:note xml:lang="<?php echo $i18n->culture; ?>"><?php echo $i18n->content; ?></skos:note>
            <?php } ?>
          <?php } ?>
        <?php } ?>
      <?php } ?>

      <?php if (QubitTerm::ROOT_ID != $term->parentId) { ?>
        <?php if (!(isset($selectedTerm) && $selectedTerm->id == $term->id)) { ?>
          <skos:broader rdf:resource="<?php echo url_for([$term->parent, 'module' => 'term'], true); ?>"/>
        <?php } ?>
      <?php } ?>

      <?php foreach ($term->getChildren() as $child) { ?>
        <skos:narrower rdf:resource="<?php echo url_for([$child, 'module' => 'term'], true); ?>"/>
      <?php } ?>

      <?php if (0 < count($relations = QubitRelation::getBySubjectOrObjectId($term->id, ['typeId' => QubitTerm::TERM_RELATION_ASSOCIATIVE_ID]))) { ?>
        <?php foreach ($relations as $relation) { ?>
          <skos:related rdf:resource="<?php echo url_for([$relation->getOpposedObject($term->id), 'module' => 'term'], true); ?>"/>
        <?php } ?>
      <?php } ?>

    </skos:Concept>

  <?php } ?>

</rdf:RDF>
