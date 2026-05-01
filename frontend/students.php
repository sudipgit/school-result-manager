<?php
if (!current_user_can('manage_options')) exit;

global $wpdb;
$table = $wpdb->prefix . 'srm_students';

/* ---------- DELETE ---------- */
if (isset($_GET['delete']) && intval($_GET['delete'])) {
    $wpdb->delete($table, ['id' => intval($_GET['delete'])]);
    echo '<div class="notice notice-success is-dismissible"><p>Student deleted successfully.</p></div>';
}

/* ---------- SORTING ---------- */
$allowed_orderby = ['id','name','roll','class','session'];
$orderby = $_GET['orderby'] ?? 'id';
$orderby = in_array($orderby, $allowed_orderby) ? $orderby : 'id';

$order = strtoupper($_GET['order'] ?? 'DESC');
$order = $order === 'ASC' ? 'ASC' : 'DESC';

/* ---------- SORT LINK ---------- */
function srm_sort_link($label, $column) {
    $order = ($_GET['order'] ?? 'ASC') === 'ASC' ? 'DESC' : 'ASC';
    $query = array_merge($_GET, [
        'orderby' => $column,
        'order'   => $order,
        'paged'   => 1
    ]);
    return '<a href="?' . esc_attr(http_build_query($query)) . '">' . esc_html($label) . '</a>';
}

/* ---------- FILTER ---------- */
$where = "WHERE 1=1";

if (!empty($_GET['class'])) {
    $where .= $wpdb->prepare(" AND class=%s", $_GET['class']);
}
if (!empty($_GET['section'])) {
    $where .= $wpdb->prepare(" AND section=%s", $_GET['section']);
}
if (!empty($_GET['session'])) {
    $where .= $wpdb->prepare(" AND session=%s", $_GET['session']);
}
if (!empty($_GET['gender'])) {
    $where .= $wpdb->prepare(" AND gender=%s", $_GET['gender']);
}

/* ---------- PAGINATION ---------- */
$per_page = 50;
$paged = max(1, intval($_GET['paged'] ?? 1));
$offset = ($paged - 1) * $per_page;

$total = $wpdb->get_var("SELECT COUNT(*) FROM $table $where");
$total_pages = ceil($total / $per_page);

/* ---------- FETCH STUDENTS ---------- */
$students = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT * FROM $table $where ORDER BY $orderby $order LIMIT %d OFFSET %d",
        $per_page,
        $offset
    )
);



/* ================= CSV PREVIEW ================= */
$preview_rows = [];
if (isset($_POST['preview_csv']) && !empty($_FILES['csv']['tmp_name'])) {
    check_admin_referer('preview_csv');

    $file = fopen($_FILES['csv']['tmp_name'], 'r');
    $header = array_map('trim', fgetcsv($file));
    $indexes = array_flip($header);

    $required = ['name','roll','class','section','session','gender'];

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

        /* DUPLICATE CHECK (CSV ONLY) */
        $exists = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM $table WHERE class=%s AND roll=%s AND session=%s",
                $data['class'], $data['roll'], $data['session']
            )
        );

        $preview_rows[] = [
            'data' => $data,
            'duplicate' => $exists ? true : false
        ];
    }
    fclose($file);
}

/* ================= CSV CONFIRM ================= */
if (isset($_POST['confirm_csv']) && !empty($_POST['rows'])) {
    check_admin_referer('confirm_csv');
    $inserted = 0;
    $skipped = 0;

    foreach ($_POST['rows'] as $json) {
        $data = json_decode(stripslashes($json), true);

        $exists = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM $table WHERE class=%s AND roll=%s AND session=%s",
                $data['class'], $data['roll'], $data['session']
            )
        );

        if ($exists) {
            $skipped++;
            continue;
        }

        $wpdb->insert($table, [
            'name' => sanitize_text_field($data['name']),
            'roll' => sanitize_text_field($data['roll']),
            'class' => sanitize_text_field($data['class']),
            'section' => sanitize_text_field($data['section']),
            'session' => sanitize_text_field($data['session']),
            'gender' => sanitize_text_field($data['gender']),
            'group' => sanitize_text_field($data['group'] ?? ''),
            'father_name' => sanitize_text_field($data['father_name'] ?? ''),
            'mother_name' => sanitize_text_field($data['mother_name'] ?? ''),
            'address' => sanitize_textarea_field($data['address'] ?? ''),
            'birth_reg_no' => sanitize_text_field($data['birth_reg_no'] ?? ''),
        ]);
        $inserted++;
    }

    echo "<div class='notice notice-success is-dismissible'>
            <p>Imported: $inserted | Skipped duplicates: $skipped</p>
          </div>";
}


/* ---------- FILTER OPTIONS ---------- */
$classes  = ['6'=>'Six','7'=>'Seven','8'=>'Eight','9'=>'Nine','10'=>'Ten'];
$sections = ['A','B','C'];
$sessions = ['2024','2025','2026'];
$genders  = ['Male','Female','Other'];
?>

<div class="wrap">
<h1>Student List</h1>

