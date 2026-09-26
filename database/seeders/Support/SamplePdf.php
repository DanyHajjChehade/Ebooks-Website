<?php

namespace Database\Seeders\Support;

/**
 * Builds a small, valid two-page PDF (title page + blurb) without any
 * dependencies, so demo books have a real file to download.
 */
final class SamplePdf
{
    private const PAGE_WIDTH = 432;   // 6in

    private const PAGE_HEIGHT = 648;  // 9in

    public static function make(string $title, string $author, string $description, ?string $category = null): string
    {
        $titlePage = self::text('F1', 26, 48, 470, self::wrap($title, 26))
            .self::text('F2', 14, 48, 470 - 34 * count(self::wrap($title, 26)) - 10, ['by '.$author])
            .($category ? self::text('F2', 10, 48, 90, [strtoupper($category)]) : '')
            .self::text('F2', 10, 48, 72, ['Book Planet demo edition']);

        $bodyLines = array_merge(
            ['About this book', ''],
            self::wrap($description, 62),
            ['', 'This is a short sample file generated for the Book Planet', 'demo catalogue. Replace it from the admin area with the', 'real PDF or EPUB edition.'],
        );

        $bodyPage = self::text('F2', 11, 48, 580, $bodyLines, 16);

        return self::document([$titlePage, $bodyPage], $title, $author);
    }

    /**
     * @param  list<string>  $pages  content streams
     */
    private static function document(array $pages, string $title, string $author): string
    {
        $objects = [];
        $pageCount = count($pages);
        $firstPageObject = 5;

        $kids = [];
        for ($i = 0; $i < $pageCount; $i++) {
            $kids[] = ($firstPageObject + $i * 2).' 0 R';
        }

        $objects[1] = '<< /Type /Catalog /Pages 2 0 R >>';
        $objects[2] = '<< /Type /Pages /Kids ['.implode(' ', $kids).'] /Count '.$pageCount.' >>';
        $objects[3] = '<< /Type /Font /Subtype /Type1 /BaseFont /Times-Bold /Encoding /WinAnsiEncoding >>';
        $objects[4] = '<< /Type /Font /Subtype /Type1 /BaseFont /Times-Roman /Encoding /WinAnsiEncoding >>';

        foreach ($pages as $i => $stream) {
            $pageObject = $firstPageObject + $i * 2;
            $objects[$pageObject] = sprintf(
                '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 %d %d] /Resources << /Font << /F1 3 0 R /F2 4 0 R >> >> /Contents %d 0 R >>',
                self::PAGE_WIDTH,
                self::PAGE_HEIGHT,
                $pageObject + 1,
            );
            $objects[$pageObject + 1] = '<< /Length '.strlen($stream)." >>\nstream\n".$stream."\nendstream";
        }

        $infoObject = count($objects) + 1;
        $objects[$infoObject] = '<< /Title ('.self::escape($title).') /Author ('.self::escape($author).') /Producer (Book Planet) >>';

        $pdf = "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n";
        $offsets = [];

        foreach ($objects as $number => $body) {
            $offsets[$number] = strlen($pdf);
            $pdf .= $number." 0 obj\n".$body."\nendobj\n";
        }

        $xref = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objects) + 1)."\n0000000000 65535 f \n";

        foreach ($offsets as $offset) {
            $pdf .= sprintf("%010d 00000 n \n", $offset);
        }

        $pdf .= 'trailer << /Size '.(count($objects) + 1).' /Root 1 0 R /Info '.$infoObject." 0 R >>\n";
        $pdf .= "startxref\n".$xref."\n%%EOF\n";

        return $pdf;
    }

    /**
     * @param  list<string>  $lines
     */
    private static function text(string $font, int $size, int $x, int $y, array $lines, ?int $leading = null): string
    {
        $leading ??= (int) round($size * 1.3);
        $out = "BT\n/{$font} {$size} Tf\n{$leading} TL\n{$x} {$y} Td\n";

        foreach ($lines as $line) {
            $out .= '('.self::escape($line).") Tj T*\n";
        }

        return $out."ET\n";
    }

    /**
     * @return list<string>
     */
    private static function wrap(string $text, int $width): array
    {
        return explode("\n", wordwrap(trim($text), $width, "\n", true));
    }

    private static function escape(string $text): string
    {
        $encoded = @iconv('UTF-8', 'Windows-1252//TRANSLIT', $text);

        return str_replace(['\\', '(', ')', "\r", "\n"], ['\\\\', '\\(', '\\)', '', ' '], $encoded === false ? $text : $encoded);
    }
}
