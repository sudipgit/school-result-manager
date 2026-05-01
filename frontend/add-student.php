<?php
if (!current_user_can('manage_options')) exit;
global $wpdb;

$table = $wpdb->prefix . 'srm_students';

$is_edit = false;
$student = null;

/* ---------- EDIT MODE LOAD ---------- */
$is_edit = false;
$student = null;

if (isset($_GET['edit']) && intval($_GET['edit']) > 0) {
    $is_edit = true;
    $student_id = intval($_GET['edit']);

    $student = $wpdb->get_row(
        $wpdb->prepare("SELECT * FROM $table WHERE id = %d", $student_id)
    );

    if (!$student) {
        echo '<div class="notice notice-error"><p>Student not found.</p></div>';
        return;
    }
}


/* ---------- HANDLE SAVE ---------- */
if (isset($_POST['srm_save_student'])) {
    check_admin_referer('srm_save_student');

   $data = [
    'name'          => sanitize_text_field($_POST['name']),
    'roll'          => sanitize_text_field($_POST['roll']),
    'class'         => sanitize_text_field($_POST['class']),
    'section'       => sanitize_text_field($_POST['section']),
    'session'       => sanitize_text_field($_POST['session']),
    'gender'        => sanitize_text_field($_POST['gender']),
    'group'         => sanitize_text_field($_POST['group'] ?? ''),
    'father_name'   => sanitize_text_field($_POST['father_name']),
    'mother_name'   => sanitize_text_field($_POST['mother_name']),
    'address'       => sanitize_textarea_field($_POST['address']),
    'birth_reg_no'  => sanitize_text_field($_POST['birth_reg_no']),
];


    if (!empty($_POST['student_id'])) {
        // UPDATE
        $wpdb->update($table, $data, ['id' => intval($_POST['student_id'])]);
        echo '<div class="notice notice-success"><p>Student updated successfully.</p></div>';
        $is_edit = true;
    } else {
        // INSERT
        $wpdb->insert($table, $data);
        echo '<div class="notice notice-success"><p>Student added successfully.</p></div>';
    }
}

/* ---------- CONTEXT (ADD MODE ONLY) ---------- */
$class   = $_POST['class']   ?? '';
$section = $_POST['section'] ?? '';
$session = $_POST['session'] ?? '';

$classes = [6=>'Six',7=>'Seven',8=>'Eight',9=>'Nine',10=>'Ten'];
$sections = ['A','B','C'];
$sessions = ['2024','2025','2026'];
$genders  = ['Male','Female','Other'];
$groups   = ['Science','Commerce','Humanities'];
?>

<div class="wrap">
<h1><?= $is_edit ? 'Edit Student' : 'Add Student' ?></h1>

<?php if (!$is_edit): ?>

<!-- CONTEXT FORM -->
<form method="post" style="margin-bottom:20px;">
    <?php wp_nonce_field('srm_save_student'); ?>

    <h2>Class Context</h2>

    <select name="class" required>
        <option value="">Select Class</option>
        <?php foreach ($classes as $v=>$l): ?>
            <option value="<?= $v ?>" <?= $class==$v?'selected':'' ?>><?= $l ?></option>
        <?php endforeach; ?>
    </select>

    <select name="section" required>
        <option value="">Section</option>
        <?php foreach ($sections as $s): ?>
            <option value="<?= $s ?>" <?= $section==$s?'selected':'' ?>><?= $s ?></option>
        <?php endforeach; ?>
    </select>

    <select name="session" required>
        <option value="">Session</option>
        <?php foreach ($sessions as $sess): ?>
            <option value="<?= $sess ?>" <?= $session==$sess?'selected':'' ?>><?= $sess ?></option>
        <?php endforeach; ?>
    </select>

    <button class="button">Set Context</button>
</form>

<?php endif; ?>

<?php
// Determine values
$val = fn($k) => esc_attr($student->$k ?? '');
?>

<?php if ($is_edit || ($class && $section && $session)): ?>

<hr>

<form method="post">
<?php wp_nonce_field('srm_save_student'); ?>

<?php if ($is_edit): ?>
<input type="hidden" name="student_id" value="<?= intval($student->id) ?>">
<?php else: ?>
<input type="hidden" name="class" value="<?= esc_attr($class) ?>">
<input type="hidden" name="section" value="<?= esc_attr($section) ?>">
<input type="hidden" name="session" value="<?= esc_attr($session) ?>">
<?php endif; ?>

<table class="form-table">

<tr>
<th>Name</th>
<td><input type="text" name="name" value="<?= $val('name') ?>" required></td>
</tr>

<tr>
<th>Roll</th>
<td><input type="text" name="roll" value="<?= $val('roll') ?>" required></td>
</tr>

<tr>
<th>Gender</th>
<td>
<select name="gender" required>
    <option value="">Select Gender</option>
    <?php foreach ($genders as $g): ?>
        <option value="<?= $g ?>" <?= ($student->gender ?? '')==$g?'selected':'' ?>><?= $g ?></option>
    <?php endforeach; ?>
</select>
</td>
</tr>

<?php if ($is_edit): ?>
<tr>
<th>Class</th>
<td>
<select name="class">
<?php foreach ($classes as $v=>$l): ?>
<option value="<?= $v ?>" <?= $val('class')==$v?'selected':'' ?>><?= $l ?></option>
<?php endforeach; ?>
</select>
</td>
</tr>

<tr>
<th>Section</th>
<td>
<select name="section">
<?php foreach ($sections as $s): ?>
<option value="<?= $s ?>" <?= $val('section')==$s?'selected':'' ?>><?= $s ?></option>
<?php endforeach; ?>
</select>
</td>
</tr>

<tr>
<th>Session</th>
<td>
<select name="session">
<?php foreach ($sessions as $sess): ?>
<option value="<?= $sess ?>" <?= $val('session')==$sess?'selected':'' ?>><?= $sess ?></option>
<?php endforeach; ?>
</select>
</td>
</tr>
<?php endif; ?>
<tr>
<th>Group</th>
<td>
<select name="group">
    <option value="">— None —</option>
    <?php foreach ($groups as $g): ?>
        <option value="<?= esc_attr($g) ?>"
            <?= (($student->group ?? '') === $g) ? 'selected' : '' ?>>
            <?= esc_html($g) ?>
        </option>
    <?php endforeach; ?>
</select>
<p class="description">Optional</p>
</td>
</tr>


<tr><th>Father Name</th><td><input type="text" name="father_name" value="<?= $val('father_name') ?>"></td></tr>
<tr><th>Mother Name</th><td><input type="text" name="mother_name" value="<?= $val('mother_name') ?>"></td></tr>
<tr><th>Address</th><td><textarea name="address"><?= esc_textarea($student->address ?? '') ?></textarea></td></tr>
<tr><th>Birth Registration No</th><td><input type="text" name="birth_reg_no" value="<?= $val('birth_reg_no') ?>"></td></tr>

</table>

<button type="submit" name="srm_save_student" class="button button-primary">
<?= $is_edit ? 'Update Student' : 'Add Student' ?>
</button>

</form>

<?php else: ?>
<p><em>Please select Class, Section and Session first.</em></p>
<?php endif; ?>

</div>
