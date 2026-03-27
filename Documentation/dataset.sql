-- ===========================================

CREATE TABLE rol (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_rol VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE empresa (
    id_empresa INT AUTO_INCREMENT PRIMARY KEY,
    nombre_empresa VARCHAR(255) NOT NULL UNIQUE,
    descripcion TEXT,
    email_contacto VARCHAR(255),
    telefono_contacto VARCHAR(50),
    pais VARCHAR(100),
    departamento VARCHAR(100),
    ciudad VARCHAR(100),
    url_sitio_web VARCHAR(255),
    logo_ruta VARCHAR(255),
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    estado VARCHAR(50) DEFAULT 'Activa',
    ultima_actualizacion DATETIME NULL,
    ultimo_editor_id INT NULL
);

CREATE TABLE rol_empresa (
    id_rol_empresa INT AUTO_INCREMENT PRIMARY KEY,
    nombre_rol_empresa VARCHAR(100) NOT NULL UNIQUE,
    descripcion TEXT
);

CREATE TABLE usuario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    dni VARCHAR(255) NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    contrasena_hash VARCHAR(255) NOT NULL,
    genero VARCHAR(20) NULL,
    cargo VARCHAR(100) NULL,
    fecha_ingreso DATE NULL,
    salario_base DOUBLE NULL,
    id_rol INT NOT NULL,
    telefono VARCHAR(50) NULL,
    pais VARCHAR(100) NULL,
    departamento VARCHAR(100) NULL,
    ciudad VARCHAR(100) NULL,
    foto_perfil VARCHAR(255) NULL,
    ruta_hdv VARCHAR(255) NULL,
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    intentos_fallidos INT NOT NULL DEFAULT 0,
    bloqueado_hasta DATETIME NULL,
    reset_code VARCHAR(20) NULL,
    reset_expire DATETIME NULL,
    id_empresa INT NULL, -- Para compatibilidad con tu controlador
    id_rol_empresa INT NULL,
    current_chat_id INT NULL, -- Seguimiento del chat activo para silenciado inteligente
    FOREIGN KEY (id_rol) REFERENCES rol(id),
    FOREIGN KEY (current_chat_id) REFERENCES conversacion(id_conversacion) ON DELETE SET NULL
);

CREATE TABLE usuario_empresa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_empresa INT NOT NULL,
    id_rol_empresa INT NOT NULL,
    fecha_union DATETIME DEFAULT CURRENT_TIMESTAMP,
    horas_semanales_estandar DECIMAL(5,2) NULL,
    UNIQUE KEY uniq_usuario_empresa (id_usuario, id_empresa),
    FOREIGN KEY (id_usuario) REFERENCES usuario(id) ON DELETE CASCADE,
    FOREIGN KEY (id_empresa) REFERENCES empresa(id_empresa) ON DELETE CASCADE,
    FOREIGN KEY (id_rol_empresa) REFERENCES rol_empresa(id_rol_empresa)
);

-- ===========================================
-- 2. RECURSOS HUMANOS
-- ===========================================

CREATE TABLE nomina (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    horas_trabajadas DOUBLE NOT NULL,
    salario_bruto DOUBLE NOT NULL,
    deducciones DOUBLE NOT NULL,
    salario_neto DOUBLE NOT NULL,
    fecha_generacion DATE NOT NULL,
    fecha_inicio_periodo DATE NOT NULL,
    fecha_fin_periodo DATE NOT NULL,
    ruta_pdf_nomina VARCHAR(255) NULL,
    bonificaciones DOUBLE DEFAULT 0,
    horas_extras DECIMAL(10, 2) DEFAULT 0,
    FOREIGN KEY (id_usuario) REFERENCES usuario(id) ON DELETE CASCADE
);

CREATE TABLE seguimiento_desempeño (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    evaluador_id INT NULL,
    fecha_evaluacion DATE NOT NULL,
    tipo_evaluacion VARCHAR(50) NOT NULL,
    puntuacion FLOAT,
    comentarios VARCHAR(500),
    objetivos_logrados VARCHAR(500),
    FOREIGN KEY (id_usuario) REFERENCES usuario(id) ON DELETE CASCADE,
    FOREIGN KEY (evaluador_id) REFERENCES usuario(id) ON DELETE SET NULL,
    CHECK (puntuacion IS NULL OR (puntuacion BETWEEN 0.0 AND 5.0))
);

