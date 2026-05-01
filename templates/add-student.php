<?php
if (!current_user_can('edit_posts')) exit;
global $wpdb;

$table = $wpdb->prefix . 'srm_students';
$table_classes  = $wpdb->prefix . 'srm_classes';
$table_sections = $wpdb->prefix . 'srm_sections';

$is_edit = false;
$student = null;

// ---------- EDIT MODE LOAD ----------
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

// ---------- HANDLE SAVE ----------
if (isset($_POST['srm_save_student'])) {
    check_admin_referer('srm_save_student');

    $class = intval($_POST['class']);
    $section_id = intval($_POST['section_id']);

    // Get section name from DB
    $section_name = $wpdb->get_var(
        $wpdb->prepare("SELECT name FROM $table_sections WHERE id=%d", $section_id)
    );

    // Get current session
    $current_session = get_option('srm_active_session') ?: '2026';

    $data = [
        'name'           => sanitize_text_field($_POST['name']),
        'name_en'        => sanitize_text_field($_POST['name_en']),
        'roll'           => intval($_POST['roll']),
        'class_no'       => $class,
        'section_id'     => $section_id,
        'section'        => sanitize_text_field($section_name),
        'session'        => $current_session,
        'gender'         => sanitize_text_field($_POST['gender']),
        'group'          => sanitize_text_field($_POST['group'] ?? ''),
        'father_name'    => sanitize_text_field($_POST['father_name']),
        'father_name_en' => sanitize_text_field($_POST['father_name_en']),
        'mother_name'    => sanitize_text_field($_POST['mother_name']),
        'mother_name_en' => sanitize_text_field($_POST['mother_name_en']),
        'birth_reg_no'   => sanitize_text_field($_POST['birth_reg_no']),
        'dob'            => !empty($_POST['dob']) ? sanitize_text_field($_POST['dob']) : null,
        'village'        => sanitize_text_field($_POST['village']),
        'district'       => sanitize_text_field($_POST['district']),
        'upozilla'       => sanitize_text_field($_POST['upozilla']),
        'union_name'     => sanitize_text_field($_POST['union_name']),
        'religion'       => sanitize_text_field($_POST['religion']),
        'address'        => sanitize_textarea_field($_POST['address']),
    ];

    if (!empty($_POST['student_id'])) {
        // UPDATE — do NOT change unique_id or created
        $wpdb->update($table, $data, ['id' => intval($_POST['student_id'])]);
        echo '<div class="notice notice-success"><p>Student updated successfully.</p></div>';
        $is_edit = true;
    } else {
        // INSERT — generate unique_id and set created time
        $data['unique_id'] = random_int(1000000000, 9999999999);
        $data['created']   = time();
        $wpdb->insert($table, $data);
        echo '<div class="notice notice-success"><p>Student added successfully.</p></div>';
    }
}

// ---------- CONTEXT ----------
$class      = intval($_POST['class'] ?? $_GET['class'] ?? ($student->class_no ?? 0));
$section_id = intval($_POST['section_id'] ?? $_GET['section_id'] ?? ($student->section_id ?? 0));

// Fetch classes from DB
$classes = $wpdb->get_results("SELECT * FROM $table_classes WHERE status=1 ORDER BY class_no ASC");

// Fetch sections for selected class
$sections = [];
if ($class) {
    $sections = $wpdb->get_results(
        $wpdb->prepare("SELECT * FROM $table_sections WHERE class_no=%d AND status=1 ORDER BY name ASC", $class)
    );
}

// Gender and groups
$religions = ['Islam', 'Hindu'];
$genders   = ['Male', 'Female', 'Other'];
$groups    = ['Science', 'Commerce', 'Humanities'];
$current_session = get_option('srm_active_session') ?: '2026';

$val = fn($k) => esc_attr($student->$k ?? '');
?>

<div class="wrap">
<h1><?= $is_edit ? 'Edit Student' : 'Add Student' ?></h1>

