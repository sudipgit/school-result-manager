<?php

add_filter('wp_nav_menu_objects', 'hide_submenu_for_guest', 10, 2);

function hide_submenu_for_guest($items, $args) {

    if (!is_user_logged_in()) {
        foreach ($items as $key => $item) {
            if (in_array('logged-in-only', $item->classes)) {
                unset($items[$key]);
            }
        }
    }

    return $items;
}
function srm_get_sessions(){
    $sessions=['2024','2025','2026','2027'];
    return $sessions;
}

function srm_check_permission(){
    if (!is_user_logged_in()) {
        return 'Please login to access this page.';
    }
    $user = wp_get_current_user();
    if (!in_array('school_manager', (array) $user->roles) && !current_user_can('manage_options')) {
        return 'You do not have permission to access this page.';
    }
}
function srm_get_teacher_photos(){
    $list=array(
        1  => 'https://rtnb.edu.bd/wp-content/uploads/2023/08/azhar-sir-1.jpg',
        2  => 'https://rtnb.edu.bd/wp-content/uploads/2023/08/azhar-sir-1.jpg',
        3  => 'https://rtnb.edu.bd/wp-content/uploads/2023/08/azhar-sir-1.jpg',
        4  => 'https://rtnb.edu.bd/wp-content/uploads/2026/04/1.-শেখ-শফিকুল-ইসলাম-e1775627902976-1.jpeg',
        5  => 'https://rtnb.edu.bd/wp-content/uploads/2026/04/2.-দেবাশীষ-ঘোষ.jpeg',
        6  => 'https://rtnb.edu.bd/wp-content/uploads/2026/04/10.-বৈশাখী-রানী--768x978.jpeg',
        7  => 'https://rtnb.edu.bd/wp-content/uploads/2023/09/IMG-20230902-WA0000-e1693640888285.jpg',
        8  => 'https://rtnb.edu.bd/wp-content/uploads/2023/08/bakul-sir.jpg',
        9  => 'https://rtnb.edu.bd/wp-content/uploads/2024/11/arafat-sir-e1732155546808-1-1024x1024.jpeg',
        10 => 'https://rtnb.edu.bd/wp-content/uploads/2023/08/received_1005257674229966-e1693460294947-768x768.webp',
        11 => 'https://rtnb.edu.bd/wp-content/uploads/2023/08/jakir-sir.png',
        12 => 'https://rtnb.edu.bd/wp-content/uploads/2023/09/IMG_20230907_103535-scaled-e1694070371599-768x768.jpg',
        13 => 'https://rtnb.edu.bd/wp-content/uploads/2023/09/IMG-20230831-WA0004-e1694277835121.jpg',
        14 => 'https://rtnb.edu.bd/wp-content/uploads/2023/08/FB_IMG_1693288822977-e1693459835484.jpg',
        15 => 'https://rtnb.edu.bd/wp-content/uploads/2023/09/IMG_20230907_103417-scaled-e1694070326232-1024x1024.jpg',
        16 => 'https://rtnb.edu.bd/wp-content/uploads/2023/09/IMG-20230904-WA0001-e1694052772357-290x300.jpg',
        17 => 'https://rtnb.edu.bd/wp-content/uploads/2026/04/19.-সুদীপ-কুমার-সরকার.jpg',
        18 => 'https://rtnb.edu.bd/wp-content/uploads/2023/08/azhar-sir-1.jpg',
        19 => 'https://rtnb.edu.bd/wp-content/uploads/2023/08/achintya-sir-e1693460947285-768x768.png',
        20 => 'https://rtnb.edu.bd/wp-content/uploads/2023/09/fl-300x266.png',
        21 => 'https://rtnb.edu.bd/wp-content/uploads/2026/04/11.-জি-এম-গোলাম-মোস্তফা-224x300.jpeg',
        22 => 'https://rtnb.edu.bd/wp-content/uploads/2023/09/IMG-20230831-WA0001-1017x1024.jpg',
        23 => 'https://rtnb.edu.bd/wp-content/uploads/2023/08/IMG-20230829-WA0001-e1693459927441.jpg',
        24 => 'https://rtnb.edu.bd/wp-content/uploads/2023/09/IMG-20230831-WA0002.jpg',
        
    );

    return $list;
}

