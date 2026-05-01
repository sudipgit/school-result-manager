<?php
if (!current_user_can('manage_options')) exit;

global $wpdb;

$students_table = $wpdb->prefix . 'srm_students';
$marks_table    = $wpdb->prefix . 'srm_marks';
$subjects_table = $wpdb->prefix . 'srm_subjects';
$classes_table  = $wpdb->prefix . 'srm_classes';

$session = '2026';   // later from settings
$exam_id = 3;        // later from settings

/* ================= PAIRED SUBJECTS ================= */
/*
 Bangla 1st (1) + Bangla 2nd (5)
 English 1st (2) + English 2nd (3)
*/
$paired_subjects = [
    [1, 5],
    [2, 3]
];

/* ================= FETCH CLASSES ================= */
$classes = $wpdb->get_results("SELECT * FROM $classes_table ORDER BY class_no");
$class_no = intval($_GET['class'] ?? 0);
$r=srm_get_marks($exam_id, $session, $class_no);

/* ================= FETCH DATA ================= */
if ($class_no) {
    $rows = $wpdb->get_results(
        $wpdb->prepare("
            SELECT
                st.id AS student_id,
                st.roll,
                st.name AS student_name,

                sub.id AS subject_id,
                sub.name AS subject_name,
                sub.full_marks,
                sub.pass_marks,
                sub.mcq_marks,
                sub.cq_marks,
                sub.practical_marks,

                m.mcq,
                m.cq,
                m.practical

            FROM $students_table st
            LEFT JOIN $marks_table m 
                ON m.student_id = st.id
               AND m.exam_id = %d
               AND m.session = %s
            LEFT JOIN $subjects_table sub
                ON sub.id = m.subject_id
            WHERE st.class_no = %d
              AND st.session = %s
            ORDER BY st.roll ASC
        ",
        $exam_id, $session, $class_no, $session
        )
    );
}
?>

<div class="wrap">
<h1>Tabulation Sheet</h1>

<form method="get">
    <input type="hidden" name="page" value="srm_tabulation">
    <select name="class" required>
        <option value="">Select Class</option>
        <?php foreach ($classes as $c): ?>
            <option value="<?= $c->class_no ?>" <?= $class_no==$c->class_no?'selected':'' ?>>
                <?= esc_html($c->name) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <button class="button button-primary">Generate</button>
</form>

<?php if ($class_no && $rows): ?>

<?php
/* ================= BUILD STRUCTURE ================= */
//var_dump($rows);
$subjects = [];
$students = [];

/* build pair map */
$pair_map = [];
foreach ($paired_subjects as $pair) {
    foreach ($pair as $sid) {
        $pair_map[$sid] = $pair;
    }
}

foreach ($rows as $r) {

    if (!isset($subjects[$r->subject_id])) {
        $subjects[$r->subject_id] = [
            'name' => $r->subject_name,
            'mcq'  => $r->mcq_marks > 0,
            'cq'   => $r->cq_marks > 0,
            'prac' => $r->practical_marks > 0,
            'full' => $r->full_marks,
            'pass' => $r->pass_marks
        ];
    }

    $mcq  = intval($r->mcq);
    $cq   = intval($r->cq);
    $prac = intval($r->practical);
    $total = $mcq + $cq + $prac;

    $students[$r->student_id]['roll'] = $r->roll;
    $students[$r->student_id]['name'] = $r->student_name;

    $students[$r->student_id]['marks'][$r->subject_id] = [
        'mcq'   => $mcq,
        'cq'    => $cq,
        'prac'  => $prac,
        'total' => $total,
        'gpa'   => srm_grade_point($total, $r->full_marks)
    ];
}

/* ================= FINAL GPA WITH PAIR LOGIC ================= */

foreach ($students as &$s) {

    $sum = 0;
    $count = 0;
    $fail = false;
    $checked_pairs = [];

    foreach ($subjects as $sid => $sub) {

        /* ===== PAIRED SUBJECT ===== */
        if (isset($pair_map[$sid])) {

            $pair_key = implode('_', $pair_map[$sid]);
            if (isset($checked_pairs[$pair_key])) continue;

            $pair_total = 0;
            $pair_pass  = 0;
            $pair_gp    = 0;

            foreach ($pair_map[$sid] as $psid) {
                $pair_total += $s['marks'][$psid]['total'] ?? 0;
                $pair_pass  += $subjects[$psid]['pass'];
                $pair_gp    += $s['marks'][$psid]['gpa'] ?? 0;
            }

            if ($pair_total < $pair_pass) {
                $fail = true;
            }

            $sum += round($pair_gp / count($pair_map[$sid]), 2);
            $count++;

            $checked_pairs[$pair_key] = true;
        }

        /* ===== SINGLE SUBJECT ===== */
        else {
            $gp = $s['marks'][$sid]['gpa'] ?? 0;
            if ($gp == 0) $fail = true;
            $sum += $gp;
            $count++;
        }
    }

    $s['gpa'] = $fail ? 0.00 : round($sum / $count, 2);
}
?>

<hr>

<table class="wp-list-table widefat striped">
<thead>
<tr>
    <th rowspan="2">Roll</th>
    <th rowspan="2">Name</th>

    <?php foreach ($subjects as $sub):
        $cols = ($sub['mcq']?1:0) + ($sub['cq']?1:0) + ($sub['prac']?1:0) + 2;
    ?>
        <th colspan="<?= $cols ?>"><?= esc_html($sub['name']) ?></th>
    <?php endforeach; ?>

    <th rowspan="2">GPA</th>
</tr>

<tr>
<?php foreach ($subjects as $sub): ?>
    <?php if ($sub['mcq']): ?><th>MCQ</th><?php endif; ?>
    <?php if ($sub['cq']): ?><th>CQ</th><?php endif; ?>
    <?php if ($sub['prac']): ?><th>PRAC</th><?php endif; ?>
    <th>T</th>
    <th>GP</th>
<?php endforeach; ?>
</tr>
</thead>

<tbody>
<?php foreach ($students as $s): ?>
<tr>
    <td><?= esc_html($s['roll']) ?></td>
    <td><?= esc_html($s['name']) ?></td>

    <?php foreach ($subjects as $sid => $sub):
        $m = $s['marks'][$sid] ?? ['mcq'=>0,'cq'=>0,'prac'=>0,'total'=>0,'gpa'=>0];
    ?>
        <?php if ($sub['mcq']): ?><td><?= $m['mcq'] ?></td><?php endif; ?>
        <?php if ($sub['cq']): ?><td><?= $m['cq'] ?></td><?php endif; ?>
        <?php if ($sub['prac']): ?><td><?= $m['prac'] ?></td><?php endif; ?>
        <td><strong><?= $m['total'] ?></strong></td>
        <td><?= number_format($m['gpa'],2) ?></td>
    <?php endforeach; ?>

    <td><strong><?= number_format($s['gpa'],2) ?></strong></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

<?php endif; ?>
</div>
