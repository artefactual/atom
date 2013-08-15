// This function will attach datepickers to the date
// range text fields in the advanced search form.

jQuery(document).ready(function() {
  var opts = {
    changeYear: true,
    changeMonth: true,
    yearRange: "-70:+70",
    dateFormat: "yymmdd",
    defaultDate: new Date(1950, 0, 1)
  };

  jQuery("#startDate").datepicker(opts);
  jQuery("#endDate").datepicker(opts);
});