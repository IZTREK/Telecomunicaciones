/* =============================================================
   PerroniPhone — Script de base de datos (SQL Server)
   Ejecutar completo en SQL Server Management Studio (SSMS).
   Todos los usuarios y datos son ficticios, con fines académicos.
   ============================================================= */

IF DB_ID('PerroniPhoneDB') IS NOT NULL
BEGIN
    ALTER DATABASE PerroniPhoneDB SET SINGLE_USER WITH ROLLBACK IMMEDIATE;
    DROP DATABASE PerroniPhoneDB;
END
GO

CREATE DATABASE PerroniPhoneDB;
GO

USE PerroniPhoneDB;
GO

-- =========================================================
-- Tabla: Usuarios
-- Las contraseñas NUNCA se guardan en texto plano: se guarda
-- un Salt aleatorio por usuario y el resultado de PBKDF2
-- (SHA-256, 100,000 iteraciones) sobre (password + salt).
-- =========================================================
CREATE TABLE Usuarios (
    Id                INT IDENTITY(1,1) PRIMARY KEY,
    CodigoUsuario     VARCHAR(10)   NOT NULL UNIQUE,      -- ej. U-1001
    Nombre            NVARCHAR(120) NOT NULL,
    Correo            NVARCHAR(150) NOT NULL UNIQUE,
    Rol               VARCHAR(20)   NOT NULL
                          CONSTRAINT CK_Usuarios_Rol CHECK (Rol IN ('administrador','soporte','cliente')),
    Estado            VARCHAR(20)   NOT NULL DEFAULT 'activo'
                          CONSTRAINT CK_Usuarios_Estado CHECK (Estado IN ('activo','bloqueado')),
    Salt              VARCHAR(64)   NOT NULL,
    HashPassword      VARCHAR(128)  NOT NULL,
    IntentosFallidos  INT           NOT NULL DEFAULT 0,
    BloqueadoHasta    DATETIME2     NULL,                 -- bloqueo temporal por fuerza bruta
    UltimoAcceso      DATETIME2     NULL,
    FechaCreacion     DATETIME2     NOT NULL DEFAULT SYSUTCDATETIME()
);
GO

-- =========================================================
-- Tabla: Permisos — qué puede hacer cada rol (RBAC)
-- =========================================================
CREATE TABLE Permisos (
    Id      INT IDENTITY(1,1) PRIMARY KEY,
    Rol     VARCHAR(20) NOT NULL,
    Permiso VARCHAR(50) NOT NULL,
    CONSTRAINT UQ_Permisos_RolPermiso UNIQUE (Rol, Permiso)
);
GO

-- =========================================================
-- Tabla: Planes (catálogo público, sin datos sensibles)
-- =========================================================
CREATE TABLE Planes (
    Id            VARCHAR(10)  PRIMARY KEY,
    Nombre        NVARCHAR(60) NOT NULL,
    Velocidad     NVARCHAR(30) NOT NULL,
    DatosMoviles  NVARCHAR(30) NOT NULL,
    Precio        NVARCHAR(30) NOT NULL
);
GO

-- =========================================================
-- Tabla: Bitácora — auditoría de eventos de seguridad
-- =========================================================
CREATE TABLE Bitacora (
    Id         INT IDENTITY(1,1) PRIMARY KEY,
    Fecha      DATETIME2 NOT NULL DEFAULT SYSUTCDATETIME(),
    Tipo       VARCHAR(10) NOT NULL
                   CONSTRAINT CK_Bitacora_Tipo CHECK (Tipo IN ('ok','warn','danger')),
    Mensaje    NVARCHAR(300) NOT NULL,
    UsuarioId  INT NULL,
    CONSTRAINT FK_Bitacora_Usuarios FOREIGN KEY (UsuarioId) REFERENCES Usuarios(Id)
);
GO

-- =========================================================
-- Datos ficticios — Usuarios
-- Contraseñas de prueba (para usarlas en el formulario de login):
--   admin@perroniphone.edu.mx           -> Admin#2026
--   soporte@perroniphone.edu.mx         -> Soporte#2026
--   cliente@perroniphone.edu.mx         -> Cliente#2026
--   emilio.farias@perroniphone.edu.mx   -> Cliente#2026 (cuenta ya bloqueada)
-- =========================================================
INSERT INTO Usuarios (CodigoUsuario, Nombre, Correo, Rol, Estado, Salt, HashPassword, IntentosFallidos, UltimoAcceso)
VALUES
('U-1001', N'Marina Ibarra Cordero',  'admin@perroniphone.edu.mx',
 'administrador', 'activo',
 'd854992cd0cae14859ee91ab547eb71b', 'e4af2b856f5a7a3f927f04dc45581ecb459e55b1698e999303d818031451be11',
 0, '2026-08-12T09:14:00'),

('U-1002', N'Diego Salcedo Núñez', 'soporte@perroniphone.edu.mx',
 'soporte', 'activo',
 '40b1fd9068731ce39c7c3d7878a5797a', '1e745832629c52423207051a58804a2187fec04d544c900f560c2ba955a445c4',
 0, '2026-08-13T17:02:00'),

('U-1003', N'Renata Ochoa Villegas', 'cliente@perroniphone.edu.mx',
 'cliente', 'activo',
 '17c271ce27fd9530f6043ddb982a7db6', '9af36d131ed1c85a83c462922c1ed3ce390d570648914e884e24cf4e1d7f3e32',
 0, '2026-08-14T08:47:00'),

('U-1004', N'Emilio Farías Reyes', 'emilio.farias@perroniphone.edu.mx',
 'cliente', 'bloqueado',
 '869d767940948102dbb064c33d0bf66e', '400a32dfa5236dc21fdf7998accf380d6eda2859767e9f5aa6b06f64febd0674',
 5, '2026-07-30T11:20:00');
GO

-- =========================================================
-- Datos ficticios — Permisos por rol
-- =========================================================
INSERT INTO Permisos (Rol, Permiso) VALUES
('administrador','ver_dashboard'),
('administrador','gestionar_usuarios'),
('administrador','ver_bitacora'),
('administrador','editar_roles'),
('soporte','ver_dashboard'),
('soporte','ver_bitacora'),
('cliente','ver_dashboard');
GO

-- =========================================================
-- Datos ficticios — Planes
-- =========================================================
INSERT INTO Planes (Id, Nombre, Velocidad, DatosMoviles, Precio) VALUES
('P-10','PerroniPhone Esencial','50 Mbps','10 GB','$349 MXN'),
('P-20','PerroniPhone Plus','150 Mbps','30 GB','$549 MXN'),
('P-30','PerroniPhone Total','500 Mbps','Ilimitados','$799 MXN');
GO

-- =========================================================
-- Datos ficticios — Bitácora inicial
-- =========================================================
INSERT INTO Bitacora (Fecha, Tipo, Mensaje, UsuarioId) VALUES
('2026-08-14T08:47:12','ok',     N'Inicio de sesión correcto — cliente@perroniphone.edu.mx', 3),
('2026-08-14T08:44:05','warn',   N'Intento de acceso con formato de correo inválido', NULL),
('2026-07-30T07:58:41','danger', N'5º intento fallido — cuenta bloqueada automáticamente (U-1004)', 4),
('2026-08-12T07:12:30','ok',     N'Cambio de rol aplicado por U-1001 (administrador)', 1),
('2026-08-11T06:30:02','warn',   N'Token de sesión expirado — se solicitó nuevo inicio de sesión', NULL);
GO

PRINT 'Base de datos PerroniPhoneDB creada correctamente.';
