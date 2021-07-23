// Maintain Qubit and Drupal global variables
// for legacy reasons. Used to share functions
// and prototypes, and to be able to trigger
// JS functions over dynamically loaded content.
var Qubit = {};
var Drupal = {behaviors: {}};

Drupal.attachBehaviors = context => {
  context = context || document;
  // Can't use arrow function here in order
  // to access `this` and its methods.
  $.each(Drupal.behaviors, function() {
    this.attach(context);
  });
};
