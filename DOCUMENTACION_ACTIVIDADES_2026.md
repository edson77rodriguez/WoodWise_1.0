# Documentación de Actividades — WoodWise (Enero–Marzo 2026)

**Proyecto:** WoodWise

**Periodo documentado:** Enero 2026 – Marzo 2026

**Propósito del documento:** Registrar formalmente las actividades realizadas, los resultados obtenidos y las evidencias que demuestran su cumplimiento.

---

## 1) Actividades Enero 2026 — Implementación del registro y autenticación del usuario

### 1.1 Objetivo
Implementar la base de acceso al sistema mediante registro e inicio de sesión, asegurando que los usuarios puedan autenticarse de forma consistente para utilizar los módulos del sistema.

### 1.2 Alcance
- Incluye: flujo de autenticación (inicio/cierre de sesión), validaciones de credenciales, manejo de errores de acceso y control de sesión.
- No incluye: reglas avanzadas de permisos por rol (trabajadas en Febrero 2026).

### 1.3 Actividades realizadas
- Definición e integración del flujo de autenticación con la interfaz principal del sistema.
- Implementación de validación de credenciales y mensajes de error controlados.
- Verificación de persistencia de sesión y acceso a vistas protegidas.
- Preparación de cuentas de prueba/demostración (cuando aplica) para validar el funcionamiento de extremo a extremo.

### 1.4 Resultados / Entregables
- Acceso controlado al sistema mediante credenciales.
- Manejo de errores de autenticación de forma amigable.
- Sesión persistente y navegación básica protegida.

### 1.5 Evidencias (insertar imágenes aquí)
- **Figura 1 (Enero):** Pantalla de inicio de sesión / registro (campos visibles y acción principal).
  - **Insertar imagen aquí**
- **Figura 2 (Enero):** Sesión iniciada correctamente (vista principal / dashboard visible).
  - **Insertar imagen aquí**
- **Figura 3 (Enero):** Error controlado por credenciales incorrectas (mensaje visible).
  - **Insertar imagen aquí**

### 1.6 Observaciones / Próximos pasos
- Vincular el rol del usuario a rutas/paneles específicos y reforzar la autorización por perfil.

---

## 2) Actividades Febrero 2026 — Desarrollo del sistema de gestión de roles y permisos

### 2.1 Objetivo
Asegurar que cada tipo de usuario (Administrador, Técnico y Productor) acceda únicamente a los módulos y rutas correspondientes, evitando confusiones de navegación y fortaleciendo la seguridad del sistema.

### 2.2 Alcance
- Incluye: protección de rutas por rol, redirección consistente post-login y validación de acceso a secciones por perfil.
- No incluye: integración con bots/APIs externas (trabajadas en fases posteriores).

### 2.3 Actividades realizadas
- Definición del comportamiento esperado por rol en el sistema.
- Configuración de control de acceso para impedir que un rol ingrese a pantallas no autorizadas.
- Normalización del flujo de redirección posterior al inicio de sesión.
- Validación de que los módulos principales se mantengan en el contexto de rol correcto durante la navegación.

### 2.4 Resultados / Entregables
- Separación clara de acceso: cada rol llega al panel correcto.
- Restricción de rutas y recursos sensibles según perfil.
- Base estable para exponer funcionalidades por rol en etapas posteriores.

### 2.5 Evidencias (insertar imágenes aquí)
- **Figura 1 (Febrero):** Inicio de sesión como Técnico y acceso a panel técnico.
  - **Insertar imagen aquí**
- **Figura 2 (Febrero):** Inicio de sesión como Productor y acceso a panel de productor.
  - **Insertar imagen aquí**
- **Figura 3 (Febrero):** Intento de acceso no autorizado y respuesta esperada (bloqueo o redirección).
  - **Insertar imagen aquí**

### 2.6 Observaciones / Próximos pasos
- Consolidar reportes y consultas por rol.
- Preparar integraciones externas (API) con reglas de acceso consistentes.

---

## 3) Actividades Marzo 2026 — Diseño del frontend del catálogo de especies

### 3.1 Objetivo
Diseñar una interfaz moderna, consistente y legible para el catálogo de especies, alineada a la identidad visual de WoodWise y con comportamiento responsivo.

### 3.2 Alcance
- Incluye: definición de paleta de colores, variables globales, estilos para layout (sidebar, navbar, contenido) y componentes visuales.
- No incluye: cambios de lógica de negocio o reglas de cálculo.

### 3.3 Actividades realizadas
- Definición de paleta institucional verde/café mediante variables CSS.
- Ajuste de layout general (sidebar fijo, navbar superior, contenido principal) para una navegación más clara.
- Implementación de comportamiento responsive y sidebar colapsable según tamaño de pantalla.
- Estilización de componentes de dashboard (tarjetas, botones de acción y estados hover/active).

### 3.4 Resultados / Entregables
- Interfaz consistente y alineada a una identidad visual definida.
- Mejor experiencia de usuario por estructura clara y responsividad.
- Base de estilos reutilizable para extender el diseño a más módulos.

### 3.5 Evidencias (insertar imágenes aquí)
- **Figura 1 (Marzo):** Vista del catálogo de especies (pantalla completa).
  - **Insertar imagen aquí**
- **Figura 2 (Marzo):** Vista responsive (móvil/tablet) mostrando navegación/menú.
  - **Insertar imagen aquí**
- **Figura 3 (Marzo):** Detalle de componentes UI (tarjetas/botones/menú usuario).
  - **Insertar imagen aquí**

### 3.6 Referencia técnica (opcional)
- Archivo de estilos del dashboard (paleta y layout): `public/css/WW/dashboard.css`

### 3.7 Observaciones / Próximos pasos
- Aplicar la guía visual a módulos restantes (trozas, árboles, reportes) para mantener consistencia.

---

## Anexos (Opcional)

### A. Registro de evidencias
Se recomienda nombrar las imágenes de evidencia con el siguiente patrón:
- `Enero2026_Fig1_Login.png`
- `Febrero2026_Fig2_ProductorDashboard.png`
- `Marzo2026_Fig1_CatalogoEspecies.png`

Y mantenerlas agrupadas en una carpeta de documentación (por ejemplo, `docs/evidencias/`) para facilitar su control.
