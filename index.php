<?php

require_once 'Validate.php';

$validate = new Validate();

if (Input::exists()) {

	$validate->valid([
		'age' => 'required|length<=:4',
	]);
}

?>

<form method="post">
	Name: <input type="text" name="age" />
	<input type="submit" value="Check">
</form>

<?php echo $validate->error(); ?>