CREATE TABLE capacitacion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_capacitacion VARCHAR(100) NOT NULL,
    descripcion VARCHAR(500) NOT NULL,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    costo DOUBLE NOT NULL,
    creador_id INT NULL,
    FOREIGN KEY (creador_id) REFERENCES usuario(id) ON DELETE SET NULL
);

CREATE TABLE inscripcion (
    id_usuario INT NOT NULL,
    id_capacitacion INT NOT NULL,
    fecha_inscripcion DATE NOT NULL,
    estado_inscripcion VARCHAR(50) NOT NULL,
    PRIMARY KEY (id_usuario, id_capacitacion),
    FOREIGN KEY (id_usuario) REFERENCES usuario(id) ON DELETE CASCADE,
    FOREIGN KEY (id_capacitacion) REFERENCES capacitacion(id) ON DELETE CASCADE
);

CREATE TABLE solicitud_vacaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    dias_solicitados INT NOT NULL,
    estado_solicitud VARCHAR(50) NOT NULL,
    fecha_solicitud DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_aprobacion DATE NULL,
    aprobador_id INT NULL,
    comentarios_aprobador VARCHAR(500),
    FOREIGN KEY (id_usuario) REFERENCES usuario(id) ON DELETE CASCADE,
    FOREIGN KEY (aprobador_id) REFERENCES usuario(id) ON DELETE SET NULL
);

-- ===========================================
-- 3. TALENTO Y EMPLEO
-- ===========================================

CREATE TABLE habilidad (
    id_habilidad INT AUTO_INCREMENT PRIMARY KEY,
    nombre_habilidad VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE experiencia_laboral (
    id_experiencia INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    titulo_puesto VARCHAR(255) NOT NULL,
    empresa_nombre VARCHAR(255) NOT NULL,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NULL,
    descripcion TEXT,
    FOREIGN KEY (id_usuario) REFERENCES usuario(id) ON DELETE CASCADE
);

CREATE TABLE estudio (
    id_estudio INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    titulo_grado VARCHAR(255) NOT NULL,
    institucion VARCHAR(255) NOT NULL,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NULL,
    descripcion TEXT,
    FOREIGN KEY (id_usuario) REFERENCES usuario(id) ON DELETE CASCADE
);

CREATE TABLE usuario_habilidad (
    id_usuario INT NOT NULL,
    id_habilidad INT NOT NULL,
    nivel_dominio VARCHAR(50),
    PRIMARY KEY (id_usuario, id_habilidad),
    FOREIGN KEY (id_usuario) REFERENCES usuario(id) ON DELETE CASCADE,
    FOREIGN KEY (id_habilidad) REFERENCES habilidad(id_habilidad) ON DELETE CASCADE
);

CREATE TABLE perfil_busqueda_empleo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL UNIQUE,
    fecha_activacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    esta_disponible BOOLEAN DEFAULT TRUE,
    titulo_buscado VARCHAR(100),
    tipo_contrato_preferido VARCHAR(50),
    modalidad_preferida VARCHAR(50),
    expectativa_salarial DOUBLE,
    habilidades_clave TEXT,
    notas_perfil TEXT,
    FOREIGN KEY (id_usuario) REFERENCES usuario(id) ON DELETE CASCADE
);

-- ===========================================
-- 4. OFERTAS Y MENSAJERÍA
-- ===========================================

CREATE TABLE oferta_trabajo (
    id_oferta INT AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT NOT NULL,
    id_creador_oferta INT NOT NULL,
    titulo_oferta VARCHAR(255) NOT NULL,
    descripcion_oferta TEXT NOT NULL,
    presupuesto_min DECIMAL(10,2) NULL,
    presupuesto_max DECIMAL(10,2) NULL,
    fecha_publicacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_cierre DATE,
    estado_oferta VARCHAR(50) DEFAULT 'Abierta',
    ubicacion VARCHAR(255),
    modalidad VARCHAR(50),
    requisitos TEXT,
    limite_participantes INT DEFAULT NULL,
    FOREIGN KEY (id_empresa) REFERENCES empresa(id_empresa) ON DELETE CASCADE,
    FOREIGN KEY (id_creador_oferta) REFERENCES usuario(id) ON DELETE CASCADE
);

