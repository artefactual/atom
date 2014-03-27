'use strict';

module.exports = function ($q, $http, SETTINGS) {

  this.getActors = function (queryString) {

    return $http({
      method: 'GET',
      url: SETTINGS.frontendPath + 'api/actors',
      params: {
        authorized_form_of_name: queryString
      }
    }).then(function (res) {

      var actors = [];
      // This pushed objects into 'actors' array
      // angular.forEach(res.data.results, function (key, index) {
      //   this.push({ authorized_form_of_name: key.authorized_form_of_name, id: index });
      // }, actors);

    // This pushes strings into 'actors' array for typeahead
      angular.forEach(res.data.results, function (item) {
        actors.push(item.authorized_form_of_name);
      });
      console.log('actors array after "foreach": ', actors);
      return actors;
    });
  };

};
