<?php
// The basic Signature Pad HTML template
//  with the addition of server side validation error messages
?>
<form method="post" action="index.php" class="sigPad">
  <?php if (isset($errors['output'])) : ?>
  <p class="error">Please sign the document</p>
  <?php endif; ?>
  <?php if (isset($errors['name'])) : ?>
  <p class="error">Please enter your name</p>
  <?php endif; ?>
  <label for="name"<?php echo (isset($errors['name'])) ? ' class="error"' : ''; ?>>Print your name</label>
    <input type="text" name="name" id="name" class="name<?php echo (isset($errors['name'])) ? ' error' : ''; ?>" value="<?php echo $name; ?>">
  <p class="drawItDesc">Draw your signature</p>
  <ul class="sigNav">
    <li class="drawIt"><a href="#draw-it" >Draw It</a></li>
    <li class="clearButton"><a href="#clear">Clear</a></li>
  </ul>
  <div class="sig sigWrapper">
    <div class="typed"></div>
    <canvas class="pad" width="198" height="55"></canvas>
    <input type="hidden" name="output" class="output">
  </div>
  <button type="submit">I accept the terms of this agreement.</button>
</form>
