# 🏆 Resultados Fútbol Amateur

![Versión](https://img.shields.io/badge/Versi%C3%B3n-1.1-blue.svg)
![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4.svg?logo=php&logoColor=white)
![MariaDB](https://img.shields.io/badge/MariaDB-10.6%20LTS-003545.svg?logo=mariadb&logoColor=white)
![Arquitectura](https://img.shields.io/badge/Arquitectura-MVC%20Puro-brightgreen.svg)

Un **Sistema Web de Gestión de Campeonatos y Resultados de Fútbol** desarrollado desde cero bajo una arquitectura **MVC (Modelo-Vista-Controlador)** en PHP puro. Diseñado para ofrecer alto rendimiento, escalabilidad y una experiencia de usuario (UX) rica, moderna y *100% mobile-first*.

El proyecto rompe con la dependencia de frameworks de terceros (cero Laravel, cero Bootstrap, cero Tailwind), controlando cada píxel de su diseño a través de CSS3 puro e implementando toda su lógica a través de los cimientos robustos de **PHP 8.2** y sentencias preparadas de **PDO** para máxima seguridad.

---

## 🚀 Capacidades y Módulos

El sistema está estructurado por diferentes componentes que garantizan una fluida administración logística completa:

*   **🛡️ Panel de Autenticación (`Auth`)**: Ingreso y control seguro de sesión de administradores (con cifrado `password_hash`).
*   **📊 Módulo de Posiciones y Resultados**: Tabla general, diferencia de goles (GD), puntos, etc. Cálculos instantáneos y ordenamiento automático.
*   **⚽ Módulo de Torneos (`Tournaments`)**: Creación de campeonatos, gestión de fases organizadas en llaves de eliminación, cruces automáticos.
*   **📱 Frontend Público Exclusivo**: Vistas hermosas adaptadas a celulares y escritorio, diseñadas expresamente para fans y jugadores, permitiendo rápida lectura de las fechas jugadas.
*   **📑 Gestión de Log y Errores (`storage/logs`)**: Rastreabilidad total con reportes automáticos fechados diarios para control y administración.

---

## ⚠️ Generador Provisional de Llaves (`playoffs_custom.php`)

> **Nota:** Este módulo aislado actúa como un **controlador temporal**.

Dado que un campeonato en curso (*Liga Deportiva Parroquial San Francisco de Borja*) requirió una lógica muy especial fuera de lo común para su matriz de cruces fijos e integrando partidos de Ida y Vuelta con desempate de tabla, se creó este script independiente. 

- No afecta la estructura MVC principal para evitar comprometer la base de datos oficial.
- Guarda el estado transitorio en formatos de texto `.json` ubicados en `storage/`.
- Permite resolver empates manuales.
- **Exportación Alta Fidelidad**: Incluye tecnología `html2canvas` y `jsPDF` renderizando el bracket completo temporal a PNG 400% y PDF vectorizado para publicarse en redes de la liga, con sus respectivas marcas de agua de control.

---

## 🛠️ Stack Tecnológico

El núcleo del software fue seleccionado meticulosamente buscando velocidad, independencia tecnológica de los estándares de moda y máxima compatibilidad de hosting:

| Capa | Tecnología |
|---|---|
| Backend | **PHP 8.2 puro** (Sin Frameworks, Tipado Estricto) |
| Base de Datos | **MariaDB 10.6 LTS** (Esquema seguro PDO/UTF8mb4) |
| Frontend | HTML5 Semántico + **JavaScript Vanilla** |
| Estilos (UI/UX) | **CSS3 Nativo** (Variables raíz, Flexbox, CSS Grid) |
| Entorno Server | Linux / XAMPP (Apache + DB) |
| Control Versiones| Git + GitHub |

---

## 🏗️ Patrón de Diseño (Estándar MVC)

Este software respeta los cánones del MVC puro para que su mantenimiento sea predecible:

```text
/app/
 ├── Controllers/  # Lógica de intercomunicación 
 ├── Models/       # Queries hacia Base de datos por entidad
 └── Views/        # Las vistas inyectadas (UI/UX)
/core/             # Enrutador, Controller & Model central singleton PDO
/public/           # Front Controller (único punto de acceso real) y Assets
```

---

## 🌍 Metas de Proyecto (Diseño de Interfaz Premium)
Cada aspecto construido maneja reglas absolutas visuales: Modo Oscuro inteligente adaptable a localStorage, bordes esmerilados (*Glassmorphism*), sombras volumétricas y una jerarquía de información orientada completamente a uso en **Dispositivos Móviles** sin que un solo elemento se quiebre.

> **Powered con elegancia y pasión por código por _SOLINTEEC DEVS & TECH._**
