DOCUMENTO DE HISTORIAS DE USUARI
O
Proyecto:
Star
t
Link — Sistema de Gestión de Recursos Humanos (HRMS)
Fecha:
27 febrero
 de 202
6
Versión:
1.
1
Analista
:
Jose Julian Guaza Botero
Instructor:
J
aime Alberto Zapata Valencia 
Institución:
SENA
Objetivo del Documento
Este documento recopila todas las historias de usuario definidas para el proyecto 
Star
t
Link HRMS, organizadas por módulo y prioridad. Cada historia representa una necesidad funcional desde la perspectiva del usuario, acompañada de criterios de aceptación verificables.
Este documento sustituye al tradicional Documento de Requisitos Funcionales (DRS) y se alinea con las prácticas ágiles (Scrum/Kanban). Las restricciones técnicas y no funcionales se documentan en el Documento de Alcance y Arquitectura del sistema.
Estructura de una Historia de Usuario
Cada historia de usuario sigue el formato estándar:
Como [tipo de usuario]
Quiero [una funcionalidad]
Para [obtener un beneficio]
Y se complementa con los siguientes campos:
Criterios de Aceptación: condiciones verificables (Given/When/Then o lista de bullets)
Módulo o categoría funcional del sistema
Prioridad: Alta / Media / Baja
Estimación en puntos de historia
Estado: Pendiente / En progreso / Terminado
Tabla de Historias de Usuario (Backlog General)
ID
Historia de Usuario
Módulo
Prioridad
Estimación
Estado
HU-01
Registro de nuevo usuario
Autenticación
Alta
3 pts
T
e
rminado
HU-02
Inicio de sesión
Autenticación
Alta
3 pts
T
e
rminado
HU-03
Recuperación de contraseña
Autenticación
Alta
3 pts
Pendiente
HU-04
Completado inicial del perfil
Perfil
Alta
2 pts
T
e
rminado
HU-05
Gestión de perfil básico
Perfil
Alta
3 pts
T
e
rminado
HU-06
Gestión de experiencia laboral
Perfil
Alta
3 pts
T
e
rminado
HU-07
Gestión de estudios y habilidades
Perfil
Media
3 pts
T
e
rminado
HU-08
Publicar perfil de búsqueda de empleo
Candidatos
Alta
3 pts
Pendiente
HU-09
Explorar perfiles de candidatos
Candidatos
Alta
3 pts
Pendiente
HU-10
Contactar candidato y enviar solicitud de contratación
Candidatos
Alta
5 pts
Pendiente
HU-11
Publicar oferta de trabajo
Ofertas de Empleo
Alta
5 pts
Pendiente
HU-12
Postularse a una oferta de trabajo
Ofertas de Empleo
Alta
3 pts
Pendiente
HU-13
Gestionar postulantes a una oferta
Ofertas de Empleo
Alta
8 pts
Pendiente
HU-14
Publicar capacitación
Capacitaciones
Alta
5 pts
Pendiente
HU-15
Inscribirse y cancelar inscripción a capacitación
Capacitaciones
Alta
3 pts
Pendiente
HU-16
Ver lista de inscritos a capacitación
Capacitaciones
Media
2 pts
Pendiente
HU-17
Crear empresa en la plataforma
Empresas
Alta
5 pts
Pendiente
HU-18
Gestionar información de la empresa
Empresas
Media
3 pts
Pendiente
HU-19
Seleccionar empresa activa (multi-empresa)
Empresas
Media
3 pts
Pendiente
HU-20
Ver miembros del equipo
Equipo Interno
Media
3 pts
Pendiente
HU-21
Gestionar roles internos del equipo
Equipo Interno
Alta
5 pts
Pendiente
HU-22
Chat privado entre usuarios
Comunicación
Alta
8 pts
Pendiente
HU-23
Chat grupal interno de empresa
Comunicación
Media
8 pts
Pendiente
HU-24
Chat grupal de postulantes a oferta
Comunicación
Media
5 pts
Pendiente
HU-25
Generar nómina para trabajadores
Nómina
Alta
8 pts
Pendiente
HU-26
Descargar recibo de nómina en PDF
Nómina
Alta
3 pts
Pendiente
HU-27
Consultar historial laboral propio
Historial Laboral
Alta
5 pts
Pendiente
HU-28
Recibir y gestionar notificaciones
Notificaciones
Alta
5 pts
Pendiente
HU-29
Registrar evaluación de desempeño
Desempeño
Media
5 pts
Pendiente
HU-3
0
Gestión global de usuarios (ADMINISTRADOR)
Administración
Alta
5 pts
Pendiente
HT-01
[HT] Sistema de roles y permisos (RBAC)
Infraestructura
Alta
8 pts
Pendiente
HT-02
[HT] Seguridad: cifrado y HTTPS
Infraestructura
Alta
5 pts
Pendiente
HT-03
[HT] Base de datos y escalabilidad
Infraestructura
Alta
8 pts
Pendiente
Desglose Narrativo — Historias de Usuario
Módulo: Autenticació
n
HU-01  Registro de nuevo usuario
Historia de Usuario
Como visitante del sistema
quiero registrarme con mi correo electrónico y una contraseña segura
para crear mi cuenta y acceder a las funcionalidades de la plataforma StartLink
Criterios de Aceptación:
•   El formulario debe solicitar nombre, correo electrónico único, contraseña y confirmación de contraseña.
•   La contraseña debe tener entre 8 y 20 caracteres, contener al menos un número y un carácter especial.
•   Se debe validar la solicitud mediante un token de seguridad (Google reCAPTCHA).
•   Si el correo ya está registrado, el sistema muestra un mensaje de error descriptivo.
•   Tras el registro exitoso, se notifica al usuario para que pueda iniciar sesión 
y redirige al inicio de sesión
•   La contraseña se almacena cifrada con bcrypt o Argon2 único.
Módulo
Autenticación
Prioridad
Alta
Estimación
3 pts
Estado
Terminado
HU-02  Inicio de sesión
Historia de Usuario
Como usuario registrado
quiero iniciar sesión con mi correo y contraseña
para acceder a mi cuenta y las funcionalidades según mi rol
Criterios de Aceptación:
El sistema valida credenciales correctas con seguridad de Google reCAPTCHA y redirige al dashboard correspondiente.
Si las credenciales son incorrectas, muestra un mensaje de error
.
Existe un botón o enlace visible de '¿Olvidaste tu contraseña?'.
Tras 5 intentos fallidos, el sistema bloquea el acceso por 15 minutos.
Módulo
Autenticación
Prioridad
Alta
Estimación
3 pts
Estado
Pendiente
HU-03  Recuperación de contraseña
Historia de Usuario
Como usuario registrado
quiero recuperar mi contraseña mediante un enlace enviado a mi correo
para restablecer el acceso a mi cuenta en caso de olvido
Criterios de Aceptación:
El usuario ingresa su correo y el sistema envía un código PIN de 6 dígitos válido por 15 minutos.
El usuario ingresa el código PIN en la plataforma junto con la nueva contraseña.
Tras restablecer exitosamente, el usuario puede iniciar sesión con la nueva contraseña.
Los códigos de restablecimiento caducan tras 15 minutos o una vez son utilizados.
Módulo
Autenticación
Prioridad
Alta
Estimación
3 pts
Estado
Pendiente
Módulo: Perfil de Usuario
HU-04  Completado inicial del perfil
Historia de Usuario
Como usuario recién registrado
quiero ver un aviso prominente para completar mi perfil tras mi primer inicio de sesión
para tener un perfil atractivo que me permita ser considerado en ofertas y postulaciones
Criterios de Aceptación:
Tras el primer login, aparece un aviso/modal para completar el perfil.
El aviso se muestra sólo si el perfil está incompleto (nombre, foto, etc.).
El usuario puede optar por completarlo ahora o postponerlo.
Se indica visualmente el porcentaje de completitud del perfil.
Módulo
Perfil
Prioridad
Alta
Estimación
2 pts
Estado
Pendiente
HU-05  Gestión de perfil básico
Historia de Usuario
Como usuario de la plataforma
quiero visualizar y actualizar mi información personal (nombre, contacto, país, ciudad, foto, DNI)
para mantener mis datos actualizados y tener una identidad completa en la plataforma
Criterios de Aceptación:
•   El usuario puede editar nombre completo, correo, género, teléfono, país, departamento, ciudad y DNI.
•   Puede subir y reemplazar su foto de perfil (formatos JPG/JPEG/PNG/GIF, máx. 5 MB).
•   Puede subir su Hoja de Vida en formatos PDF, DOC o DOCX (máx. 5 MB).
•   Los cambios se guardan con confirmación visual de éxito.
•   El perfil actualizado se refleja en toda la plataforma.
Módulo
Perfil
Prioridad
Alta
Estimación
3 pts
Estado
Pendiente
HU-06  Gestión de experiencia laboral
Historia de Usuario
Como usuario de la plataforma
quiero agregar, editar y eliminar entradas de experiencia laboral en mi perfil
para mostrar mi trayectoria profesional a potenciales empleadores
Criterios de Aceptación:
El usuario puede agregar una experiencia con título del puesto, empresa, fechas inicio/fin y descripción.
Puede eliminar una entrada con confirmación previa.
Las entradas se muestran ordenadas cronológicamente (más reciente primero).
Módulo
Perfil
Prioridad
Alta
Estimación
3 pts
Estado
Pendiente
HU-07  Gestión de estudios y habilidades
Historia de Usuario
Como usuario de la plataforma
quiero registrar mis estudios académicos y habilidades
para presentar mis competencias de forma clara y estructurada
Criterios de Aceptación:
•   El usuario puede agregar estudios con título, institución, fecha de inicio y fecha de fin
.
•   Puede agregar habilidades puntuales
.
•   Puede eliminar cualquier entrada de estudios o habilidades.
•   Los cambios se reflejan en el perfil público de búsqueda de empleo si está
.
Módulo
Perfil
Prioridad
Media
Estimación
3 pts
Estado
Pendiente
Módulo: Perfiles de Candidatos
HU-08  Publicar perfil de búsqueda de empleo
Historia de Usuario
Como Usuario (talento)
quiero publicar y actualizar mi perfil de búsqueda de empleo con título buscado, modalidad y expectativa salarial
para ser visible ante Administradores de Empresa interesados en mi perfil
Criterios de Aceptación:
•   El perfil incluye: título buscado, tipo de contrato preferido, modalidad y expectativa salarial.
•   El campo 'está disponible' puede ser activado/desactivado por el usuario.
•   Al adquirir rol de Administrador de Empresa, el perfil se oculta automáticamente (está disponible = FALSE).
•   El perfil público es visible para todos los usuarios de la plataforma.
Módulo
Candidatos
Prioridad
Alta
Estimación
3 pts
Estado
Pendiente
HU-09  Explorar perfiles de candidatos
Historia de Usuario
Como usuario de la plataforma
quiero buscar y visualizar perfiles de candidatos disponibles con filtros por nombre, título y habilidades
para encontrar talentos o conocer otros profesionales en la plataforma
Criterios de Aceptación:
Se muestran únicamente perfiles con esta_disponible = TRUE.
El usuario puede filtrar por nombre, título buscado y habilidades clave.
Cada tarjeta de perfil muestra foto, nombre, título buscado y habilidades principales.
Los resultados se actualizan dinámicamente con cada filtro aplicado.
Módulo
Candidatos
Prioridad
Alta
Estimación
3 pts
Estado
Pendiente
HU-10  Contactar candidato y enviar solicitud de contratación
Historia de Usuario
Como Administrador de Empresa
quiero ver el perfil detallado de un candidato, iniciar un chat privado y enviarle una solicitud formal de contratación
para gestionar el proceso de selección de talento directamente desde la plataforma
Criterios de Aceptación:
•   El Administrador de Empresa puede acceder a la vista completa del perfil del candidato.
•   Puede iniciar un chat privado directamente desde el perfil del candidato.
•   Puede enviar solicitud formal de contratación especificando empresa a la que aplicará, salario base ofrecido y horas semanales (sin mensaje personalizado).
•   La solicitud genera una notificación al candidato.
•   El candidato puede aceptar o rechazar la solicitud desde sus notificaciones.
Módulo
Candidatos
Prioridad
Alta
Estimación
5 pts
Estado
Pendiente
HU-11  Publicar oferta de trabajo
Historia de Usuario
Como Administrador de Empresa
quiero crear y publicar ofertas de trabajo con título, descripción, rango salarial, ubicación, modalidad, requisitos, límite de postulantes y fecha de cierre
para atraer candidatos calificados para las vacantes de mi empresa
Criterios de Aceptación:
•   El formulario requiere estrictamente: título (>5 char), descripción (>20 char), ubicación, modalidad (Presencial/Remoto/Híbrido), presupuesto min/max, requisitos y fecha de cierre a futuro.
•   La oferta queda vinculada a la empresa del creador.
•   La oferta publicada es visible para todos los USUARIOS de la plataforma.
•   El creador puede editar, archivar o eliminar la oferta en cualquier momento.
Módulo
Ofertas de Empleo
Prioridad
Alta
Estimación
5 pts
Estado
Pendiente
Módulo: Ofertas de Emple
o
HU-12  Postularse a una oferta de trabajo
Historia de Usuario
Como Usuario
quiero buscar, visualizar y postularme a ofertas de trabajo disponibles
para acceder a oportunidades laborales dentro de la plataforma
Criterios de Aceptación:
•   El usuario puede ver el detalle completo de la oferta antes de postularse.
•   Tras postularse, se genera una notificación al Administrador de Empresa.
•   El usuario puede ver el estado de su postulación (Pendiente, Contratado, Rechazado).
•   El usuario puede retirarse de la postulación si lo desea, a menos que ya haya sido rechazado permanentemente.
Módulo
Ofertas de Empleo
Prioridad
Alta
Estimación
3 pts
Estado
Pendiente
HU-13  Gestionar postulantes a una oferta
Historia de Usuario
Como Administrador de Empresa
quiero ver la lista de postulantes a mi oferta, acceder a sus perfiles, chatear con ellos y decidir contratarlos o rechazarlos
para gestionar el proceso de selección de manera centralizada y eficiente
Criterios de Aceptación:
Se muestra la lista completa de postulantes con nombre
 y
 foto
