var postData = context().req.body;

// translate updates to "classification" to match schema
postData['class'] = postData.classification;
delete postData.classification;

if (typeof postData.id != 'undefined') {
  dpd.aipsraw.put(postData, function(result, error) {
    var message,
        responseData = {};

    if (error) {
      responseData.message = JSON.parse(error).message;
      responseData.error = true;
    } else {
      responseData.message = 'Update successful.';
    }

    setResult(responseData);
  });
} else {
  setResult({'message': 'You must specify an ID when updating.', 'error': true});
}