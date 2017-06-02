(function ($)
    {
        Drupal.behaviors.staticpage_tinymce = {
            attach: function (context)
                {
                    // make a list of all css files on the current page.
                    // we need to tell tinymce about these so that it can 
                    // load the same files within the editor (for better wysiwyg).
                    var tinymce_css = '';
                    jQuery('head').find('link').each(function() {
                        var href = jQuery(this).attr('href')
                        if (/.css$/.test(href)) {
                            if (tinymce_css != '') {
                                tinymce_css += ',' 
                            }
                            tinymce_css += href;
                        }
                    });
                    tinymce.init({
                          selector: 'textarea', 
                          height: 250, 
                          content_css: tinymce_css,
                          body_id: 'content',
                    });
                } 
    };
})(jQuery);