.
Al contratar: actualiza estado de postulación, vincula usuario a la empresa, oculta perfil de búsqueda y genera notificación.
Al rechazar: marca la postulación como Rechazada de forma permanente y notifica al candidato.
El Administrador de Empresa puede iniciar chat privado con cualquier postulante.
Se puede crear un chat grupal con todos los postulantes de la oferta.
Módulo
Ofertas de Empleo
Prioridad
Alta
Estimación
8 pts
Estado
Pendiente
Módulo: Capacitaciones
HU-14  Publicar capacitación
Historia de Usuario
Como Administrador de Empresa
quiero crear y publicar capacitaciones con nombre, descripción, fechas de inicio/fin y costo
para ofrecer oportunidades de formación a usuarios de la plataforma
Criterios de Aceptación:
•   El formulario requiere nombre, descripción, costo obligatoriamente y fechas de inicio y fin válidas (no en el pasado).
•   La capacitación queda vinculada al creador y a su empresa.
•   La capacitación publicada es visible para todos los usuarios.
•   El creador puede editar o eliminar la capacitación.
•   Solo el creador o Administrador de Empresa pueden eliminarla.
Módulo
Capacitaciones
Prioridad
Alta
Estimación
5 pts
Estado
Pendiente
HU-15  Inscribirse y cancelar inscripción a capacitación
Historia de Usuario
Como Usuario de la plataforma
quiero inscribirme en capacitaciones disponibles y cancelar mi inscripción si lo deseo
para gestionar mis oportunidades de formación de manera autónoma
Criterios de Aceptación:
Todos los usuarios pueden inscribirse a capacitaciones.
El usuario puede ver el detalle completo de la capacitación antes de inscribirse.
Puede cancelar la inscripción antes de la fecha de inicio.
Se genera una notificación al creador de la capacitación cuando alguien se inscribe.
El historial de capacitaciones queda registrado en el perfil del usuario.
Módulo
Capacitaciones
Prioridad
Alta
Estimación
3 pts
Estado
Pendiente
HU-16  Ver lista de inscritos a capacitación
Historia de Usuario
Como Administrador de Empresa
quiero ver la lista detallada de usuarios inscritos a una capacitación con nombre, correo y fecha de inscripción
para controlar la participación y gestionar la formación de mi empresa
Criterios de Aceptación:
•   Solo roles autorizados (creador, Administrador de Empresa) pueden ver la lista.
•   La lista muestra nombre completo, correo y fecha/hora de inscripción.
•   La lista puede ser impresa.
•   Se indica el número total de inscritos.
Módulo
Capacitaciones
Prioridad
Media
Estimación
2 pts
Estado
Pendiente
Módulo: Empresas
HU-17  Crear empresa en la plataforma
Historia de Usuario
Como usuario autenticado
quiero crear una nueva empresa u organización dentro de 
Star
t
Link
para gestionar mis procesos de recursos humanos y publicar ofertas desde mi propia empresa
Criterios de Aceptación:
•   Cualquier usuario autenticado puede crear una empresa.
•   Al crear la empresa, el usuario adopta el rol de Administrador de Empresa.
•   La empresa requiere obligatoriamente nombre de la empresa y un correo electrónico de contacto válido. Se puede aportar el logo opcionalmente.
•   El usuario creador queda automáticamente vinculado como Administrador de Empresa.
•   Se genera notificación de confirmación de creación.
Módulo
Empresas
Prioridad
Alta
Estimación
5 pts
Estado
Pendiente
HU-18  Gestionar información de la empresa
Historia de Usuario
Como Administrador de Empresa
quiero ver, editar y actualizar la información de mi empresa (nombre, descripción, logo, contacto, URL web)
para mantener la presencia institucional de mi empresa actualizada en la plataforma
Criterios de Aceptación:
•   El Administrador de Empresa puede editar todos los datos de la empresa.
•   Puede subir y reemplazar el logo (JPG/PNG, máx. 5 MB).
•   Los cambios se reflejan inmediatamente en los listados públicos.
•   Solo el Administrador de Empresa puede editar la información.
•   Se registra quién y cuándo realizó el último cambio.
Módulo
Empresas
Prioridad
Media
Estimación
3 pts
Estado
Pendiente
HU-19  Seleccionar empresa activa (multi-empresa)
Historia de Usuario
Como Administrador de Empresa asociado a múltiples empresas
quiero seleccionar qué empresa deseo gestionar cuando estoy vinculado a más de una
para administrar cada empresa de forma independiente sin confusión
Criterios de Aceptación:
•   La sección 
'Mi Equipo
' muestra todas las empresas a las que pertenece el usuari
o
.
•   El usuario selecciona la empresa activa y el sistema carga el contexto correspondiente.
•   Las acciones realizadas (publicar oferta, gestionar equipo) quedan asociadas a la empresa activa.
•   Se puede cambiar de empresa activa en cualquier momento sin cerrar sesión.
Módulo
Empresas
Prioridad
Media
Estimación
3 pts
Estado
Pendiente
Módulo: Equipo Interno
HU-20  Ver miembros del equipo
Historia de Usuario
Como Administrador de Empresa
quiero acceder a la sección 'Mi Equipo' y ver la información de mis compañeros (nombre, correo, foto, rol)
para conocer a los integrantes de mi empresa y facilitar la comunicación interna
Criterios de Aceptación:
•   Solo los usuarios asociados a una empresa pueden acceder.
•   Se muestran todos los miembros activos de la empresa con nombre, foto, correo y rol interno.
•   Si está en múltiples empresas, se le pide seleccionar cuál revisar.
•   La lista se actualiza automáticamente cuando se agregan o retiran miembros.
Módulo
Equipo Interno
Prioridad
Media
Estimación
3 pts
Estado
Pendiente
HU-21  Gestionar roles internos del equipo
Historia de Usuario
Como Administrador de Empresa
quiero gestionar los permisos internos de los usuarios de mi empresa
para controlar las responsabilidades en mi organización
Criterios de Aceptación:
•   El Administrador de Empresa puede cambiar los permisos internos de cualquier miembro asociado.
•   No puede eliminarse a sí mismo ni cambiar el rol del Dueño principal de la empresa.
•   Puede desvincular a un usuario de la empresa sin eliminar su cuenta global.
•   Los cambios de permisos generan notificaciones al usuario afectado.
•   Un usuario desvinculado pierde acceso a los recursos de la empresa.
Módulo
Equipo Interno
Prioridad
Alta
Estimación
5 pts
Estado
Pendiente
Módulo: Comunicación y Mensajería
HU-22  Chat privado entre usuarios
Historia de Usuario
Como usuario de la plataforma
quiero iniciar y mantener conversaciones privadas uno a uno con otros usuarios
para comunicarme directamente con candidatos, compañeros de empresa o contratadores
Criterios de Aceptación:
Cualquier usuario puede iniciar un chat privado desde el perfil de otro usuario.
Se pueden enviar y recibir mensajes de texto.
El historial completo de mensajes está disponible en todo momento.
El usuario puede eliminar chats y marcarlos como favoritos.
Los mensajes se muestran
 con
 hora
