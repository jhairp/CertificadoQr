<?php

namespace App\Http\Controllers;

use App\Models\Certificado;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;

class VerificacionCertificadoController extends Controller
{
    public function __invoke(string $codigo)
    {
        $certificado = Certificado::query()->where('codigo_cer', $codigo)->first();

        return view('certificados.verificar', compact('certificado'));
    }

    public function qr(string $codigo)
    {
        $certificado = Certificado::query()->where('codigo_cer', $codigo)->firstOrFail();
        $qrCode = new QrCode(data: route('certificados.verificar', $certificado->codigo_cer), size: 300, margin: 10);
        $svg = (new SvgWriter())->write($qrCode)->getString();

        return response($svg, 200, ['Content-Type' => 'image/svg+xml']);
    }
}
