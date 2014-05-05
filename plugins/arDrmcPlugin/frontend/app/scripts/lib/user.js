(function () {

  'use strict';

  function User (data) {
    this.username = data.username;
    this.email = data.email;
    this.groups = data.groups;
  }

  User.prototype.isMemberOf = function (group) {
    return this.groups.indexOf(group) !== -1;
  };

  User.prototype.isMemberOfOneOf = function (groups) {
    var result = false;
    var self = this;
    groups.forEach(function (group) {
      if (self.isMemberOf(group)) {
        result = true;
      }
    });
    return result;
  };

  User.prototype.canAdministrate = function () {
    return this.isMemberOf('administrator');
  };

  User.prototype.canEdit = function () {
    return this.isMemberOfOneOf([
      'administrator',
      'editor'
    ]);
  };

  User.prototype.canContribute = function () {
    return this.canEdit() || this.isMemberOf('contributors');
  };

  User.prototype.canRead = function () {
    return this.isMemberOf('authenticated');
  };

  module.exports = User;

})();
