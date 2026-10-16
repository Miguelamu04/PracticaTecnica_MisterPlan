

CREATE DATABASE IF NOT EXISTS misterplan_reservas_v2
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE misterplan_reservas_v2;

CREATE TABLE IF NOT EXISTS reserva (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre_huesped VARCHAR(150) NOT NULL,
    email_huesped VARCHAR(254) NOT NULL,
    nombre_alojamiento VARCHAR(200) NOT NULL,
    fecha_entrada DATE NOT NULL,
    fecha_salida DATE NOT NULL,
    estado ENUM('pendiente', 'confirmada', 'cancelada')
        NOT NULL DEFAULT 'pendiente',
    importe DECIMAL(10,2) NOT NULL,
    notas TEXT NULL,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;



CREATE TABLE IF NOT EXISTS reserva_evento (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    reserva_id BIGINT UNSIGNED NOT NULL,
    tipo ENUM('creacion', 'confirmacion', 'cancelacion') NOT NULL,
    descripcion VARCHAR(255) NOT NULL,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_evento_reserva
        FOREIGN KEY (reserva_id)
        REFERENCES reserva(id)
        ON DELETE RESTRICT
        ON UPDATE RESTRICT
) ENGINE=InnoDB;


ALTER TABLE reserva
    ADD INDEX idx_reserva_estado (estado),
    ADD INDEX idx_reserva_fechas (fecha_entrada, fecha_salida),
    ADD INDEX idx_reserva_huesped (nombre_huesped);

ALTER TABLE reserva_evento
    ADD INDEX idx_evento_reserva_fecha
    (reserva_id, fecha_creacion, id);

ALTER TABLE reserva
    ADD CONSTRAINT chk_reserva_fechas
        CHECK (fecha_salida > fecha_entrada),
    ADD CONSTRAINT chk_reserva_importe
        CHECK (importe >= 0);



INSERT INTO reserva (
    id,
    nombre_huesped,
    email_huesped,
    nombre_alojamiento,
    fecha_entrada,
    fecha_salida,
    estado,
    importe,
    notas,
    fecha_creacion
) VALUES
(
    1,
    'Ana García',
    'ana.garcia@example.com',
    'Hotel Plaza Mayor',
    '2026-11-03',
    '2026-11-06',
    'confirmada',
    345.00,
    'Llegada aproximada a las 18:00.',
    '2026-10-10 10:15:00'
),
(
    2,
    'Miguel López',
    'miguel.lopez@example.com',
    'Apartamento La Muralla',
    '2026-11-12',
    '2026-11-16',
    'pendiente',
    480.50,
    NULL,
    '2026-10-11 09:30:00'
),
(
    3,
    'Laura Sánchez',
    'laura.sanchez@example.com',
    'Casa Rural El Encinar',
    '2026-10-22',
    '2026-10-25',
    'cancelada',
    270.00,
    'Viaje familiar.',
    '2026-10-08 12:00:00'
),
(
    4,
    'David Fernández',
    'david.fernandez@example.com',
    'Hostal Centro',
    '2026-12-01',
    '2026-12-03',
    'confirmada',
    145.90,
    NULL,
    '2026-10-12 16:45:00'
),
(
    5,
    'Elena Martín',
    'elena.martin@example.com',
    'Apartamento La Muralla',
    '2026-11-05',
    '2026-11-08',
    'pendiente',
    312.00,
    'Solicita habitación tranquila.',
    '2026-10-13 11:20:00'
),
(
    6,
    'Carlos Ruiz',
    'carlos.ruiz@example.com',
    'Hotel Plaza Mayor',
    '2026-12-20',
    '2026-12-24',
    'cancelada',
    520.00,
    NULL,
    '2026-10-14 08:05:00'
),
(
    7,
    'Lucía Romero',
    'lucia.romero@example.com',
    'Hotel Costa Azul',
    '2026-11-18',
    '2026-11-22',
    'pendiente',
    410.00,
    'Solicita cama doble.',
    '2026-10-15 09:15:00'
),
(
    8,
    'Javier Moreno',
    'javier.moreno@example.com',
    'Apartamentos del Parque',
    '2026-12-08',
    '2026-12-12',
    'confirmada',
    385.75,
    NULL,
    '2026-10-15 11:30:00'
);




INSERT INTO reserva_evento (
    reserva_id,
    tipo,
    descripcion,
    fecha_creacion
) VALUES
(1, 'creacion', 'Reserva creada en estado pendiente', '2026-10-10 10:15:00'),
(1, 'confirmacion', 'Cambio de estado: pendiente a confirmada', '2026-10-10 10:20:00'),

(2, 'creacion', 'Reserva creada en estado pendiente', '2026-10-11 09:30:00'),

(3, 'creacion', 'Reserva creada en estado pendiente', '2026-10-08 12:00:00'),
(3, 'cancelacion', 'Cambio de estado: pendiente a cancelada', '2026-10-09 14:00:00'),

(4, 'creacion', 'Reserva creada en estado pendiente', '2026-10-12 16:45:00'),
(4, 'confirmacion', 'Cambio de estado: pendiente a confirmada', '2026-10-12 16:47:00'),

(5, 'creacion', 'Reserva creada en estado pendiente', '2026-10-13 11:20:00'),

(6, 'creacion', 'Reserva creada en estado pendiente', '2026-10-14 08:05:00'),
(6, 'cancelacion', 'Cambio de estado: pendiente a cancelada', '2026-10-14 12:25:00'),

(7, 'creacion', 'Reserva creada en estado pendiente', '2026-10-15 09:15:00'),

(8, 'creacion', 'Reserva creada en estado pendiente', '2026-10-15 11:30:00'),
(8, 'confirmacion', 'Cambio de estado: pendiente a confirmada', '2026-10-15 11:35:00');
