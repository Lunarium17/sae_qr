<?php

namespace App\Support;

class CarreraColor
{
    /**
     * Devuelve un matiz (hue) estable para una carrera.
     * La misma carrera siempre produce el mismo color.
     */
    public static function hue(string $carrera): int
    {
        $carrera = trim(mb_strtolower($carrera));

        if ($carrera === '') {
            return 222;
        }

        return abs(crc32($carrera)) % 360;
    }

    /**
     * Estilo para una insignia solida con el color de la carrera.
     */
    public static function badge(string $carrera): string
    {
        $h = self::hue($carrera);

        return 'background: hsl(' . $h . ', 62%, 42%); color: #fff;';
    }

    /**
     * Estilo suave (fondo tenue + texto del color) para insignias y bordes.
     */
    public static function suave(string $carrera): string
    {
        $h = self::hue($carrera);

        return 'background: hsla(' . $h . ', 62%, 42%, .12); color: hsl(' . $h . ', 62%, 32%); border: 1px solid hsla(' . $h . ', 62%, 42%, .45);';
    }

    /**
     * Color puro de la carrera (para bordes, barras o texto).
     */
    public static function color(string $carrera): string
    {
        return 'hsl(' . self::hue($carrera) . ', 62%, 42%)';
    }
}
