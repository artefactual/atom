'use strict';

module.exports = function ($http, $q, SETTINGS) {

  // Create a map of level of descriptions IDs and its corresponding CSS class
  this.levels = {};
  for (var key in SETTINGS.drmc)
  {
    if (key.indexOf('lod_') === 0)
    {
      var name = key.slice(4).slice(0, -3).replace(/_/g, '-');
      this.levels[SETTINGS.drmc[key]] = name;
    }
  }

  this.getTree = function (id) {
    var self = this;
    return $http({
      method: 'GET',
      url: SETTINGS.frontendPath + 'api/informationobjects/' + id + '/tree'
    }).success(function (data)
    {
      // Iterate over all the elements of the tree and add a property "level"
      // containing a CSS class for every level of description. Should I be
      // doing this in cbd/graph.js?
      function addLevelCssClass (data)
      {
        for (var i in data)
        {
          var e = data[i];
          e.level = self.levels[e.level_of_description_id];

          if (typeof e.children !== 'undefined')
          {
            addLevelCssClass(e.children);
          }
        }
      }

      data.level = self.levels[data.level_of_description_id];
      addLevelCssClass(data.children);
    });
  };

  this.getById = function (id, params) {
    params = params || {};
    var configuration = {
      method: 'GET',
      url: SETTINGS.frontendPath + 'api/informationobjects/' + id,
    };
    if (Object.keys(params).length > 0) {
      configuration.params = params;
    }
    var self = this;
    return $http(configuration).success(function (data) {
      data.level = self.levels[data.level_of_description_id];
    });
  };

  this.get = function (params) {
    params = params || {};
    var configuration = {
      method: 'GET',
      url: SETTINGS.frontendPath + 'api/informationobjects'
    };
    if (Object.keys(params).length > 0) {
      configuration.params = params;
    }
    var self = this;
    return $http(configuration).success(function (data) {
      data.level = self.levels[data.level_of_description_id];
    });
  };

  this.getWorks = function (params) {
    params = params || {};
    params.level_id = SETTINGS.drmc.lod_artwork_record_id;
    return this.get(params);
  };

  this.getWork = function (id) {
    var params = { level_id: SETTINGS.drmc.lod_artwork_record_id };
    return this.getById(id, params);
  };

};
