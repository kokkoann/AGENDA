<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/session/FechaUtils.php';

class FechaUtilsTest extends TestCase
{
    public function testFormatFechaDevuelveFormatoCorrecto()
    {
        $fecha = "2025-08-31";
        $resultado = formatFecha($fecha);

        $this->assertStringContainsString("DOMINGO 31 DE AGOSTO DE 2025", $resultado);
    }

    public function testFormatFechaConOtraFecha()
    {
        $fecha = "2025-01-02";
        $resultado = formatFecha($fecha);

        $this->assertEquals("JUEVES 02 DE ENERO DE 2025", $resultado);
    }
}