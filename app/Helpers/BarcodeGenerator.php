<?php

namespace App\Helpers;

class BarcodeGenerator
{
    /**
     * Code 128 pattern table (Value 0 to 106)
     */
    protected static array $patterns = [
        '212222', '222122', '222221', '121223', '121322', '131222', '122213', '122312', '132212', '221213',
        '221312', '231212', '112232', '122132', '122231', '113222', '123122', '123221', '223211', '221132',
        '221231', '213212', '223112', '312131', '311222', '321122', '321221', '312212', '322112', '322211',
        '212123', '212321', '232121', '111323', '131123', '131321', '112313', '132113', '132311', '211313',
        '231113', '231311', '112133', '112331', '132131', '113123', '113321', '133121', '313121', '211331',
        '231131', '213113', '213311', '213131', '311123', '311321', '331121', '312113', '312311', '332111',
        '314111', '221411', '431111', '111224', '111422', '121124', '121421', '141122', '141221', '112214',
        '112412', '122114', '122411', '142112', '142211', '241211', '221114', '413111', '241112', '134111',
        '111242', '121142', '121241', '114212', '124112', '124211', '411212', '421112', '421211', '212141',
        '214121', '412121', '111143', '111341', '131141', '114113', '114311', '411113', '411311', '113141',
        '114131', '311141', '411131', '211412', '211214', '211232', '2331112' // 106 is STOP pattern
    ];

    /**
     * Generate an SVG Code 128 barcode.
     *
     * @param string $code
     * @param int $height
     * @param int $widthFactor
     * @param bool $showText
     * @return string Raw SVG markup
     */
    public static function svg(string $code, int $height = 50, int $widthFactor = 2, bool $showText = true): string
    {
        $code = trim($code);
        if ($code === '') {
            $code = '000000';
        }

        // Code 128 Set B start code value is 104
        $values = [104];
        $checksum = 104;

        $len = strlen($code);
        for ($i = 0; $i < $len; $i++) {
            $ascii = ord($code[$i]);
            $val = $ascii >= 32 ? $ascii - 32 : 0;
            $values[] = $val;
            $checksum += $val * ($i + 1);
        }

        $checkVal = $checksum % 103;
        $values[] = $checkVal;
        $values[] = 106; // Stop code

        // Build bars binary string
        $binary = '';
        foreach ($values as $val) {
            $pattern = self::$patterns[$val] ?? self::$patterns[0];
            $isBar = true;
            for ($p = 0; $p < strlen($pattern); $p++) {
                $count = (int)$pattern[$p];
                $binary .= str_repeat($isBar ? '1' : '0', $count);
                $isBar = !$isBar;
            }
        }

        // Render SVG bars
        $totalModules = strlen($binary);
        $quietZone = 10;
        $svgWidth = ($totalModules + ($quietZone * 2)) * $widthFactor;
        $svgHeight = $showText ? $height + 20 : $height;

        $svg = "<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"{$svgWidth}\" height=\"{$svgHeight}\" viewBox=\"0 0 {$svgWidth} {$svgHeight}\">\n";
        $svg .= "  <rect width=\"100%\" height=\"100%\" fill=\"#ffffff\"/>\n";

        $x = $quietZone * $widthFactor;
        for ($b = 0; $b < $totalModules; $b++) {
            if ($binary[$b] === '1') {
                $barW = $widthFactor;
                $svg .= "  <rect x=\"{$x}\" y=\"5\" width=\"{$barW}\" height=\"{$height}\" fill=\"#000000\"/>\n";
            }
            $x += $widthFactor;
        }

        if ($showText) {
            $textY = $height + 17;
            $escaped = htmlspecialchars($code, ENT_QUOTES, 'UTF-8');
            $textX = $svgWidth / 2;
            $svg .= "  <text x=\"{$textX}\" y=\"{$textY}\" text-anchor=\"middle\" font-family=\"monospace\" font-size=\"14\" font-weight=\"bold\" fill=\"#111111\">{$escaped}</text>\n";
        }

        $svg .= "</svg>";

        return $svg;
    }
}

