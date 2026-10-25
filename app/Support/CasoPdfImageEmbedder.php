<?php

namespace App\Support;

use App\Models\CasoDocumento;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class CasoPdfImageEmbedder
{
    /**
     * @param  iterable<CasoDocumento>  $documents
     * @return array<int, string>
     */
    public function embed(iterable $documents): array
    {
        $images = [];

        foreach ($documents as $document) {
            if (! str_starts_with($document->mime_type, 'image/')) {
                continue;
            }

            $contents = Storage::disk('case_files')->get($document->ruta);
            $imageInfo = getimagesizefromstring($contents);

            if ($imageInfo === false || ! in_array($imageInfo['mime'], ['image/jpeg', 'image/png', 'image/webp'], true)) {
                throw new RuntimeException("No se pudo procesar la imagen adjunta: {$document->nombre_original}");
            }

            $mimeType = $imageInfo['mime'];

            if ($mimeType === 'image/webp') {
                $contents = $this->convertWebpToPng($contents, $document->nombre_original);
                $mimeType = 'image/png';
            }

            $images[$document->getKey()] = 'data:'.$mimeType.';base64,'.base64_encode($contents);
        }

        return $images;
    }

    private function convertWebpToPng(string $contents, string $fileName): string
    {
        $image = imagecreatefromstring($contents);

        if ($image === false) {
            throw new RuntimeException("No se pudo convertir la imagen adjunta: {$fileName}");
        }

        ob_start();

        try {
            if (! imagepng($image)) {
                throw new RuntimeException("No se pudo convertir la imagen adjunta: {$fileName}");
            }

            $png = ob_get_contents();

            if ($png === false) {
                throw new RuntimeException("No se pudo convertir la imagen adjunta: {$fileName}");
            }

            return $png;
        } finally {
            ob_end_clean();
            imagedestroy($image);
        }
    }
}
