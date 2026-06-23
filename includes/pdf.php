<?php
use Dompdf\Dompdf;
use Dompdf\Options;

/*function srm_generate_pdf($html, $filename='result.pdf',$orientation='portrait') {
    require_once SRM_PLUGIN_PATH . 'dompdf/vendor/autoload.php';
    $options = new Options();
    $options->set('isRemoteEnabled', true);
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', $orientation);
    $dompdf->render();
    $dompdf->stream($filename, ['Attachment'=>false]);
    exit;
}*/

function srm_generate_pdf($html, $filename='result.pdf', $orientation='portrait') {
    require_once SRM_PLUGIN_PATH . 'dompdf/vendor/autoload.php';

    //use Dompdf\Dompdf; // ⚠️ this line will actually cause an error here — see note below
    //use Dompdf\Options;

    $options = new Options();
    $options->set('isRemoteEnabled', true);
    $options->set('fontDir', SRM_PLUGIN_PATH . 'dompdf/fonts/');
    $options->set('fontCache', SRM_PLUGIN_PATH . 'dompdf/fonts/');
    $options->set('chroot', SRM_PLUGIN_PATH);

    $dompdf = new Dompdf($options);

    $fontMetrics = $dompdf->getFontMetrics();
    $fontMetrics->registerFont(
        ['family' => 'Nikosh', 'style' => 'normal', 'weight' => 'normal'],
        SRM_PLUGIN_PATH . 'dompdf/fonts/Nikosh.ttf'
    );

    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', $orientation);
    $dompdf->render();
    $dompdf->stream($filename, ['Attachment'=>false]);
    exit;
}