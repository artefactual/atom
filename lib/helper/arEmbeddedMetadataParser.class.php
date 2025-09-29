<?php

/**
 * arEmbeddedMetadataParser — EXIF/XMP/IPTC extractor for AtoM 2.9 (PHP 8.3)
 * - Prefers exiftool, falls back to basic image props
 * - Returns structured array + pretty summary for Physical characteristics.
 */
class arEmbeddedMetadataParser
{
    public static function extract(string $absolutePath): ?array
    {
        if (!is_file($absolutePath) || !is_readable($absolutePath)) {
            return null;
        }

        $meta = null;
        $exiftool = self::which('exiftool');
        if ($exiftool) {
            $cmd = sprintf(
                '%s -j -n %s 2>/dev/null',
                escapeshellcmd($exiftool),
                escapeshellarg($absolutePath)
            );
            $json = @shell_exec($cmd);
            if (is_string($json) && strlen($json) > 2) {
                $arr = json_decode($json, true);
                if (is_array($arr) && isset($arr[0]) && is_array($arr[0])) {
                    $meta = $arr[0];
                }
            }
        }

        if (!$meta) {
            $meta = [];
            $meta['MIMEType'] = function_exists('mime_content_type')
                ? @mime_content_type($absolutePath)
                : null;
            $meta['FileSize'] = @filesize($absolutePath);
            $gs = @getimagesize($absolutePath);
            if (is_array($gs)) {
                $meta['ImageWidth'] = $gs[0] ?? null;
                $meta['ImageHeight'] = $gs[1] ?? null;
            }
        }

        $meta['_norm'] = self::normalize($meta);

        return $meta;
    }

    public static function formatSummary(array $meta): string
    {
        $n =
            isset($meta['_norm']) && is_array($meta['_norm'])
                ? $meta['_norm']
                : [];
        $lines = [];
        $lines[] = 'Technical Metadata:';
        $lines[] = '';
        if (!empty($n['dimensions'])) {
            $lines[] = 'Image Size: '.$n['dimensions'];
        }
        if (!empty($n['dpi'])) {
            $lines[] = 'Resolution (DPI): '.$n['dpi'];
        }
        if (!empty($n['bitDepth'])) {
            $lines[] = 'Bit Depth: '.$n['bitDepth'];
        }
        if (!empty($n['compression'])) {
            $lines[] = 'Compression: '.$n['compression'];
        }
        if (!empty($n['colorModel'])) {
            $lines[] = 'Color Model: '.$n['colorModel'];
        }
        if (!empty($meta['FileSize'])) {
            $lines[] = 'File Size: '.self::fmtBytes($meta['FileSize']);
        }
        if (!empty($meta['MIMEType'])) {
            $lines[] = 'MIME Type: '.$meta['MIMEType'];
        }

        $desc = [];
        if (!empty($n['title'])) {
            $desc[] = 'Title: '.$n['title'];
        }
        if (!empty($n['creator'])) {
            $desc[] = 'Creator: '.$n['creator'];
        }
        if (!empty($n['description'])) {
            $desc[] = 'Description: '.$n['description'];
        }
        if (!empty($n['createDate'])) {
            $desc[] = 'Created: '.$n['createDate'];
        }
        if (!empty($n['software'])) {
            $desc[] = 'Software: '.$n['software'];
        }
        if (!empty($n['rights'])) {
            $desc[] = 'Rights: '.$n['rights'];
        }

        if ($desc) {
            $lines[] = '';
            $lines[] = 'Embedded Metadata:';
            foreach ($desc as $d) {
                $lines[] = $d;
            }
        }

        return trim(implode("\n", $lines));
    }

    public static function applySummaryToInformationObject(
        QubitInformationObject $io,
        string $summary
    ): void {
        if ('' === $summary) {
            return;
        }
        $existing = (string) $io->physicalCharacteristics;

        // Remove any previous "Technical Metadata:" block (replace on re-upload/edit)
        $clean = preg_replace('/\n?Technical Metadata:.*\z/s', '', $existing);
        $clean = rtrim((string) $clean);

        $io->physicalCharacteristics = $clean
            ? $clean."\n\n".$summary
            : $summary;
        $io->save();
    }

