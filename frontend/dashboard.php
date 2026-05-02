<?php
global $wpdb;
if (!current_user_can('administrator')) {
    echo '<p>Please log in to view your profile.</p>';
    return;
}
$active_session = get_option('srm_active_session'); // e.g., "2025-2026"
$active_exam_id = intval(get_option('srm_active_exam')); // e.g., 3
$exam_name = $wpdb->get_var($wpdb->prepare("SELECT name FROM {$wpdb->prefix}srm_exams WHERE id=%d", $active_exam_id));
$photos = srm_get_teacher_photos();   
?>
<div class="dashboard-layout">
    <h2> <?= esc_html($exam_name)?> - <?= esc_html($active_session) ?></h2>

  <!-- SIDEBAR -->
  <div class="d-sidebar">
    <div class="d-sidebar-logo">R</div>
    <span class="d-sidebar-label">Management</span>
    <ul>
      <li class="active"><a href="https://rtnb.edu.bd/management/dashboard/">Dashboard</a></li>
      <li><a href="https://rtnb.edu.bd/management/dashboard/">Admit Card</a></li>
      <li><a href="https://rtnb.edu.bd/management/seat-plan/">Seat Plan</a></li>
      <li><a href="https://rtnb.edu.bd/management/teacher-list/">Teacher List</a></li>
      <li><a href="https://rtnb.edu.bd/management/teacher-list/">Teacher Marksheet</a></li>
      <li><a href="">Student Marksheet</a></li>
    </ul>
  </div>

  <!-- CONTENT -->
  <div class="d-content">
    <div class="dashboard-wrap">

      <div class="menu-section-label">Management Portal</div>

      <ul class="grid-a">

        <!-- 1. Dashboard -->
         <li>
            <a href="https://rtnb.edu.bd/management/dashboard/">
                <div class="menu-card-body">
                <div class="menu-card-icon">
                    <!-- paste an SVG icon here -->
                </div>
                <div class="menu-card-name">Dashboard</div>
                <div class="menu-card-desc">Overview and quick access</div>
                </div>
                <div class="menu-card-footer">Open →</div>
            </a>
        </li>
         <li>
            <a href="https://rtnb.edu.bd/management/dashboard/">
                <div class="menu-card-body">
                <div class="menu-card-icon">
                    <!-- paste an SVG icon here -->
                </div>
                <div class="menu-card-name">Dashboard</div>
                <div class="menu-card-desc">Overview and quick access</div>
                </div>
                <div class="menu-card-footer">Open →</div>
            </a>
        </li>
         <li>
            <a href="https://rtnb.edu.bd/management/dashboard/">
                <div class="menu-card-body">
                <div class="menu-card-icon">
                    <!-- paste an SVG icon here -->
                </div>
                 <div class="menu-card-name">Admit Card</div>
                 <div class="menu-card-desc">Generate and download student admit cards for the exam</div>
                </div>
                <div class="menu-card-footer">Open →</div>
            </a>
        </li>
        
        <li>
             <a href="https://rtnb.edu.bd/management/seat-plan/">
                <div class="menu-card-body">
                <div class="menu-card-icon">
                    <!-- paste an SVG icon here -->
                </div>
                 <div class="menu-card-name">Seat Plan</div>
              <div class="menu-card-desc">View and manage examination hall seating arrangements</div>
                </div>
                <div class="menu-card-footer">Open →</div>
            </a>
        </li>

       

        <!-- 4. Teacher List -->
        <li>
          <a href="https://rtnb.edu.bd/management/teacher-list/">
            <div class="menu-card-body">
              <div class="menu-card-icon">
               
              </div>
              <div class="menu-card-name">Teacher List</div>
              <div class="menu-card-desc">Browse all teaching staff assigned to this examination</div>
            </div>
            <div class="menu-card-footer">Open →</div>
          </a>
        </li>

        <!-- 5. Teacher Marksheet -->
        <li>
          <a href="https://rtnb.edu.bd/management/teacher-list/">
            <div class="menu-card-body">
              <div class="menu-card-icon">
                
              </div>
              <div class="menu-card-name">Teacher Marksheet</div>
              <div class="menu-card-desc">Download subject-wise mark reports submitted by teachers</div>
            </div>
            <div class="menu-card-footer">Open →</div>
          </a>
        </li>

        <!-- 6. Student Marksheet -->
        <li>
          <a href="">
            <div class="menu-card-body">
              <div class="menu-card-icon">
               
              </div>
              <div class="menu-card-name">Student Marksheet</div>
              <div class="menu-card-desc">Access and print individual student result sheets</div>
            </div>
            <div class="menu-card-footer">Open →</div>
          </a>
        </li>

      </ul>
    </div>
  </div>

</div>

