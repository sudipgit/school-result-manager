<?php
if (!current_user_can('edit_posts')) exit;

global $wpdb;

$table_students = $wpdb->prefix . 'srm_students';
$table_classes  = $wpdb->prefix . 'srm_classes';
$table_sections = $wpdb->prefix . 'srm_sections';

/* ---------- CURRENT SESSION ---------- */
$current_session = get_option('srm_active_session') ?: '2026';

/* ---------- DELETE ---------- */
if (isset($_GET['delete']) && intval($_GET['delete'])) {
    $wpdb->delete($table_students, ['id' => intval($_GET['delete'])]);
    echo '<div class="notice notice-success is-dismissible"><p>Student deleted successfully.</p></div>';
}

/* ---------- SORTING ---------- */
$allowed_orderby = ['id','name','roll','class_no','session'];
$orderby = $_GET['orderby'] ?? 'roll';
$orderby = in_array($orderby, $allowed_orderby) ? $orderby : 'roll';

$order = strtoupper($_GET['order'] ?? 'ASC');
$order = $order === 'DESC' ? 'DESC' : 'ASC';

function srm_sort_link($label, $column) {
    $order = ($_GET['order'] ?? 'ASC') === 'ASC' ? 'DESC' : 'ASC';
    $query = array_merge($_GET, [
        'orderby' => $column,
        'order'   => $order,
        'paged'   => 1
    ]);
    return '<a href="?' . esc_attr(http_build_query($query)) . '">' . esc_html($label) . '</a>';
}

/* ---------- FILTER INPUT ---------- */
$class   = intval($_GET['class'] ?? 0);
$section = sanitize_text_field($_GET['section'] ?? '');
$gender  = sanitize_text_field($_GET['gender'] ?? '');

/* ---------- FETCH CLASSES ---------- */
$classes = $wpdb->get_results(
    "SELECT class_no, name FROM $table_classes WHERE status=1 ORDER BY class_no ASC"
);

/* ---------- FETCH SECTIONS (AFTER CLASS) ---------- */
$sections = [];
if ($class) {
    $sections = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT id, name FROM $table_sections WHERE class_no=%d AND status=1 ORDER BY name ASC",
            $class
        )
    );
}

/* ---------- WHERE CLAUSE ---------- */
$where  = "WHERE session = %s";
$params = [$current_session];

if ($class) {
    $where .= " AND class_no = %d";
    $params[] = $class;
}

if ($section) {
    $where .= " AND section = %s";
    $params[] = $section;
}

if ($gender) {
    $where .= " AND gender = %s";
    $params[] = $gender;
}

/* ---------- PAGINATION ---------- */
$per_page = 50;
$paged  = max(1, intval($_GET['paged'] ?? 1));
$offset = ($paged - 1) * $per_page;

$total = $wpdb->get_var(
    $wpdb->prepare("SELECT COUNT(*) FROM $table_students $where", ...$params)
);

$total_pages = ceil($total / $per_page);

/* ---------- FETCH STUDENTS ---------- */
$students = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT * FROM $table_students $where 
         ORDER BY $orderby $order 
         LIMIT %d OFFSET %d",
        ...array_merge($params, [$per_page, $offset])
    )
);
?>

<div class="wrap">
<h1>Student List (Session <?= esc_html($current_session) ?>)</h1>

<!-- FILTER FORM -->
<form method="get" style="margin-bottom:15px;">
    <input type="hidden" name="page" value="srm_student_list">

    <!-- CLASS -->
    <select name="class" onchange="this.form.submit()">
        <option value="">All Classes</option>
        <?php foreach ($classes as $c): ?>
            <option value="<?= esc_attr($c->class_no) ?>"
                <?= selected($class, $c->class_no) ?>>
                <?= esc_html($c->name) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <!-- SECTION -->
    <select name="section" onchange="this.form.submit()" <?= !$class ? 'disabled' : '' ?>>
        <option value="">All Sections</option>
        <?php foreach ($sections as $s): ?>
            <option value="<?= esc_attr($s->name) ?>"
                <?= selected($section, $s->name) ?>>
                <?= esc_html($s->name) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <!-- GENDER -->
    <select name="gender" onchange="this.form.submit()">
        <option value="">All Genders</option>
        <option value="Male" <?= selected($gender,'Male') ?>>Male</option>
        <option value="Female" <?= selected($gender,'Female') ?>>Female</option>
    </select>

    <a href="<?= admin_url('admin.php?page=srm_student_list') ?>" class="button">
        Reset
    </a>
</form>

<!-- STUDENT TABLE -->
<table class="wp-list-table widefat fixed striped">
<thead>
<tr>
    <th>ID</th>
    <th><?= srm_sort_link('Name','name') ?></th>
    <th><?= srm_sort_link('Roll','roll') ?></th>
    <th><?= srm_sort_link('Class','class_no') ?></th>
    <th>Section</th>
    <th>Group</th>
    <th><?= srm_sort_link('Session','session') ?></th>
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
    <td><?= esc_html($s->class_no) ?></td>
    <td><?= esc_html($s->section) ?></td>
    <td><?= esc_html($s->group ?? '-') ?></td>
    <td><?= esc_html($s->session) ?></td>
    <td><?= esc_html($s->gender) ?></td>
    <td>
        <a href="<?= admin_url('admin.php?page=srm_add_student&edit='.$s->id) ?>">Edit</a> |
        <a href="<?= admin_url('admin.php?page=srm_student_list&delete='.$s->id) ?>"
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
<div class="tablenav bottom">
    <div class="tablenav-pages">
        <?php
        echo paginate_links([
            'base'      => add_query_arg('paged','%#%'),
            'format'    => '',
            'current'   => $paged,
            'total'     => $total_pages,
            'prev_text' => '«',
            'next_text' => '»',
        ]);
        ?>
    </div>
</div>
<?php endif; ?>

</div>