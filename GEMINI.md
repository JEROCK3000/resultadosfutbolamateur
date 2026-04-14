# GEMINI.md — Instrucciones del Proyecto: Resultados Fútbol

> **Versión:** 1.1 — Actualizado: 2026-03-17

## Descripción General
Proyecto web de resultados de fútbol desarrollado con PHP puro, sin frameworks de ningún tipo.
Todo el contenido, planes, documentación y comunicación se realizan en **español latino**, a excepción de:
- Nombres de variables y constantes (pueden estar en inglés)
- Modelado de base de datos: nombres de tablas, columnas y claves (pueden estar en inglés)

---

## Stack Tecnológico

| Capa | Tecnología |
|---|---|
| Backend | PHP 8.2 puro (sin frameworks) |
| Base de datos | MariaDB 10.6 LTS (máxima compatibilidad con PHP 8.2) |
| Frontend | HTML5 + CSS3 + JavaScript vanilla |
| Control de versiones | Git + GitHub |
| Servidor local | XAMPP (Apache + MariaDB) |
| Despliegue | Script `deploy.sh` vía SSH |

---

## Estilo y Diseño

- Diseños **modernos, suaves, elegantes y de calidad enterprise**.
- Paletas de colores armoniosas, tipografía moderna (Google Fonts: Inter, Outfit o similar).
- Usar CSS nativo: variables CSS, gradientes, sombras suaves, micro-animaciones.
- **Cero dependencias de frameworks CSS** (sin Bootstrap, sin Tailwind).
- Modo oscuro como opción preferida donde aplique.

### 📱 Diseño Responsivo — OBLIGATORIO

> **Regla absoluta: TODO debe ser 100% responsivo. Sin excepción.**

- Enfoque **mobile-first**: diseñar primero para móvil, luego escalar a tablet y escritorio.
- Usar CSS Grid y Flexbox nativos para layouts fluidos.
- Breakpoints mínimos a respetar:
  - `480px` — móviles pequeños
  - `768px` — tablets
  - `1024px` — escritorio
  - `1280px` — pantallas grandes
- Imágenes y medios con `max-width: 100%` y `height: auto`.
- Tipografía fluida con `clamp()` o unidades relativas (`rem`, `em`, `vw`).
- Navegación adaptable: menú hamburguesa en móvil.
- Tablas de datos con scroll horizontal en pantallas pequeñas.
- Ninguna vista o componente se entrega sin haber sido verificado en móvil.

---

## Arquitectura MVC

Todo el sistema se desarrolla bajo el patrón **Modelo-Vista-Controlador (MVC)** implementado en PHP puro.

### Estructura de Directorios

```
resultadosfutbol/
├── app/
│   ├── Controllers/         # Controladores (uno por módulo)
│   │   └── NombreController.php
│   ├── Models/              # Modelos (uno por entidad de BD)
│   │   └── NombreModel.php
│   └── Views/               # Vistas HTML+PHP (organizadas por módulo)
│       └── nombre/
│           ├── index.php    # Listado (Read)
│           ├── create.php   # Formulario de creación (Create)
│           ├── edit.php     # Formulario de edición (Update)
│           └── delete.php   # Confirmación de eliminación (Delete)
├── core/
│   ├── Router.php           # Enrutador principal
│   ├── Controller.php       # Clase base de controladores
│   ├── Model.php            # Clase base de modelos
│   └── Database.php         # Conexión PDO singleton
├── docs/                    # Documentación de todo lo que ocurre en el proyecto
│   ├── modulos/             # Documentación por módulo
│   ├── errores/             # Registro de errores resueltos
│   ├── implementaciones/    # Nuevas funcionalidades documentadas
│   └── base-de-datos/       # Cambios de esquema BD
├── public/                  # Punto de entrada público
│   ├── index.php            # Front Controller (único punto de entrada)
│   └── assets/
│       ├── css/
│       ├── js/
│       └── img/
├── storage/
│   └── logs/
│       └── YYYY-MM-DD.log   # Log diario del sistema
├── helpers/                 # Funciones globales (writeLog, etc.)
├── deploy.sh                # Script de despliegue a producción
├── .env                     # Variables de entorno (NO subir a GitHub)
├── .env.example             # Plantilla de variables de entorno (SÍ subir)
├── .gitignore               # Archivos excluidos del repositorio
└── GEMINI.md                # Este archivo
```

### Convenciones MVC

#### Modelos
- Un modelo por tabla principal de la base de datos.
- Contiene toda la lógica de acceso a datos (queries PDO).
- Cada modelo implementa los métodos CRUD base:
  - `getAll()` — obtener todos los registros
  - `getById(int $id)` — obtener uno por ID
  - `create(array $data)` — insertar nuevo registro
  - `update(int $id, array $data)` — actualizar registro
  - `delete(int $id)` — eliminar registro

#### Controladores
- Un controlador por módulo.
- Métodos estándar por módulo:
  - `index()` — listado con paginación
  - `show(int $id)` — detalle
  - `create()` — mostrar formulario de creación
  - `store()` — procesar creación (POST)
  - `edit(int $id)` — mostrar formulario de edición
  - `update(int $id)` — procesar edición (POST)
  - `destroy(int $id)` — eliminar (POST con confirmación)

#### Vistas
- Cada módulo tiene su propia carpeta en `app/Views/nombre-modulo/`.
- Todas las vistas heredan de un layout base (`app/Views/layouts/app.php`).
- Cada vista es **100% responsiva** (mobile-first).
- Incluir siempre: breadcrumbs de navegación y mensajes de éxito/error.

