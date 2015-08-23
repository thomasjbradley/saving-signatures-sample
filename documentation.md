# Saving Signatures with PHP & MySQL

**A small PHP tutorial on how to capture the signature from Signature Pad and store it in a MySQL database, for later retrieval.**

**This tutorial depends on Signature Pad. If you don’t know what that is go [check out Signature Pad, for capturing electronic signatures](https://github.com/thomasjbradley/signature-pad).**

---

*This tutorial is just a small overview of the important pieces of information from the downloadable sample application. [Check out the sample app itself](https://github.com/thomasjbradley/saving-signatures-sample/), there are lots of comments in the code to help you out.*

## Sample Application Files

```
saving-signatures-sample/
 |- index.php
 |- lib/
 |   |- save-signature.php
 |- views/
 |   |- accept.php
 |   |- regenerate.php
 |- signature-pad/
 |   |- …
```

- `index.php` contains the basic HTML template and a few `if-statements` to hide/show different pieces of HTML and Javascript.
- `lib/save-signature.php` is the meat and potatoes, the controller: it gets the content from the form, validates it, and saves it to the database.
- `views/accept.php` is the basic Signature Pad HTML template for [accepting a signature](https://github.com/thomasjbradley/signature-pad/blob/gh-pages/documentation.md#accepting-signatures), with the addition of some serverside validation code.
- `views/regenerate.php` is the basic Signature Pad HTML template for [regenerating a signature](https://github.com/thomasjbradley/signature-pad/blob/gh-pages/documentation.md#regenerating-signatures), with the addition of a little PHP to output the signator and date.
- `signature-pad` is just a clone of the complete Signature Pad Git repository. The sample application only uses files from the `build` sub-folder.

## Getting the Signature

Signature Pad submits the signature, along with the rest of the form submission, inside a hidden input field.

*From `views/accept.php`:*

```html
<form method="post" action="index.php">
  ⋮
  <input type="hidden" name="output">
  ⋮
</form>
```

From this hidden field we can capture the signature and store it in the database.

The easiest way to get the signature using PHP is with the `$_POST` super global.

```php
<?php
$sig = $_POST['output'];
```

Using the `$_POST` array isn’t secure (and creates a few other problems when there are validation errors and we try to keep information in the form). In PHP, the best way to get information from a form is using [PHP’s filter functions](http://php.net/filter). They provide a more secure way to grab user input and strip out unwanted information. We also won’t get any PHP error messages if we try to access the user input and the form hasn’t been submittted.

*From `lib/save-signature.php`:*

```php
<?php
$output = filter_input(INPUT_POST, 'output', FILTER_UNSAFE_RAW);
```

We can use `FILTER_UNSAFE_RAW` for the signature itself, because we don’t actually want to strip any information from the signature. If you want to be even more specific try using `FILTER_VALIDATE_REGEX`.

## Validating the Signature

Probably the best way to validate the signature would be to run it through `json_decode()` and see if it can be decoded.

*From `lib/save-signature.php`:*

```php
<?php
if (!json_decode($output)) {
  $errors['output'] = true;
}
```

**Don’t forget, you should also validate the name to make sure one was entered.**

## Setting Up the Database

The database to store the signature only needs to hold a few pieces of information: the signator’s name and the signature. For legal reasons, it is best to store more information about the signature: at least a hash of the signature, the signator’s IP address, and the time the signature was written.

For more information about electronic signature legallity, check out your country’s regulations. [Wikipedia has a good list of electronic signature regulations](http://en.wikipedia.org/wiki/Electronic_signature).

### Sample Table Setup

<table>
  <thead>
    <tr>
      <th scope="col">Name</th>
      <th scope="col">Data-Type</th>
      <th scope="col">Length</th>
      <th scope="col">What’s it For?</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>id</td>
      <td>int</td>
      <td>11</td>
      <td>Primary key, auto increment</td>
    </tr>
    <tr>
      <td>signator</td>
      <td>varchar</td>
      <td>255</td>
      <td>Holds the user input for the `name` field</td>
    </tr>
    <tr>
      <td>signature</td>
      <td>text</td>
      <td></td>
      <td>Holds the signature in its JSON form</td>
    </tr>
    <tr>
      <td>sig_hash</td>
      <td>varchar</td>
      <td>128</td>
      <td>Holds a SHA1 hash of the JSON signature</td>
    </tr>
    <tr>
      <td>ip</td>
      <td>varchar</td>
      <td>46</td>
      <td>Holds the signator’s IP address</td>
    </tr>
    <tr>
      <td>created</td>
      <td>int</td>
      <td>11</td>
      <td>The UNIX timestamp of when the signature was saved</td>
    </tr>
  </tbody>
</table>

## Saving to the Database

After everything is validated, we can save the signature to the database. It’s easiest to just store the JSON representation of the signature in the database. If you want to create a graphic file, [check out how to convert the signature to an image](https://github.com/thomasjbradley/signature-to-image).

It’s best to use PHP’s [PDO](http://php.net/pdo) for connecting to databases. By using `PDO::prepare()`, we can help protect against SQL injection attacks.

*From `lib/save-signature.php`:*

```php
<?php
// Open the database connection
$db = new PDO($dsn, $user, $pass);
// Make sure we are talking to the database in UTF-8
$db->exec('SET NAMES utf8');

// Create some other pieces of information about the user
//  to confirm the legitimacy of their signature
$sig_hash = sha1($output);
$created = time();
$ip = $_SERVER['REMOTE_ADDR'];

// Use PDO prepare to insert all the information into the database
$sql = $db->prepare('
  INSERT INTO signatures (signator, signature, sig_hash, ip, created)
  VALUES (:signator, :signature, :sig_hash, :ip, :created)
');
$sql->bindValue(':signator', $name, PDO::PARAM_STR);
$sql->bindValue(':signature', $output, PDO::PARAM_STR);
$sql->bindValue(':sig_hash', $sig_hash, PDO::PARAM_STR);
$sql->bindValue(':ip', $ip, PDO::PARAM_STR);
$sql->bindValue(':created', $created, PDO::PARAM_INT);
$sql->execute();
```

## Regenerating the Signature

One of the easiest ways to regenerate the signature is to use PHP to write out some Javascript onto one of your pages. When the page loads it will have a native Javascript varible containing all the signature information and can use Signature Pad to regenerate the display.

```php
<script>
  $(document).ready(function () {
    // Write out the complete signature from the database to Javascript
    var sig = <?php echo $output; ?>;
    $('.sigPad').signaturePad({displayOnly : true}).regenerate(sig);
  });
</script>
```

## What Else Do We Need?

Well, this application is far from complete—even though it’s fully functional. It’s a simple tutorial on how to capture the signature. Some of the missing things are:

1. User authentication, sign-in/sign-out, or unique passkeys.
2. HTTPS—critical for capturing signatures online.
3. It might be nice to encrypt the signatures in the database.

And likely more.
