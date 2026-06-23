<?php
if (!current_user_can('edit_posts')) exit;
global $wpdb;

$table = $wpdb->prefix . 'srm_students';
$table_classes  = $wpdb->prefix . 'srm_classes';
$table_sections = $wpdb->prefix . 'srm_sections';

// ---------- CONTEXT ----------
$class      = intval($_POST['class'] ?? $_GET['class'] ?? 0);
$section_id = intval($_POST['section_id'] ?? $_GET['section_id'] ?? 0);

// Fetch classes from DB
$classes = $wpdb->get_results("SELECT * FROM $table_classes WHERE status=1 ORDER BY class_no ASC");

// Fetch sections for selected class
$sections = [];
if ($class) {
    $sections = $wpdb->get_results(
        $wpdb->prepare("SELECT * FROM $table_sections WHERE class_no=%d AND status=1 ORDER BY name ASC", $class)
    );
}

$current_session = get_option('srm_active_session') ?: '2026';
?>

<div class="wrap">
<p>Update Student Names via CSV</p>

<!-- CONTEXT FORM -->
<form method="get" style="margin-bottom:20px;">
    <h4>Class Context</h4>
    <input type="hidden" name="page" value="<?= esc_attr($_GET['page'] ?? '') ?>">

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

<?php
/* ================= CSV ENCODING HELPER ================= */
/**
 * Reads a CSV file from disk, detects its character encoding,
 * converts it to clean UTF-8 if needed, strips BOM, and returns
 * an in-memory stream resource ready for fgetcsv().
 */
function srm_open_csv_as_utf8($filepath) {
    $raw = file_get_contents($filepath);

    if ($raw === false) {
        return false;
    }

    // Strip UTF-8 BOM if present
    $raw = preg_replace('/^\xEF\xBB\xBF/', '', $raw);

    // Detect encoding among common culprits (Excel exports, etc.)
    $encoding = mb_detect_encoding(
        $raw,
        ['UTF-8', 'UTF-16LE', 'UTF-16BE', 'Windows-1252', 'ISO-8859-1'],
        true
    );

    if ($encoding && $encoding !== 'UTF-8') {
        $raw = mb_convert_encoding($raw, 'UTF-8', $encoding);
    }

    // Re-check: if still not valid UTF-8, force-clean it so it doesn't
    // break json_encode() / DB inserts later.
    if (!mb_check_encoding($raw, 'UTF-8')) {
        $raw = mb_convert_encoding($raw, 'UTF-8', 'UTF-8');
    }

    $stream = fopen('php://temp', 'r+');
    fwrite($stream, $raw);
    rewind($stream);

    return $stream;
}

/* ================= CSV PREVIEW ================= */
$preview_rows = [];

if (isset($_POST['preview_csv']) && !empty($_FILES['csv']['tmp_name'])) {

    check_admin_referer('preview_csv');

    if (!$class || !$section_id) {
        echo "<div class='notice notice-error'><p>Select Class and Section first.</p></div>";
    } else {

        $file = srm_open_csv_as_utf8($_FILES['csv']['tmp_name']);

        if ($file === false) {
            echo "<div class='notice notice-error'><p>Could not read the uploaded file.</p></div>";
        } else {

            $header = array_map('trim', fgetcsv($file));
            $indexes = array_flip($header);

            // We only need roll plus the two fields we're allowed to update.
            $required = ['roll'];
            $missing_column = false;

            foreach ($required as $r) {
                if (!isset($indexes[$r])) {
                    echo "<div class='notice notice-error'><p>Missing column: $r</p></div>";
                    $missing_column = true;
                }
            }

            if (!isset($indexes['name']) && !isset($indexes['name_en'])) {
                echo "<div class='notice notice-error'><p>CSV must contain at least a 'name' or 'name_en' column.</p></div>";
                $missing_column = true;
            }

            if (!$missing_column) {

                while (($row = fgetcsv($file)) !== false) {

                    $data = [];
                    foreach ($indexes as $key => $i) {
                        $data[$key] = trim($row[$i] ?? '');
                    }

                    // Skip empty row
                    if (empty($data['roll']) && empty($data['name']) && empty($data['name_en'])) {
                        continue;
                    }

                    // Find matching student: roll + class + section + session
                    $existing = $wpdb->get_row(
                        $wpdb->prepare(
                            "SELECT id, name, name_en FROM $table
                             WHERE class_no=%d AND section_id=%d AND roll=%d AND session=%s",
                            $class,
                            $section_id,
                            intval($data['roll']),
                            $current_session
                        )
                    );

                    $preview_rows[] = [
                        'data'  => $data,
                        'match' => $existing, // null if no match found
                    ];
                }
            }

            fclose($file);
        }
    }
}