---

## Rutas y Endpoints

- Todas las rutas deben funcionar **tanto en localhost como en producción** sin modificación manual.
- Usar una constante global `BASE_URL` definida en el archivo de configuración, detectada automáticamente:

```php
// src/config/app.php
define('BASE_URL', (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/resultadosfutbol');
```

- Cada módulo nuevo debe documentar sus endpoints en `docs/modulos/nombre-modulo.md`.
- Las rutas internas nunca deben ser rutas absolutas del sistema de archivos expuestas al usuario.

---

## Base de Datos

- Motor: **MariaDB 10.6 LTS**
- Conexión mediante **PDO con prepared statements** (sin mysqli directo).
- Charset: `utf8mb4`, collation: `utf8mb4_unicode_ci`.
- Prefijo de tablas: ninguno (tablas limpias en su propio schema).
- Los nombres de tablas y columnas van en **inglés** (snake_case).
- Toda migración o cambio de esquema debe documentarse en `docs/base-de-datos/`.

---

## Control de Versiones — GitHub

- Repositorio en GitHub conectado desde el inicio del proyecto.
- Flujo de trabajo Git:
  1. Desarrollar en local (rama `develop` o `feature/nombre`)
  2. Verificar en localhost
  3. Merge a `main`
  4. Ejecutar `deploy.sh` para subir a producción

### Archivo `deploy.sh`
Script en la raíz del proyecto que realiza:
1. `git add .`
2. `git commit -m "mensaje"`
3. `git push origin main`
4. Conexión SSH al servidor de producción
5. `git pull origin main` en el servidor

```bash
#!/bin/bash
# deploy.sh — Script de despliegue a producción

MSG="${1:-Deploy automático $(date '+%Y-%m-%d %H:%M:%S')}"

echo "📦 Preparando commit: $MSG"
git add .
git commit -m "$MSG"
git push origin main

echo "🚀 Desplegando en producción..."
ssh usuario@servidor.com "cd /var/www/resultadosfutbol && git pull origin main && php artisan-equivalent.php migrate"

echo "✅ Deploy completado."
```

---

## Sistema de Logs

- Todos los errores, eventos y acciones relevantes del sistema deben registrarse en:
  ```
  storage/logs/YYYY-MM-DD.log
  ```
- El nombre del archivo de log se genera automáticamente con la fecha actual.
- Función helper global de logging:

```php
function writeLog(string $level, string $message, array $context = []): void {
    $dir = __DIR__ . '/../../storage/logs';
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $file = $dir . '/' . date('Y-m-d') . '.log';
    $entry = '[' . date('Y-m-d H:i:s') . '] [' . strtoupper($level) . '] ' . $message;
    if (!empty($context)) $entry .= ' | Contexto: ' . json_encode($context, JSON_UNESCAPED_UNICODE);
    file_put_contents($file, $entry . PHP_EOL, FILE_APPEND | LOCK_EX);
}
```

- Niveles de log a usar: `INFO`, `WARNING`, `ERROR`, `DEBUG`, `CRITICAL`.

---

## Documentación Obligatoria

Por cada módulo o funcionalidad desarrollada, crear o actualizar el archivo correspondiente en `docs/`:

- **Nuevo módulo:** `docs/modulos/nombre-modulo.md`
- **Error resuelto:** `docs/errores/YYYY-MM-DD-descripcion.md`
- **Nueva implementación:** `docs/implementaciones/YYYY-MM-DD-descripcion.md`
- **Cambio de BD:** `docs/base-de-datos/YYYY-MM-DD-cambio.md`

Cada documento debe incluir: fecha, descripción, archivos afectados, solución aplicada (si aplica).

---

## Estándares de Código PHP

- PHP 8.2: usar tipos estrictos (`declare(strict_types=1)`), enums, readonly properties donde aplique.
- PSR-4 para autoloading manual (sin Composer si es posible, o con Composer mínimo).
- Funciones y métodos documentados con DocBlocks en español.
- Validación de entrada del usuario siempre del lado del servidor.
- Sanitización con `htmlspecialchars()`, `filter_input()`, y prepared statements.
- Contraseñas hasheadas con `password_hash()` / `password_verify()`.

---

## Reglas Generales del Agente

1. **Todo plan, explicación y documentación generada debe estar en español latino.**
2. Antes de crear cualquier módulo, documenta el plan en `docs/`.
3. Antes de hacer cambios en BD, documenta el esquema en `docs/base-de-datos/`.
4. Cada endpoint nuevo debe especificarse claramente con su URL relativa y absoluta.
5. Registrar en el log cualquier operación crítica o error detectado durante el desarrollo.
6. Nunca exponer datos sensibles (contraseñas, tokens, claves de API) en el código fuente.
7. El archivo `.env` jamás debe subirse a GitHub (verificar `.gitignore`).
8. **Todo módulo nuevo siempre incluye su CRUD completo** (crear, leer, actualizar, eliminar) desde el inicio, aunque no todas las operaciones se expongan en la UI inmediatamente.
9. **Todo diseño y toda vista debe ser 100% responsiva (mobile-first).** No se acepta ninguna entrega sin responsividad verificada.
10. Seguir siempre la arquitectura MVC: ninguna lógica de negocio en las vistas, ninguna lógica de presentación en los controladores.
