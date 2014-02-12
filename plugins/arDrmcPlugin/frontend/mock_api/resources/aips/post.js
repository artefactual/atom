var postData = context().req.body;

dpd.aipsraw.put(postData, function(result, error) {
  var message;
  if (error) {
    message = 'Error updating.';
  } else {
    message = 'Update successful.';
  }
  setResult({'message': message});
});