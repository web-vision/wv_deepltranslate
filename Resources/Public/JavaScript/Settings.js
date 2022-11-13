define(['jquery'], function() {
  //avoid numeric
  $('.languageAssigned').keypress(function (key) {
    if ((key.charCode > 90 && key.charCode < 97) || (key.charCode < 65 && key.charCode != 45) || key.charCode > 122) {
      return false;
    }
  });
});