.
Módulo
Comunicación
Prioridad
Alta
Estimación
8 pts
Estado
Pendiente
HU-23  Chat grupal interno de empresa
Historia de Usuario
Como Administrador de Empresa
quiero crear chats grupales seleccionando miembros de mi empresa
para facilitar la coordinación y comunicación en equipo dentro de la organización
Criterios de Aceptación:
El usuario puede seleccionar múltiples miembros de su empresa para crear un chat grupal.
Se puede definir un nombre para el grupo.
Todos los participantes pueden enviar y recibir mensajes.
Se muestra la lista de participantes de cada grupo.
El creador puede agregar o remover participantes.
Módulo
Comunicación
Prioridad
Media
Estimación
8 pts
Estado
Pendiente
HU-24  Chat grupal de postulantes a oferta
Historia de Usuario
Como Administrador de Empresa
quiero crear un chat grupal con todos los postulantes a una oferta específica
para comunicarme con múltiples candidatos de forma simultánea y eficiente
Criterios de Aceptación:
•   El Administrador de Empresa puede crear el chat grupal desde la lista de postulantes de una oferta, o chats privados directamente.
•   Todos los postulantes activos son agregados automáticamente al grupo.
•   Se puede enviar mensajes de texto al grupo.
•   El grupo se puede disolver o archivar tras cerrar la oferta.
Módulo
Comunicación
Prioridad
Media
Estimación
5 pts
Estado
Pendiente
Módulo: Nómina
HU-25  Generar nómina para trabajadores
Historia de Usuario
Como Administrador de Empresa
quiero generar nóminas para los trabajadores de mi empresa registrando horas, salario base, deducciones y bonificaciones
para automatizar el cálculo de pagos y mantener un registro formal de la remuneración
Criterios de Aceptación:
•   
El Administrador de Empresa puede generar nóminas para cualquier miembro activo de su empresa (excepto para sí mismo).
•   El formulario requiere horas trabajadas, tarifa por hora, deducciones, bonificaciones, horas extras y validación de fecha inicio/fin.
•   Cada nómina generada queda almacenada en el historial laboral del receptor.
•   Se expide una notificación automática al usuario al generarse su nómina.
Módulo
Nómina
Prioridad
Alta
Estimación
8 pts
Estado
Pendiente
HU-26  Descargar recibo de nómina en PDF
Historia de Usuario
Como Usuario o Administrador de Empresa
quiero descargar o imprimir las nóminas generadas en formato HTML o PDF comprimible
para tener un comprobante formal de mi remuneración o para registros contables de la empresa
Criterios de Aceptación:
•   El Usuario puede descargar sus propias nóminas en PDF/HTML desde su historial.
•   El Administrador de Empresa puede descargar nóminas de cualquier trabajador de su empresa.
•   El PDF incluye todos los detalles: período, horas, salario bruto, deducciones, bonificaciones y salario neto.
•   El nombre del archivo incluye el nombre del trabajador y el período de la nómina.
•   La descarga funciona desde cualquier dispositivo con navegador compatible.
Módulo
Nómina
Prioridad
Alta
Estimación
3 pts
Estado
Pendiente
Módulo: Historial Laboral
HU-27  Consultar historial laboral propio
Historia de Usuario
Como Usuario
quiero acceder a mi historial de desempeño y todas las nóminas que las empresas me han asignado
para tener un registro completo y transparente de mi trayectoria laboral dentro de la plataforma
Criterios de Aceptación:
•   El Usuario accede a 'Mi Historial Laboral' desde su menú principal.
•   Se muestran todas las nóminas con fecha, empresa, período y montos.
•   Se muestran las evaluaciones de desempeño recibidas.
•   El historial implementa paginación para evitar sobrecarga de información.
•   Cada entrada de nómina permite ver el detalle completo.
Módulo
Historial Laboral
Prioridad
Alta
Estimación
5 pts
Estado
Pendiente
Módulo: Notificaciones
HU-28  Recibir y gestionar notificaciones
Historia de Usuario
Como usuario de la plataforma
quiero recibir notificaciones sobre eventos relevantes (mensajes, postulaciones, nóminas, solicitudes) y marcarlas como leídas o no leídas
para estar informado de manera centralizada sobre todas las actividades importantes que me conciernen
Criterios de Aceptación:
•   Existe un portal de notificaciones unificado accesible.
•   Las notificaciones incluyen: cambios de estado de postulación, nóminas generadas, cambios de rol y solicitudes de contratación.
•   Cada notificación muestra título, descripción breve, fecha/hora y un enlace al elemento relevante.
•   El usuario puede marcar notificaciones como leídas individualmente o todas a la vez.
•   Un indicador visual (badge/contador) muestra la cantidad de notificaciones no leídas.
Módulo
Notificaciones
Prioridad
Alta
Estimación
5 pts
Estado
Pendiente
Módulo: Desempeño
HU-29  Registrar evaluación de desempeño
Historia de Usuario
Como Administrador de Empresa
quiero registrar evaluaciones de desempeño para los integrantes de mi empresa con puntuaciones y comentarios
para llevar un seguimiento objetivo del rendimiento del equipo y retroalimentar a los usuarios
Criterios de Aceptación:
•   El Administrador de Empresa puede crear una evaluación de desempeño para cualquier miembro asociado.
•   La evaluación incluye: período evaluado, puntuación numérica o por categorías y comentarios.
•   El usuario recibe una notificación al registrarse una nueva evaluación.
•   El usuario puede visualizar todas sus evaluaciones desde su historial laboral.
•   Las evaluaciones quedan vinculadas al período y empresa correspondientes.
Módulo
Desempeño
Prioridad
Media
Estimación
5 pts
Estado
Pendiente
Módulo: Administración Global
HU-3
0
  Gestión global de usuarios (ADMINISTRADOR)