/* ================= CSV CONFIRM (UPDATE ONLY) ================= */
if (isset($_POST['confirm_csv']) && !empty($_POST['rows'])) {

    check_admin_referer('confirm_csv');

    $updated = 0;
    $skipped = 0;

    foreach ($_POST['rows'] as $json) {

        $data = json_decode(stripslashes($json), true);

        $existing = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM $table
                 WHERE class_no=%d AND section_id=%d AND roll=%d AND session=%s",
                $class,
                $section_id,
                intval($data['roll']),
                $current_session
            )
        );

        if (!$existing) {
            $skipped++;
            continue;
        }

        $update_data = [];

        if (isset($data['name']) && $data['name'] !== '') {
            $update_data['name'] = sanitize_text_field($data['name']);
        }
        if (isset($data['name_en']) && $data['name_en'] !== '') {
            $update_data['name_en'] = sanitize_text_field($data['name_en']);
        }

        if (empty($update_data)) {
            $skipped++;
            continue;
        }

        $wpdb->update($table, $update_data, ['id' => intval($existing)]);
        $updated++;
    }

    echo "<div class='notice notice-success'>
            <p>Updated: $updated | Skipped (no match or no data): $skipped</p>
          </div>";
}
?>

<?php if ($class && $section_id): ?>
<hr>
<h4>Upload CSV to Update Names</h4>
<p class="description">
    CSV must include a <code>roll</code> column, plus <code>name</code> and/or <code>name_en</code>.
    Rows are matched against existing students by Roll + Class + Section + Session.
    No new students will be created — unmatched rows are skipped.
</p>

<form method="post" enctype="multipart/form-data">
    <?php wp_nonce_field('preview_csv'); ?>
    <input type="hidden" name="class" value="<?= esc_attr($class) ?>">
    <input type="hidden" name="section_id" value="<?= esc_attr($section_id) ?>">
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
        <input type="hidden" name="class" value="<?= esc_attr($class) ?>">
        <input type="hidden" name="section_id" value="<?= esc_attr($section_id) ?>">

        <table class="wp-list-table widefat striped">
            <thead>
                <tr>
                    <th>Roll</th>
                    <th>CSV Name</th>
                    <th>CSV Name (EN)</th>
                    <th>Current Name</th>
                    <th>Current Name (EN)</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>

            <?php foreach ($preview_rows as $row) :
                $matched = $row['match'] !== null;
            ?>
                <tr style="<?= $matched ? '' : 'background:#ffecec;' ?>">
                    <td><?= esc_html($row['data']['roll'] ?? '') ?></td>
                    <td><?= esc_html($row['data']['name'] ?? '') ?></td>
                    <td><?= esc_html($row['data']['name_en'] ?? '') ?></td>
                    <td><?= esc_html($row['match']->name ?? '—') ?></td>
                    <td><?= esc_html($row['match']->name_en ?? '—') ?></td>
                    <td>
                        <?= $matched ? 'Found — Will Update' : 'No Match — Will Skip' ?>
                    </td>
                </tr>

                <?php if ($matched) : ?>
                    <input type="hidden" name="rows[]"
                        value="<?= esc_attr(json_encode($row['data'])) ?>">
                <?php endif; ?>

            <?php endforeach; ?>

            </tbody>
        </table>

        <br>
        <button class="button button-primary" name="confirm_csv">
            Update Matched Students
        </button>

    </form>
<?php endif; ?>

<?php else: ?>
<p><em>Please select Class and Section first.</em></p>
<?php endif; ?>

</div>