<!-- CSV UPLOAD -->
<form method="post" enctype="multipart/form-data">
    <?php wp_nonce_field('preview_csv'); ?>
    <input type="file" name="csv" accept=".csv" required>
    <button class="button button-primary" name="preview_csv">Preview CSV</button>
</form>

<!-- CSV PREVIEW -->
<?php if ($preview_rows): ?>
<form method="post">
<?php wp_nonce_field('confirm_csv'); ?>
<table class="wp-list-table widefat striped">
<thead>
<tr>
    <th>Name</th><th>Roll</th><th>Class</th><th>Session</th><th>Status</th>
</tr>
</thead>
<tbody>
<?php foreach ($preview_rows as $r): ?>
<tr style="<?= $r['duplicate'] ? 'background:#ffecec;' : '' ?>">
    <td><?= esc_html($r['data']['name']) ?></td>
    <td><?= esc_html($r['data']['roll']) ?></td>
    <td><?= esc_html($r['data']['class']) ?></td>
    <td><?= esc_html($r['data']['session']) ?></td>
    <td><?= $r['duplicate'] ? 'Duplicate – Skipped' : 'Ready' ?></td>
</tr>
<?php if (!$r['duplicate']): ?>
<input type="hidden" name="rows[]" value="<?= esc_attr(json_encode($r['data'])) ?>">
<?php endif; ?>
<?php endforeach; ?>
</tbody>
</table>
<button class="button button-primary" name="confirm_csv">Import Valid Students</button>
</form>
<?php endif; ?>
<!-- FILTER FORM -->
<form method="get" style="margin:15px 0; padding:10px; background:#fff; border:1px solid #ccd0d4;">
    <input type="hidden" name="page" value="srm_student_list">
    <input type="hidden" name="orderby" value="<?= esc_attr($orderby) ?>">
    <input type="hidden" name="order" value="<?= esc_attr($order) ?>">

    <select name="class">
        <option value="">All Classes</option>
        <?php foreach ($classes as $v=>$l): ?>
            <option value="<?= esc_attr($v) ?>" <?= ($_GET['class'] ?? '')==$v?'selected':'' ?>>
                <?= esc_html($l) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <select name="section">
        <option value="">All Sections</option>
        <?php foreach ($sections as $sec): ?>
            <option value="<?= esc_attr($sec) ?>" <?= ($_GET['section'] ?? '')==$sec?'selected':'' ?>>
                <?= esc_html($sec) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <select name="session">
        <option value="">All Sessions</option>
        <?php foreach ($sessions as $sess): ?>
            <option value="<?= esc_attr($sess) ?>" <?= ($_GET['session'] ?? '')==$sess?'selected':'' ?>>
                <?= esc_html($sess) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <select name="gender">
        <option value="">All Genders</option>
        <?php foreach ($genders as $g): ?>
            <option value="<?= esc_attr($g) ?>" <?= ($_GET['gender'] ?? '')==$g?'selected':'' ?>>
                <?= esc_html($g) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <button class="button button-primary">Filter</button>
    <a href="<?= admin_url('admin.php?page=srm_student_list') ?>" class="button">Reset</a>
</form>

<!-- STUDENT TABLE -->
<table class="wp-list-table widefat fixed striped">
<thead>
<tr>
    <th>ID</th>
    <th><?= srm_sort_link('Name', 'name') ?></th>
    <th><?= srm_sort_link('Roll', 'roll') ?></th>
    <th><?= srm_sort_link('Class', 'class') ?></th>
    <th>Section</th>
    <th>Group</th>
    <th><?= srm_sort_link('Session', 'session') ?></th>
    <th>Gender</th>
    <th>Actions</th>
</tr>
</thead>
<tbody>

<?php if ($students): foreach ($students as $s): ?>
<tr>
    <td><?= esc_html($s->id) ?></td>
    <td><?= esc_html($s->name) ?></td>
    <td><?= esc_html($s->roll) ?></td>
    <td><?= esc_html($s->class) ?></td>
    <td><?= esc_html($s->section) ?></td>
    <td><?= esc_html($s->group ?? '-') ?></td>
    <td><?= esc_html($s->session) ?></td>
    <td><?= esc_html($s->gender) ?></td>
    <td>
        <a href="<?= admin_url('admin.php?page=srm_add_student&edit=' . $s->id) ?>">Edit</a> |
        <a href="<?= admin_url('admin.php?page=srm_student_list&delete=' . $s->id) ?>"
           onclick="return confirm('Delete this student?')">
           Delete
        </a>
    </td>
</tr>
<?php endforeach; else: ?>
<tr>
    <td colspan="9" style="text-align:center;">No students found.</td>
</tr>
<?php endif; ?>

</tbody>
</table>

<!-- PAGINATION -->
<?php if ($total_pages > 1): ?>
<div style="margin-top:15px;">
<?php
echo paginate_links([
    'base'    => add_query_arg(array_merge($_GET, ['paged'=>'%#%'])),
    'format'  => '',
    'current' => $paged,
    'total'   => $total_pages
]);
?>
</div>
<?php endif; ?>

</div>