<?php if (!$is_edit): ?>
<!-- CONTEXT FORM -->
<form method="get" style="margin-bottom:20px;">
    <h2>Class Context</h2>
    <<input type="hidden" name="page" value="<?= esc_attr($_GET['page'] ?? '') ?>">

    <select name="class" required onchange="this.form.submit()">
        <option value="">Select Class</option>
        <?php foreach ($classes as $c): ?>
            <option value="<?= $c->class_no ?>" <?= $class == $c->class_no ? 'selected' : '' ?>><?= esc_html($c->name) ?></option>
        <?php endforeach; ?>
    </select>

    <select name="section_id" required onchange="this.form.submit()">
        <option value="">Select Section</option>
        <?php foreach ($sections as $s): ?>
            <option value="<?= $s->id ?>" <?= $section_id == $s->id ? 'selected' : '' ?>><?= esc_html($s->name) ?></option>
        <?php endforeach; ?>
    </select>

    <button class="button">Set Context</button>
</form>
<?php endif; ?>

<?php
/* ================= CSV PREVIEW ================= */
$preview_rows = [];

if (isset($_POST['preview_csv']) && !empty($_FILES['csv']['tmp_name'])) {

    check_admin_referer('preview_csv');

    if (!$class || !$section_id) {
        echo "<div class='notice notice-error'><p>Select Class and Section first.</p></div>";
    } else {

        $file   = fopen($_FILES['csv']['tmp_name'], 'r');
        $header = array_map('trim', fgetcsv($file));
        $indexes = array_flip($header);

        $required = ['name_en', 'roll', 'gender'];

        foreach ($required as $r) {
            if (!isset($indexes[$r])) {
                echo "<div class='notice notice-error'><p>Missing column: $r</p></div>";
                return;
            }
        }

        while (($row = fgetcsv($file)) !== false) {

            $data = [];
            foreach ($indexes as $key => $i) {
                $data[$key] = trim($row[$i] ?? '');
            }

            // Skip empty row
            if (empty($data['name_en']) && empty($data['roll']) && empty($data['gender'])) {
                continue;
            }

            // Duplicate check
            $exists = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT id FROM $table WHERE class_no=%d AND roll=%d AND session=%s",
                    $class,
                    intval($data['roll']),
                    $current_session
                )
            );

            $preview_rows[] = [
                'data'      => $data,
                'duplicate' => $exists ? true : false,
            ];
        }

        fclose($file);
    }
}

/* ================= CSV CONFIRM ================= */
if (isset($_POST['confirm_csv']) && !empty($_POST['rows'])) {

    check_admin_referer('confirm_csv');

    $inserted = 0;
    $skipped  = 0;

    foreach ($_POST['rows'] as $json) {

        $data = json_decode(stripslashes($json), true);

        $exists = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM $table WHERE class_no=%d AND roll=%d AND session=%s",
                $class,
                intval($data['roll']),
                $current_session
            )
        );

        if ($exists) {
            $skipped++;
            continue;
        }

        $section_name = $wpdb->get_var(
            $wpdb->prepare("SELECT name FROM $table_sections WHERE id=%d", $section_id)
        );

        $name = $data['name'] ? sanitize_text_field($data['name']) : sanitize_text_field($data['name_en']);

        $wpdb->insert($table, [
            'unique_id'  => random_int(1000000000, 9999999999),
            'name'       => $name,
            'name_en'    => sanitize_text_field($data['name_en']),
            'roll'       => intval($data['roll']),
            'class_no'   => $class,
            'section_id' => $section_id,
            'section'    => sanitize_text_field($section_name),
            'session'    => $current_session,
            'gender'     => sanitize_text_field($data['gender']),
            'created'    => time(),
        ]);

        $inserted++;
    }

    echo "<div class='notice notice-success'>
            <p>Imported: $inserted | Skipped: $skipped</p>
          </div>";
}
?>

