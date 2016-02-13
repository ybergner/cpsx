<?php
//-----------------------------------------------------------------------
// Functions follow
//-----------------------------------------------------------------------

// Common get data from Form @return array $form
function get_form() {
  $form = array();
  if (getenv("REQUEST_METHOD") == "POST") {
    while (list($name,  $value)  =  each($_POST)) {
      $form[$name]  =  utf8_encode(strip_tags ($value));
    }
  }else{
    $query_string  =  getenv("QUERY_STRING");
    $query_array  =  split("&",  $query_string);
    while (list($key,  $val)  =  each($query_array)) {
      list($name,  $value)  =  split("=",  $val);
      $name  =  urldecode($name);
      $value  =  strip_tags (urldecode($value));
      $form[$name]  =  utf8_encode(htmlspecialchars($value));
    }
  }
  return $form;
}
?>
