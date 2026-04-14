# Exportación de PDF con Dompdf

**Fecha:** 2026-03-18
**Descripción:** Se ha reemplazado la exportación basada en vista de impresión del navegador por una exportación directa a un archivo PDF (Moderno y Profesional) utilizando la librería Dompdf.

## Archivos afectados:
- `app/Controllers/ExportController.php`: Se modificaron los métodos `standingsPdf` y `matchesPdf` para bufferizar el HTML generado y procesarlo a través de la clase `Dompdf\Dompdf`, transmitiendo un archivo con las cabeceras requeridas. Se incluyó el helper privado `generatePdf`.
- `app/Views/export/standings_pdf.php`: Se adaptó la vista para servir puramente como recurso interno del renderizador de PDF (se removieron emojis, botones de impresión y `window.print();`).
- `app/Views/export/matches_pdf.php`: Igual que el documento de posiciones, optimizando el formato HTML para la correcta lectura por el motor Dompdf.
- `vendor/dompdf/`: Sistema de directorios para la librería externa.

## Solución Aplicada:
Se instaló standalone Dompdf descargándolo de los GitHub releases (v3.1.5). Este motor procesa los HTML directamente en el servidor utilizando internamente `autoload.inc.php` sin requerir composer activo en la máquina local o configuración extra del servidor, resultando en un archivo PDF estructurado para descarga como `Attachment`.