<?php if ($is_edit || ($class && $section_id)): ?>
    <?php if ($class && $section_id): ?>
    <hr>
    <h2>Import Students via CSV</h2>

    <form method="post" enctype="multipart/form-data">
        <?php wp_nonce_field('preview_csv'); ?>
        <input type="file" name="csv" accept=".csv" required>
        <button class="button button-primary" name="preview_csv">
            Preview CSV
        </button>
    </form>

    <?php if (!empty($preview_rows)) : ?>
        <hr>
        <h3>CSV Preview</h3>

        <form method="post">
            <?php wp_nonce_field('confirm_csv'); ?>

            <table class="wp-list-table widefat striped">
                <thead>
                    <tr>
                        <th>Name (EN)</th>
                        <th>Roll</th>
                        <th>Gender</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>

                <?php foreach ($preview_rows as $row) : ?>
                    <tr style="<?= $row['duplicate'] ? 'background:#ffecec;' : '' ?>">
                        <td><?= esc_html($row['data']['name_en']) ?></td>
                        <td><?= esc_html($row['data']['roll']) ?></td>
                        <td><?= esc_html($row['data']['gender']) ?></td>
                        <td>
                            <?= $row['duplicate'] ? 'Duplicate – Will Skip' : 'Ready to Import' ?>
                        </td>
                    </tr>

                    <?php if (!$row['duplicate']) : ?>
                        <input type="hidden" name="rows[]"
                            value="<?= esc_attr(json_encode($row['data'])) ?>">
                    <?php endif; ?>

                <?php endforeach; ?>

                </tbody>
            </table>

            <br>
            <button class="button button-primary" name="confirm_csv">
                Import Valid Students
            </button>

        </form>
    <?php endif; ?>
    <?php endif; ?>

<hr>
<form method="post">
<?php wp_nonce_field('srm_save_student'); ?>

<?php if ($is_edit): ?>
<input type="hidden" name="student_id" value="<?= intval($student->id) ?>">
<?php endif; ?>

<table class="form-table">

<tr>
<th>Class</th>
<td>
<select name="class" required onchange="this.form.submit()">
    <option value="">Select Class</option>
    <?php foreach ($classes as $c): ?>
        <option value="<?= $c->class_no ?>" <?= $class == $c->class_no ? 'selected' : '' ?>><?= esc_html($c->name) ?></option>
    <?php endforeach; ?>
</select>
</td>
</tr>

<tr>
<th>Section</th>
<td>
<select name="section_id" required onchange="this.form.submit()">
    <option value="">Select Section</option>
    <?php foreach ($sections as $s): ?>
        <option value="<?= $s->id ?>" <?= $section_id == $s->id ? 'selected' : '' ?>><?= esc_html($s->name) ?></option>
    <?php endforeach; ?>
</select>
</td>
</tr>

<tr>
<th>Name Bangla</th>
<td><input type="text" name="name" value="<?= $val('name') ?>" required></td>
</tr>

<tr>
<th>Name English</th>
<td><input type="text" name="name_en" value="<?= $val('name_en') ?>" required></td>
</tr>

<tr>
<th>Roll</th>
<td><input type="number" name="roll" value="<?= $val('roll') ?>" required></td>
</tr>

<tr>
<th>Religion</th>
<td>
<select name="religion" required>
    <option value="">Select Religion</option>
    <?php foreach ($religions as $r): ?>
        <option value="<?= $r ?>" <?= ($student->religion ?? '') == $r ? 'selected' : '' ?>><?= $r ?></option>
    <?php endforeach; ?>
</select>
</td>
</tr>

<tr>
<th>Gender</th>
<td>
<select name="gender" required>
    <option value="">Select Gender</option>
    <?php foreach ($genders as $g): ?>
        <option value="<?= $g ?>" <?= ($student->gender ?? '') == $g ? 'selected' : '' ?>><?= $g ?></option>
    <?php endforeach; ?>
</select>
</td>
</tr>

<tr>
<th>Group</th>
<td>
<select name="group">
    <option value="">— None —</option>
    <?php foreach ($groups as $g): ?>
        <option value="<?= esc_attr($g) ?>" <?= (($student->group ?? '') === $g) ? 'selected' : '' ?>>
            <?= esc_html($g) ?>
        </option>
    <?php endforeach; ?>
</select>
<p class="description">Optional</p>
</td>
</tr>

<tr>
<th>Father Name Bangla</th>
<td><input type="text" name="father_name" value="<?= $val('father_name') ?>"></td>
</tr>

<tr>
<th>Father Name English</th>
<td><input type="text" name="father_name_en" value="<?= $val('father_name_en') ?>"></td>
</tr>

