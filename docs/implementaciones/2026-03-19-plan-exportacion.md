# Plan de Implementación de Exportaciones (PDF y Excel)

**Fecha:** 2026-03-19
**Descripción:** Análisis y estrategia para agregar capacidades de exportación de reportes (PDF/Excel) en todos los módulos clave de la plataforma, habiéndose corregido previamente el conflicto de cabeceras HTTP en el motor PDF.

## 1. Problema de formato (Extensión .pdf faltante)
El error donde el archivo se descargaba con un nombre generado aleatorio sin extensión se debía a la interpretación restrictiva de las cabeceras HTTP en navegadores al recibir un MIME `application/pdf` mediante el encapsulador nativo de `Dompdf`.
**Solución ya aplicada:** 
Se deshabilitó el método nativo `$dompdf->stream()` y se estructuró una salida de buffer manual con la inyección explícita de las cabeceras `Content-Type: application/pdf`, `Content-Disposition: attachment; filename="..."`, `Cache-Control` y `Content-Length`. Esto asegura la descarga estricta de un archivo legible `.pdf`.

## 2. Plan de Expansión de Módulos
La acción requerida es incorporar opciones de exportación dual (PDF/Excel) a las siguientes entidades. Para ello usaremos el `ExportController` y el motor `Dompdf` + plantillas HTML para PDF, y el formato de SpreadsheetML para Excel.

### A. Resultados de la Jornada (Admin y Público)
* **Controlador:** Métodos `resultsPdf($leagueId)` y `resultsExcel($leagueId)`
* **Vistas:** Botones en `app/Views/results/index.php` (Admin) y `app/Views/public/matches.php` (Público).
* **Plantilla PDF:** `app/Views/export/results_pdf.php`
* **Datos:** Fecha, horario, local, goles local, goles visitante, visitante, estado (oficial).

### B. Módulo de Equipos
* **Controlador:** Métodos `teamsPdf()` y `teamsExcel()`
* **Vista:** Botones en `app/Views/teams/index.php`.
* **Plantilla PDF:** `app/Views/export/teams_pdf.php`
* **Datos:** Nombre de Liga, Equipo (Nombre completo), Nombre corto, Año de fundación.

### C. Módulo de Árbitros
* **Controlador:** Métodos `refereesPdf()` y `refereesExcel()`
* **Vista:** Botones en `app/Views/referees/index.php`.
* **Plantilla PDF:** `app/Views/export/referees_pdf.php`
* **Datos:** Nombre, Rol principal, Experiencia/Acreditación.

### D. Partidos Programados del Admin
* **Controlador:** Modificar/reutilizar `matchesPdf()` para que no sea estrictamente ligado al filtro público y soporte exportación maestra (todas las ligas o ligas asignadas).
* **Vista:** Botones directos en `app/Views/matches/index.php`.

## 3. Pasos para la Implementación (En proceso)
1. **Configuración de Rutas:** Anexar los endpoints principales en `core/Router.php` de forma global (`/exportar/equipos/pdf`, etc.).
2. **Generación de Plantillas:** Crear las iteraciones HTML limpias para PDF eliminando la navegación.
3. **Múltiples Nodos XML:** Extender la lógica `builtXml` del Controller para generar columnas y celdas correspondientes a las nuevas tablas SQL de Árbitros, Resultados y Equipos.
4. **Validación:** Comprobar la estampa del CSS nativo (sin frameworks) en las exportaciones para mantener un `enterprise-look` en PDF.

A continuación, iniciaré progresivamente la escritura de estos componentes bajo el estándar del proyecto.
