# Plan de Sorteos y Fixture Dinámico (Fase 2)

> **Fecha:** 2026-03-20
> **Módulo:** Sorteos y Asignación de Calendario

## 📖 Entendimiento del Objetivo
El objetivo es transformar el módulo de sorteos y calendario actual (donde todos los partidos se generan y asignan inmediatamente a una fecha/hora/estadio calculada robóticamente) por un **sistema en dos etapas mucho más realista para torneos amateur/profesionales:**

1. **Sorteo Total del Campeonato (El Fixture Base):**
   - Sirve para establecer de inicio el "Todos contra Todos".
   - Genera qué equipo juega contra quién en la "FECHA 1", "FECHA 2", etc.
   - Estos partidos nacen en un estado **"Por definir"** (sin fecha exacta, sin hora, sin estadio y sin árbitro asignado).

2. **Sorteo Semanal de Logística (La Programación Real):**
   - Se realiza habitualmente a mitad de semana para organizar los juegos del fin de semana de la Fecha en curso.
   - **Paso 1:** El administrador selecciona la disponibilidad (Ej: "Este fin de semana tenemos el Estadio A y B, y los Árbitros X, Y, Z").
   - **Paso 2:** El sistema toma los partidos huérfanos de la "FECHA 1" y los distribuye equitativamente garantizando:
     - Partidos Sábado y Domingo.
     - Horarios estables: 10:00, 12:00, 14:00, 16:00 (bloques de 2 horas).
     - Equidad sin colisión de uso de canchas (asignación inteligente).
     - Una vez guardado, los partidos cambian a estado "Programado".

---

## 🛠️ Modificaciones a nivel de Base de Datos
Para dar soporte técnico a este flujo, debemos ejecutar un `ALTER TABLE` a la tabla `matches`:

1. Añadir la columna de jornada:
   - `round_number` (INT): Identificará a qué "Fecha" pertenece el partido (Fecha 1, Fecha 2...).
2. Permitir que los recursos nazcan vacíos (`NULL`):
   - Modificar `match_date`, `match_time` y `stadium_id` para aceptar valores `NULL`.
3. Añadir estado inicial al Enum:
   - Modificar `status` (ENUM) para agregar `unscheduled` (o "Por programar").

---

## 🚀 Flujo de Desarrollo Propuesto

### Etapa 1: Preparación del Esqueleto
- Actualizar el modelo de BD (`MatchModel.php`) para soportar lecturas y escrituras con los nuevos campos nulos y la columna `round_number`.
- Sustituir la mecánica actual del `/calendario/generar` para que, basándose en la animación "Sorteo del Campeonato", se escriba en la base de datos exclusivamente la matriz de enfrentamientos de las jornadas (Generador de Fixture Base con algoritmo de "Polígono" estándar round-robin).

### Etapa 2: Módulo "Sorteo Semanal" (NUEVO)
- Crear una nueva interfaz en el panel de administrador: `/programacion-semanal/{league_id}`.
- **Vista de Asignación:** Mostrará la "FECHA N" que toca jugar. Permitirá visualizar los cruces (A vs B).
- **Panel Lateral de Disponibilidad:** Checkboxes para marcar qué Estadios y qué Árbitros van a trabajar este fin de semana.
- **Botón `Ejecutar Sorteo Logístico`:**
  - Invoca por POST al servidor, el cual calculará los bloques (Ej: Sábado 10:00 Estadio A).
  - Repartirá las cruces equitativamente al azar (Shuffle) y luego guardará las Fechas/Horas/Estadios/Árbitros.
  - Opcionalmente, mostrar en la interfaz un drag & drop (arrastrar y soltar) por si el administrador quiere hacer un ajuste de último minuto (Ej: "este equipo viajará de lejos, lo pondré a las 14:00 manual").

### Etapa 3: Cierre y Retrocompatibilidad
- Adaptar las vistas públicas de "Resultados" y "Partidos Programados" para agrupar los datos por `FECHA` (`round_number`), ocultando evidentemente aquellos encuentros que todavía están "Por definir" (Status `unscheduled`) o mostrándolos en una tabla como "Próximos Enfrentamientos" sin fecha confirmada.

---

## 📅 Próximo Paso
¿Apruebas este plan de acción? Si estás de acuerdo con la estrategia de base de datos y diseño lógico, mi próxima respuesta ejecutará los `ALTER TABLE` e iniciará la "Etapa 1" integrando esto en la aplicación.
