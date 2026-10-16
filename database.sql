CREATE DATABASE IF NOT EXISTS sistema_reservas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sistema_reservas;
CREATE TABLE reserva (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre_huesped VARCHAR(255) NOT NULL,
    email_huesped VARCHAR(255) NOT NULL,
    nombre_alojamiento VARCHAR(255) NOT NULL,
    fecha_entrada DATE NOT NULL,
    fecha_salida DATE NOT NULL,
    estado ENUM(
        'pendiente',
        'confirmada',
        'cancelada'
    ),
    importe DECIMAL(10,2) NOT NULL,
    notas TEXT,
    fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_estado_reserva (estado),
    INDEX idx_fechas_reserva (fecha_entrada,fecha_salida),
    INDEX idx_huesped_reserva (nombre_huesped)

);
CREATE TABLE reserva_evento(
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    reserva_id INT UNSIGNED NOT NULL,
    tipo VARCHAR(50) NOT NULL,
    descripcion VARCHAR(255) NOT NULL,
    fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_reserva_fecha_evento (reserva_id, fecha_creacion),

    CONSTRAINT fk_evento_reserva
    FOREIGN KEY (reserva_id)
    REFERENCES reserva(id)
    ON DELETE RESTRICT
);
INSERT INTO reserva (
    nombre_huesped,
    email_huesped,
    nombre_alojamiento,
    fecha_entrada,
    fecha_salida,
    estado,
    importe,
    notas
) VALUES
(
    'Laura García',
    'laura@example.com',
    'Hotel Alameda',
    '2026-11-10',
    '2026-11-13',
    'confirmada',
    285.00,
    'Habitación doble'
),
(
    'Daniel Martín',
    'daniel@example.com',
    'Apartamentos Centro',
    '2026-12-01',
    '2026-12-05',
    'pendiente',
    360.50,
    NULL
),
(
    'Marta López',
    'marta@example.com',
    'Casa Rural El Olivo',
    '2026-10-20',
    '2026-10-22',
    'cancelada',
    175.00,
    'Cancelación solicitada por el huésped'
);

INSERT INTO reserva_evento (
    reserva_id,
    tipo,
    descripcion
) VALUES
(1, 'creacion', 'Reserva creada'),
(1, 'confirmacion', 'Reserva confirmada'),
(2, 'creacion', 'Reserva creada'),
(3, 'creacion', 'Reserva creada'),
(3, 'cancelacion', 'Reserva cancelada');