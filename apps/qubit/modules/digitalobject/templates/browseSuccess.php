<?php use_helper('Javascript') ?>

<?php

echo javascript_tag(<<<content

(function ($)
  {

    $(document).ready(function()
      {

        $('.digital-object li > a')

          .click(function(event)
            {
              event.preventDefault();
            })
          .mouseover(function()
            {
              \$this = $(this).addClass('hover');
            })
          .mouseout(function()
            {
              \$this = $(this).removeClass('hover');
            })

          .hoverIntent({
            over: function()
              {
                \$layer = $(this)
                  .before('<div class="digital-object-detail" />')
                  .prev()
                  .mouseleave(function(event)
                    {
                      $(this).fadeOut(100, function()
                        {
                          $(this).remove();
                        });
                    });

                \$layer

                  .append(
                    \$this
                      .clone()
                      .wrap('div')
                      .addClass('img')
                      .css('position', 'static'))

                  .append(
                    \$this
                      .nextAll()
                      .clone()
                      .show());

                \$layer
                  .css({
                    'top': (\$layer.height() - \$this.height()) / -2,
                    'left': (\$layer.width() - \$this.width()) / -2
                   });

              },
            timeout: 500,
            out: function()
              {

              }})

          .parent().find('> h2, > div').hide();

      });

    $(window).load(function()
      {
        var maxWidth = 0;
        var maxHeight = 0;

        // Calculate maxHeight and maxWidth
        $('.digital-object-browser li').each(function()
          {
            \$this = $(this);

            width = \$this.width();
            height = \$this.height();

            if (width > maxWidth)
            {
              maxWidth = width;
            }

            if (height > maxHeight)
            {
              maxHeight = height;
            }
          });

        // Set maxWidth
        $('.digital-object-browser li').width(maxWidth);

        // Set maxWidth
        $('.digital-object-browser li').height(maxHeight);

        // 
        $('.digital-object-browser li').each(function()
          {
            \$anchor = \$(this).find('> a');

            if (maxHeight > \$anchor.height())
            {
              \$anchor
                .css('position', 'relative')
                .css('top', (maxHeight - \$anchor.height()) / 2);
            }

          });

      });

  })(jQuery);

content

);

?>

<h1><?php echo __('Browse %1% - %2%', array('%1%' => sfConfig::get('app_ui_label_digitalobject'), '%2%' => $mediaType->getName(array('cultureFallback' => true)))) ?></h1>

<div class="digital-object-browser">

  <ul class="digital-object">

    <?php foreach ($pager->getResults() as $key => $item): ?>

      <li>

        <?php if ($item->showAsCompoundDigitalObject()): ?>
          <?php echo get_component('digitalobject', 'show', array('resource' => $item->getPage(0), 'usageType' => QubitTerm::THUMBNAIL_ID, 'link' => array($item->informationObject, 'module' => 'informationobject'), 'iconOnly' => true)) ?>
        <?php else: ?>
          <?php echo get_component('digitalobject', 'show', array('resource' => $item, 'usageType' => QubitTerm::THUMBNAIL_ID, 'link' => array($item->informationObject, 'module' => 'informationobject'), 'iconOnly' => true)) ?>
        <?php endif; ?>

        <h2><?php echo link_to(render_title($item->informationObject), array($item->informationObject, 'module' => 'informationobject')) ?><?php if (QubitTerm::PUBLICATION_STATUS_DRAFT_ID == $item->informationObject->getPublicationStatus()->status->id): ?> <span class="publicationStatus"><?php echo $item->informationObject->getPublicationStatus()->status ?></span><?php endif; ?></h2>

        <?php if ($item->informationObject->getCollectionRoot() !== $item->informationObject): ?>
          <?php echo render_show(__('Part of'), link_to(render_title($item->informationObject->getCollectionRoot()), array($item->informationObject->getCollectionRoot(), 'module' => 'informationobject'))) ?>
        <?php endif; ?>

      </li>

  <?php endforeach; ?>

  </ul>

</div>

<?php echo get_partial('default/pager', array('pager' => $pager)) ?>