CREATE TABLE postulacion (
    id_postulacion INT AUTO_INCREMENT PRIMARY KEY,
    id_oferta INT NOT NULL,
    id_usuario INT NOT NULL,
    fecha_postulacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    estado_postulacion ENUM('Postulado', 'Contratado', 'Rechazado', 'Oferta Enviada', 'Oferta Aceptada', 'Oferta Rechazada') DEFAULT 'Postulado',
    mensaje_postulacion TEXT,
    rechazo_permanente TINYINT(1) DEFAULT 0,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_oferta) REFERENCES oferta_trabajo(id_oferta) ON DELETE CASCADE,
    FOREIGN KEY (id_usuario) REFERENCES usuario(id) ON DELETE CASCADE
);

CREATE TABLE conversacion (
    id_conversacion INT AUTO_INCREMENT PRIMARY KEY,
    tipo_conversacion ENUM('oferta_grupal', 'oferta_privada', 'empresa_interna', 'perfil_publico') NOT NULL,
    id_proyecto INT NULL,
    titulo_conversacion VARCHAR(255),
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    ultimo_mensaje DATETIME NULL,
    FOREIGN KEY (id_proyecto) REFERENCES oferta_trabajo(id_oferta) ON DELETE SET NULL
);

CREATE TABLE conversacion_metadata (
    id_metadata INT AUTO_INCREMENT PRIMARY KEY,
    id_conversacion INT NOT NULL,
    clave VARCHAR(50) NOT NULL,
    valor TEXT,
    FOREIGN KEY (id_conversacion) REFERENCES conversacion(id_conversacion) ON DELETE CASCADE,
    UNIQUE KEY (id_conversacion, clave)
);

CREATE TABLE conversacion_participante (
    id_conversacion INT NOT NULL,
    id_usuario INT NOT NULL,
    fecha_union DATETIME DEFAULT CURRENT_TIMESTAMP,
    notificaciones_activas BOOLEAN DEFAULT TRUE,
    es_favorito BOOLEAN DEFAULT FALSE,
    PRIMARY KEY (id_conversacion, id_usuario),
    FOREIGN KEY (id_conversacion) REFERENCES conversacion(id_conversacion) ON DELETE CASCADE,
    FOREIGN KEY (id_usuario) REFERENCES usuario(id) ON DELETE CASCADE
);

CREATE TABLE mensaje (
    id_mensaje INT AUTO_INCREMENT PRIMARY KEY,
    id_conversacion INT NOT NULL,
    id_remitente INT NOT NULL,
    contenido TEXT NOT NULL,
    tipo_mensaje ENUM('normal', 'sistema', 'contratacion', 'rechazo', 'oferta_enviada', 'oferta_aceptada', 'oferta_rechazada', 'solicitud_contratacion_enviada', 'solicitud_contratacion_aceptada', 'solicitud_contratacion_rechazada') DEFAULT 'normal',
    metadata JSON DEFAULT NULL,
    fecha_envio DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_leido DATETIME NULL,
    FOREIGN KEY (id_conversacion) REFERENCES conversacion(id_conversacion) ON DELETE CASCADE,
    FOREIGN KEY (id_remitente) REFERENCES usuario(id) ON DELETE CASCADE
);

-- ===========================================
-- 5. DATOS MAESTROS (OBLIGATORIOS)
-- ===========================================

INSERT INTO rol (id, nombre_rol) VALUES 
(1, 'Administrador'), 
(2, 'Candidato'), 
(3, 'Empresa');

INSERT INTO rol_empresa (id_rol_empresa, nombre_rol_empresa) VALUES 
(1, 'Dueño'), 
(2, 'Reclutador'), 
(3, 'Empleado');

CREATE TABLE IF NOT EXISTS notificaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    mensaje TEXT NOT NULL,
    tipo ENUM('info', 'success', 'warning', 'error', 'contratacion', 'danger', 'chat') DEFAULT 'info',
    icono VARCHAR(100) DEFAULT 'fas fa-info-circle',
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    leida TINYINT(1) DEFAULT 0,
    url_redireccion VARCHAR(255) DEFAULT NULL,
    postulacion_id INT DEFAULT NULL,
    solicitud_contratacion_id INT DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES usuario(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;