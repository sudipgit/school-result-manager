<?php
use Dompdf\Dompdf;
use Dompdf\Options;

function srm_generate_pdf($html, $filename='result.pdf',$orientation='portrait') {
    require_once SRM_PLUGIN_PATH . 'dompdf/vendor/autoload.php';
    $options = new Options();
    $options->set('isRemoteEnabled', true);
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', $orientation);
    $dompdf->render();
    $dompdf->stream($filename, ['Attachment'=>false]);
    exit;
}
