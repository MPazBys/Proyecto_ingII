# Desarrollo Web "Librería M&P" - Ingeniería de Software II

Este espacio presenta el desarrollo de la versión web del sistema de librería. El proyecto fue iniciado en **Taller de Programación I** y escalado a nivel profesional en la asignatura **Ingeniería de Software II** (FaCENA - UNNE), aplicando metodologías ágiles y métricas de ingeniería.

Este desarrollo es producto del trabajo en equipo realizado por:
* **Bys, Paz**
* **Cadozo, Micaela**

El proyecto despliega una solución web funcional bajo un entorno de desarrollo local (XAMPP), combinando PHP y MySQL, con un fuerte enfoque en la calidad del software y la gestión del proyecto.

### ⚙️ Metodologías y Tecnologías Implementadas

El proyecto se construyó bajo estándares formales de ingeniería de software, priorizando la fiabilidad, la integridad de los datos y la escalabilidad del producto:

*   **Arquitectura:** Patrón **Modelo-Vista-Controlador (MVC)** implementado a través del framework CodeIgniter 4, separando la lógica de negocio, el acceso a datos y la interfaz gráfica.
*   **Gestión de Proyecto:** Ciclo de vida incremental planificado bajo el marco de trabajo **SCRUM**, organizado en 4 Sprints consecutivos (desde el diseño inicial hasta el despliegue del carrito transaccional y pruebas).
*   **Estimación de Esfuerzo:** Aplicación del modelo empírico **COCOMO Básico** (Modo Orgánico) para la proyección de costos, calendario y estimación del tamaño del software (KLOC).
*   **Diseño UML:** Modelado avanzado utilizando diagramas de Casos de Uso, Secuencia, Clases y la aplicación del **Patrón de Diseño Estado** para la gestión del ciclo de vida de los pedidos (Pendiente, Enviado, Finalizado)[cite: 9].
*   **Base de Datos:** Diseño relacional normalizado hasta la Tercera Forma Normal (3FN) con integridad referencial estricta.
*   **Calidad de Código:** Implementación de Pruebas Unitarias automatizadas (Unit Testing) con **PHPUnit**.
*   **Stack Tecnológico:** PHP, MySQL, entorno local XAMPP.

### 📂 Estructura del Repositorio

    > ## Proyecto_ingII
    - README.md (documento principal de presentación)
    - proyecto_bys_cardozo/ (Directorio principal con el código fuente y MVC)
    - Trabajo de Campo G29.pdf (Documentación integral del proceso del proyecto siguiendo los lineamientos de Ingería de software, Manual de Instalación y Configuración (Guía de despliegue local) y Manual de Usuario (Guía de operación del sistema))
    - bd_bys_cardozo.sql (Script de creación y lote de datos MySQL)
    - diagrama de clases.png (Modelado UML del sistema)
    - proyecto_bys_cardozo.txt (Documento de credenciales de prueba y comandos)