Historia de Usuario
Como ADMINISTRADOR global de la plataforma
quiero visualizar, editar y gestionar roles globales de cualquier usuario registrado en la plataforma
para mantener el control y la integridad de todos los perfiles y accesos del sistema
Criterios de Aceptación:
El ADMINISTRADOR puede buscar cualquier usuario por nombre o correo.
Puede ver y editar el rol global de cualquier usuario.
Puede suspender temporalmente una cuenta de usuario.
Puede acceder a los datos completos de cualquier empresa registrada.
Todos sus cambios quedan registrados en el log de auditoría
.
Módulo
Administración
Prioridad
Alta
Estimación
5 pts
Estado
Pendiente
Historias Técnicas o de Soporte
Las siguientes historias representan tareas técnicas necesarias para el correcto funcionamiento del sistema, aunque no son directamente visibles para el usuario final.
HT-
01 Sistema
 de roles y permisos (RBAC)
Historia de Usuario
Como desarrollador del sistema
quiero implementar cifrado de contraseñas con bcrypt/Argon2, HTTPS en todas las comunicaciones y registro de eventos de auditoría
para proteger los datos sensibles de usuarios y empresas cumpliendo con los requisitos de seguridad definidos
Criterios de Aceptación:
•   Todas las contraseñas se almacenan con bcrypt o Argon2 y salt único.
•   Toda comunicación se realiza sobre HTTPS con certificado TLS/SSL válido.
•   El sistema registra: inicios de sesión (exitosos y fallidos), cambios de rol, generación de nóminas y acceso a datos sensibles.
•   Los logs de auditoría son inmutables y solo accesibles por perfiles con privilegios del sistema.
•   Las pruebas verifican que ningún dato sensible se transmite en texto claro.
Módulo
Infraestructura
Prioridad
Alta
Estimación
8 pts
Estado
Pendiente
HT-02  Seguridad: cifrado y HTTPS
Historia de Usuario
Como desarrollador del sistema
quiero diseñar e implementar la base de datos MySQL con índices, normalización estratégica y soporte para paginación
para garantizar el rendimiento del sistema ante un crecimiento significativo en volumen de datos
Criterios de Aceptación:
Todas las contraseñas se almacenan con bcrypt o Argon2 y salt único.
Toda comunicación se realiza sobre HTTPS con certificado TLS/SSL válido.
El sistema registra: inicios de sesión (exitosos y fallidos), cambios de rol, generación de nóminas y acceso a datos sensibles.
Los logs de auditoría son inmutables y solo accesibles por ADMINISTRADOR global y Admin de Empresa.
Las pruebas verifican que ningún dato sensible se transmite en texto claro.
Módulo
Infraestructura
Prioridad
Alta
Estimación
5 pts
Estado
Pendiente
HT-03  Base de datos y escalabilidad
Historia de Usuario
Como desarrollador del sistema
quiero diseñar e implementar la base de datos MySQL con índices, normalización estratégica y soporte para paginación
para garantizar el rendimiento del sistema ante un crecimiento significativo en volumen de datos
Criterios de Aceptación:
•   Las tablas principales tienen índices en columnas de búsqueda frecuente.
•   La paginación está implementada en historial laboral, notificaciones y listas de candidatos.
•   El tiempo de respuesta para 'Configurar perfil' es menor a 3 segundos con 200 usuarios concurrentes.
•   El esquema soporta millones de registros en tablas de mensajes, nóminas y usuarios sin degradación crítica.
•   Se documentan las decisiones de normalización/desnormalización estratégica.
Módulo
Infraestructura
Prioridad
Alta
Estimación
8 pts
Estado
Pendiente
Notas y Consideraciones Globales
Evolución del documento
Se espera que las historias sean refinadas a lo largo del desarrollo. Cualquier cambio debe ser acordado entre el Product Owner, el equipo de desarrollo y el instructor, quedando reflejado en la sección 8 (Revisión y Cambios).
Validación de historias
Cada historia se considerará 'Aceptada' cuando todos sus criterios de aceptación sean verificados mediante pruebas funcionales y el Product Owner otorgue su aprobación formal.
Política de 'Definición de Terminado' (DoD)
El código ha sido revisado y aprobado.
Las pruebas unitarias y de integración correspondientes pasan exitosamente.
La funcionalidad ha sido validada en el entorno de staging.
El Product Owner ha revisado y aceptado la historia.
La documentación técnica relevante ha sido actualizada.
Módulos marcados como PENDIENTES:
Las funcionalidades de Seguimiento de Desempeño (RF.DES.001) y Vacaciones (RF.VAC.001, RF.VAC.002) están marcadas como pendientes en el DRS original. Las historias HU-29, HU-30 
y HU-31 cubren estos requisitos y deben ser priorizadas en sprints posteriores, una vez completados los módulos base.
Revisión y Cambios
Versión
Fecha
Descripción
Responsable
1.
1
27
-0
2
-202
6
Documento inicial con 32 historias de usuario y 3 historias técnicas, cubriendo todos los módulos del DRS de 
Star
t
Link.
Jose Julian Guaza Botero