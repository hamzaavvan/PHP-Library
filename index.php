<?php

require_once 'Validate.php';

// Instantiating the Validate object
$validate = new Validate();

// checking for user fields
if (Input::exists()) {
	$validate->valid([
		'age' => 'required|length>=:4',
	]);
}

?>

<!-- Simple test form -->
<form method="post">
	Name: <input type="text" name="age" />
	<input type="submit" value="Check">
</form>


<?php echo $validate->error(); ?>
