<?php

declare(strict_types=1);

namespace App\Services;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class QrCodeService
{
    /**
     * Generate QR code as SVG string.
     */
    public function generateSvg(string $content, int $size = 200): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle($size),
            new SvgImageBackEnd
        );

        $writer = new Writer($renderer);

        return $writer->writeString($content);
    }

    /**
     * Generate QR code as base64 data URI.
     */
    public function generateDataUri(string $content, int $size = 200): string
    {
        $svg = $this->generateSvg($content, $size);

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }
}