function srm_render_marks_table($rows,$subject) {
    ?>
    <table class="marks-table">
        <thead>
            <tr>
                <th>Roll</th>
                <th style="min-width:130px">Student Name</th>
                 <?php if ($subject->mcq_marks > 0): ?><th>MCQ (<?= $subject->mcq_marks ?>)</th><?php endif; ?>
                <?php if ($subject->cq_marks > 0): ?><th>CQ (<?= $subject->cq_marks ?>)</th><?php endif; ?>
                <?php if ($subject->practical_marks > 0): ?><th>Prac (<?= $subject->practical_marks ?>)</th><?php endif; ?>
                <th>Total (<?= $subject->full_marks ?>)</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $mark): ?>
            <tr>
                <td><?= esc_html($mark->roll) ?></td>
                <td class="name"><?= esc_html($mark->student_name) ?></td>
          <?php if ($subject->mcq_marks > 0) {?>
                <td><?= esc_html($mark->mcq) ?></td>
                <?php } ?>

<?php if ($subject->cq_marks > 0) {?>
                <td><?= esc_html($mark->cq) ?></td>
                <?php } ?>
<?php if ($subject->practical_marks > 0) {?>
                <td><?= esc_html($mark->practical) ?></td>
                <?php } ?>


               
                <td><strong ><?= esc_html($mark->total) ?></stronpractical_marksg></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    
    <?php
}

function srm_render_marks_table2($rows, $subject, $group_by_class = false) {
    $class_labels = [
        101 => 'Science',
        102 => 'Commerce',
        103 => 'Humanities',
    ];
    ?>
    <table class="marks-table">
        <thead>
            <tr>
                <th>Roll</th>
                <th style="min-width:130px">Student Name</th>
                <?php if ($subject->mcq_marks > 0): ?><th>MCQ (<?= $subject->mcq_marks ?>)</th><?php endif; ?>
                <?php if ($subject->cq_marks > 0): ?><th>CQ (<?= $subject->cq_marks ?>)</th><?php endif; ?>
                <?php if ($subject->practical_marks > 0): ?><th>Prac (<?= $subject->practical_marks ?>)</th><?php endif; ?>
                <th>Total (<?= $subject->full_marks ?>)</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $current_class = null;
        $col_count = 1 // Roll + Name
            + ($subject->mcq_marks > 0 ? 1 : 0)
            + ($subject->cq_marks > 0 ? 1 : 0)
            + ($subject->practical_marks > 0 ? 1 : 0)
            + 1; // Total

        foreach ($rows as $mark):
            // Print class group title row when class changes
            if ($group_by_class && $mark->class_no !== $current_class):
                $current_class = $mark->class_no;
                $label = $class_labels[$current_class] ?? 'Class ' . $current_class;
                ?>
                <tr class="class-group-title">
                    <td></td>
                    <td colspan="<?= $col_count ?>"><?= esc_html($label) ?></td>
                </tr>
            <?php endif; ?>
            <tr>
                <td><?= esc_html($mark->roll) ?></td>
                <td class="name"><?= esc_html($mark->student_name) ?></td>
                <?php if ($subject->mcq_marks > 0): ?>
                <td><?= esc_html($mark->mcq) ?></td>
                <?php endif; ?>
                <?php if ($subject->cq_marks > 0): ?>
                <td><?= esc_html($mark->cq) ?></td>
                <?php endif; ?>
                <?php if ($subject->practical_marks > 0): ?>
                <td><?= esc_html($mark->practical) ?></td>
                <?php endif; ?>
                <td><strong><?= esc_html($mark->total) ?></strong></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php
}
?>