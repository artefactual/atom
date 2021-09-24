import Tooltip from "bootstrap/js/dist/tooltip";

document
  .querySelectorAll("[data-bs-toggle=tooltip]")
  .forEach((element) => new Tooltip(element));