    private static function which(string $bin): ?string
    {
        $out = @shell_exec(
            'command -v '.escapeshellarg($bin).' 2>/dev/null'
        );
        $path = is_string($out) ? trim($out) : '';

        return $path && is_executable($path) ? $path : null;
    }

    private static function fmtBytes($b): string
    {
        if (!is_numeric($b)) {
            return (string) $b;
        }
        $u = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;
        while ($b >= 1024 && $i < count($u) - 1) {
            $b /= 1024;
            ++$i;
        }

        return sprintf('%.1f %s', $b, $u[$i]);
    }

    private static function normalize(array $m): array
    {
        $n = [];
        $w = $m['ImageWidth'] ?? ($m['ExifImageWidth'] ?? null);
        $h = $m['ImageHeight'] ?? ($m['ExifImageHeight'] ?? null);
        if ($w && $h) {
            $n['dimensions'] = "{$w} x {$h} pixels";
        }

        $xdpi = $m['XResolution'] ?? ($m['XResolutionDPI'] ?? null);
        $ydpi = $m['YResolution'] ?? ($m['YResolutionDPI'] ?? null);
        if ($xdpi && $ydpi) {
            $n['dpi'] = "{$xdpi} x {$ydpi}";
        } elseif ($xdpi) {
            $n['dpi'] = (string) $xdpi;
        }

        $n['bitDepth'] = $m['BitsPerSample'] ?? ($m['BitDepth'] ?? null);
        $n['compression'] = $m['Compression'] ?? null;
        $n['colorModel'] =
            $m['PhotometricInterpretation'] ?? ($m['ColorSpace'] ?? null);
        $n['software'] = $m['Software'] ?? null;
        $n['createDate'] =
            $m['DateTimeOriginal'] ??
            ($m['CreateDate'] ?? ($m['ModifyDate'] ?? null));

        $n['title'] = self::firstNonEmpty([
            self::collapseLangAlt($m['XMP-dc:Title'] ?? null),
            $m['Title'] ?? null,
        ]);
        $n['description'] = self::firstNonEmpty([
            self::collapseLangAlt($m['XMP-dc:Description'] ?? null),
            $m['ImageDescription'] ?? null,
            $m['Description'] ?? null,
        ]);
        $n['creator'] = self::firstNonEmpty([
            self::collapseArray($m['XMP-dc:Creator'] ?? null),
            $m['Artist'] ?? null,
            $m['By-line'] ?? null,
        ]);
        $n['rights'] = self::firstNonEmpty([
            self::collapseLangAlt($m['XMP-dc:Rights'] ?? null),
            $m['Rights'] ?? null,
            $m['Copyright'] ?? null,
        ]);

        return array_filter($n, fn ($v) => null !== $v && '' !== $v);
    }

    private static function firstNonEmpty(array $c): ?string
    {
        foreach ($c as $v) {
            if (is_string($v) && '' !== trim($v)) {
                return trim($v);
            }
        }

        return null;
    }

    private static function collapseLangAlt($v): ?string
    {
        if (is_string($v)) {
            return $v;
        }
        if (is_array($v)) {
            foreach (['x-default', 'en', 'en-ZA', 'en-US'] as $k) {
                if (isset($v[$k]) && is_string($v[$k]) && '' !== trim($v[$k])) {
                    return trim($v[$k]);
                }
            }
            foreach ($v as $vv) {
                if (is_string($vv) && '' !== trim($vv)) {
                    return trim($vv);
                }
            }
        }

        return null;
    }

    private static function collapseArray($v): ?string
    {
        if (is_string($v)) {
            return $v;
        }
        if (is_array($v)) {
            $flat = array_filter(
                array_map(fn ($x) => is_string($x) ? trim($x) : '', $v)
            );
            if ($flat) {
                return implode(', ', $flat);
            }
        }

        return null;
    }
}
