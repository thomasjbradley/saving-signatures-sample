<?php
// The basic Signature Pad regeneration HTML
//  with the addition of PHP to write out the signator's name and signing date
?>
<div class="sigPad signed">
  <div class="sigWrapper">
  <div class="typed"><?php echo htmlentities($name, ENT_NOQUOTES, 'UTF-8'); ?></div>
    <canvas class="pad" width="198" height="55"></canvas>
  </div>
  <p><?php echo htmlentities($name, ENT_NOQUOTES, 'UTF-8'); ?><br><?php echo date('F j, Y', $created); ?></p>
</div>
