// This function will attach datepickers to the date
// range text fields in the advanced search form.

jQuery(document).ready(function() {
  var opts = {
    changeYear: true,
    changeMonth: true,
    yearRange: "-70:+70",
    dateFormat: "yymmdd",
    defaultDate: new Date(1950, 0, 1),
    constrainInput: false
  };

  function normalizeDateString()
  {
    this.value = jQuery.trim(this.value);
    this.value = this.value.replace(/\//g, '');
    this.value = this.value.replace(/-/g, '');

    if (this.value.match(/^\d{4}$/) !== null)
    {
      this.value = this.value + '01';
    }
    if (this.value.match(/^\d{6}$/) !== null)
    {
      this.value = this.value + '01';
    }
  }

  jQuery("#startDate").focus(normalizeDateString).datepicker(opts);
  jQuery("#endDate").focus(normalizeDateString).datepicker(opts);
});
