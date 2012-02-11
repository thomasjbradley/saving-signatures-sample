<?php

// To keep the code as clean as possible, separation of concerns and all that,
//  put the main logic for this application in a file separate from the HTML
require_once 'lib/save-signature.php';

?><!DOCTYPE html>
<head>
  <meta charset="utf-8">
  <title>Saving a Signature &middot; Signature Pad</title>
  <link rel="stylesheet" href="signature-pad/build/jquery.signaturepad.css">
  <!--[if lt IE 9]><script src="signature-pad/build/flashcanvas.js"></script><![endif]-->
  <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
</head>
<body>
  <?php
    // A small trigger to flip between showing the signature form and the regeneration screen
    if ($show_form) {
      require_once 'views/accept.php';
    } else {
      require_once 'views/regenerate.php';
    }
  ?>
  <script src="signature-pad/build/jquery.signaturepad.min.js"></script>
  <?php
    // Another trigger to write the appropriate Javascript to the page:
    //  If we are showing the form, write the initialization code
    //  If we are showing the final signature, write the regeneration code
    if ($show_form) :
  ?>
  <script>
    $(document).ready(function () {
      $('.sigPad').signaturePad({drawOnly : true});
    });
  </script>
  <?php else : ?>
  <script>
    $(document).ready(function () {
      var sig = <?php echo $output; ?>;
      $('.sigPad').signaturePad({displayOnly : true}).regenerate(sig);
    });
  </script>
  <?php endif; ?>
  <script src="signature-pad/build/json2.min.js"></script>
</body>
