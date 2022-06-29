// Maintain Qubit and Drupal global variables
// for legacy reasons. Used to share functions
// and prototypes, and to be able to trigger
// JS functions over dynamically loaded content.
var Qubit = {
  treeviewTypes: {
    default: { icon: "fas fa-folder" },
    Item: { icon: "fas fa-file-alt" },
    File: { icon: "fas fa-file-alt" },
    Series: { icon: "fas fa-folder" },
    Subseries: { icon: "fas fa-folder" },
    subfonds: { icon: "fas fa-folder" },
    "Sous-fonds": { icon: "fas fa-folder" },
    Fonds: { icon: "fas fa-archive" },
    Collection: { icon: "fas fa-archive" },
  },
};
var Drupal = { behaviors: {} };

Drupal.attachBehaviors = (context) => {
  context = context || document;
  // Can't use arrow function here in order
  // to access `this` and its methods.
  $.each(Drupal.behaviors, function () {
    this.attach(context);
  });
};

// Attach all behaviors on document ready
$(() => Drupal.attachBehaviors(document));

// Explicitly add vars to window for Webpack build
window.Qubit = Qubit;
window.Drupal = Drupal;
