"use strict";

(function(Qubit) {
  Qubit.Pager = function(limit, options) {
    this.skip = 0; // Where to start displaying items from
    this.limit = limit; // Items per page
    this.total = 0; // Total items to page through

    // Whether or not paging state will be stored in the browser location
    this.locationHashStorage = true;

    if (
      typeof options != "undefined" &&
      options["disableLocationHashStorage"]
    ) {
      this.locationHashStorage = false;
    }

    this.init();
  };

  Qubit.Pager.prototype = {
    // Attempt to set skip/limit from values in URL's hash
    init: function() {
      var locationData = this.getLocationHashData();

      // Check to see if location hash data isn't empty
      if (JSON.stringify(locationData) != JSON.stringify({})) {
        // Restore state from location hash data
        this.skip = parseInt(locationData.skip);
        this.limit = parseInt(locationData.limit);
      }
    },

    // Get key/value data from the hash portion of the URL
    getLocationHashData: function() {
      var data = {};

      // If window location has hash data, attempt to parse it
      if (window.location.hash.indexOf("#") > -1) {
        var pairs = window.location.hash.substring(1).split("&");

        pairs.forEach(function(pair, index) {
          var pairData = pair.split("=");
          var key = pairData[0];
          var value = pairData[1];
          data[key] = value;
        });
      }

      return data;
    },

    // Store data in key/value format in the hash portion of the URL
    storeDataAsLocationHash: function(data) {
      if (!this.locationHashStorage) {
        return;
      }

      var serialized = "";

      // Serialize data's properties and values
      for (var key in data) {
        if (serialized != "") {
          serialized = serialized + "&";
        }
        serialized = serialized + key + "=" + data[key];
      }

      // Store serialied data as URL hash
      window.location.hash = serialized;
    },

    // Store the current skip/limit in the hash portion of the URL
    storeState: function() {
      this.storeDataAsLocationHash({ skip: this.skip, limit: this.limit });
    },

    // Get current skip value
    getSkip: function() {
      return this.skip;
    },

    // Set skip value
    setSkip: function(value) {
      this.skip = value;
      this.storeState();
    },

    // Get current limit value
    getLimit: function() {
      return this.limit;
    },

    // Set limit value
    setLimit: function(value) {
      this.limit = value;
      this.storeState();
    },

    // Get current total of items
    getTotal: function() {
      return this.total;
    },

    // Set total of items
    setTotal: function(value) {
      this.total = value;
    },

    // Move to next page
    next: function() {
      this.setSkip(this.skip + this.limit);
    },

    // Get remaining items
    getRemaining: function() {
      var remaining = this.getTotal() - (this.getSkip() + this.getLimit());
      return remaining <= 0 ? 0 : remaining;
    },

    replaceUrlTags: function(url) {
      url = url.replace("{skip}", this.getSkip());
      return url.replace("{limit}", this.getLimit());
    }
  };
})(Qubit);
