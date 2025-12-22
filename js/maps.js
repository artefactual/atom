(($) => {
  "use strict";

  $(() => {
    var $container = $(".simple-map");

    if (!$container.length) {
      return;
    }

    window.initializeSimpleMap = function () {
      var location = new google.maps.LatLng(
        $container.data("latitude"),
        $container.data("longitude")
      );
      var map = new google.maps.Map($container.get(0), {
        zoom: 16,
        center: location,
        panControl: false,
        mapTypeControl: true,
        zoomControl: true,
        scaleControl: false,
        streetViewControl: false,
        mapTypeControlOptions: {
          style: google.maps.MapTypeControlStyle.DROPDOWN_MENU,
        },
        mapTypeId: google.maps.MapTypeId.ROADMAP,
        zoomControlOptions: { style: google.maps.ZoomControlStyle.SMALL },
      });
      var marker = new google.maps.Marker({ position: location, map: map });
    };

    $.getScript(
      "https://maps.google.com/maps/api/js?sensor=false&callback=initializeSimpleMap&key=" +
        $container.data("key")
    );
  });
})(jQuery);
