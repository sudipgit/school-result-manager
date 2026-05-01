<?php
function srm_calculate_gpa($marks) {
    if ($marks >= 80) return 5.00;
    if ($marks >= 70) return 4.00;
    if ($marks >= 60) return 3.50;
    if ($marks >= 50) return 3.00;
    if ($marks >= 40) return 2.00;
    if ($marks >= 33) return 1.00;
    return 0.00;
}