<tr>
<th>Mother Name Bangla</th>
<td><input type="text" name="mother_name" value="<?= $val('mother_name') ?>"></td>
</tr>

<tr>
<th>Mother Name English</th>
<td><input type="text" name="mother_name_en" value="<?= $val('mother_name_en') ?>"></td>
</tr>

<tr>
<th>Birth Registration No</th>
<td><input type="text" name="birth_reg_no" value="<?= $val('birth_reg_no') ?>"></td>
</tr>

<tr>
<th>Date of Birth</th>
<td><input type="date" name="dob" value="<?= esc_attr($student->dob ?? '') ?>"></td>
</tr>

<tr>
<th>District</th>
<td>
    <select name="district" id="district" required></select>
</td>
</tr>

<tr>
<th>Upozilla</th>
<td>
    <select name="upozilla" id="upozilla" required></select>
</td>
</tr>

<tr>
<th>Union</th>
<td>
    <select name="union_name" id="union" required></select>
</td>
</tr>

<tr>
<th>Village</th>
<td><input type="text" name="village" value="<?= esc_attr($student->village ?? '') ?>"></td>
</tr>

</table>

<button type="submit" name="srm_save_student" class="button button-primary">
    <?= $is_edit ? 'Update Student' : 'Add Student' ?>
</button>

</form>
<?php else: ?>
<p><em>Please select Class and Section first.</em></p>
<?php endif; ?>

</div>

<script>
const bdLocations = {
    "Satkhira": {
        "Kaligonj": ["Ratanpur", "Dhalbaria","Mathureshpur", "Krishnanagar", "Bishnupur", "Champaphul", "Dakshin Sreepur", "Kushlia", "Nalta", "Tarali", "Bhara Simla","Mautala"],
        "Shyamnagar": ["Kushura","Gangutia"]
    },
    "Rajshahi": {
        "Paba": ["Harian","Hujuri Para"],
        "Godagari": ["Deopara","Matikata"]
    }
};

document.addEventListener("DOMContentLoaded", function () {

    const districtSelect = document.getElementById("district");
    const upozillaSelect = document.getElementById("upozilla");
    const unionSelect    = document.getElementById("union");

    let selectedDistrict = "<?= esc_js($student->district ?? '') ?>";
    let selectedUpozilla = "<?= esc_js($student->upozilla ?? '') ?>";
    let selectedUnion    = "<?= esc_js($student->union_name ?? '') ?>";

    /* ---------- LOAD DISTRICTS ---------- */
    function loadDistricts() {
        districtSelect.innerHTML = '';
        let firstDistrict = Object.keys(bdLocations)[0];

        for (let district in bdLocations) {
            districtSelect.innerHTML += `<option value="${district}">${district}</option>`;
        }

        districtSelect.value = selectedDistrict || firstDistrict;
        loadUpozillas(districtSelect.value);
    }

    /* ---------- LOAD UPOZILLAS ---------- */
    function loadUpozillas(district) {
        upozillaSelect.innerHTML = '';
        let upozillas   = Object.keys(bdLocations[district]);
        let firstUpozilla = upozillas[0];

        upozillas.forEach(u => {
            upozillaSelect.innerHTML += `<option value="${u}">${u}</option>`;
        });

        upozillaSelect.value = selectedUpozilla || firstUpozilla;
        loadUnions(district, upozillaSelect.value);
    }

    /* ---------- LOAD UNIONS ---------- */
    function loadUnions(district, upozilla) {
        unionSelect.innerHTML = '';
        let unions    = bdLocations[district][upozilla];
        let firstUnion = unions[0];

        unions.forEach(u => {
            unionSelect.innerHTML += `<option value="${u}">${u}</option>`;
        });

        unionSelect.value = selectedUnion || firstUnion;
    }

    /* ---------- EVENTS ---------- */
    districtSelect.addEventListener("change", function () {
        selectedDistrict = this.value;
        selectedUpozilla = '';
        selectedUnion    = '';
        loadUpozillas(this.value);
    });

    upozillaSelect.addEventListener("change", function () {
        selectedUpozilla = this.value;
        selectedUnion    = '';
        loadUnions(districtSelect.value, this.value);
    });

    /* ---------- INIT ---------- */
    loadDistricts();
});
</script>