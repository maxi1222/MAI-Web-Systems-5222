-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `MAI_Panaderia`
--
CREATE DATABASE IF NOT EXISTS `MAI_Panaderia` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `MAI_Panaderia`;

-- Deshabilitar revisión de claves foráneas para borrado y creación limpia
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS detalle_pedidos;
DROP TABLE IF EXISTS pedidos;
DROP TABLE IF EXISTS productos;
DROP TABLE IF EXISTS usuarios;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE usuarios (
  id_usuario int(11) PRIMARY KEY AUTO_INCREMENT,
  nombre varchar(100) NOT NULL,
  email varchar(100) UNIQUE KEY NOT NULL,
  password varchar(255) NOT NULL,
  rol enum('Administrador','Empleado') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO usuarios (`nombre`, `email`, `password`, `rol`) VALUES
('Iñaki Barreda', 'iniaki.barreda@gmail.com', '$2y$10$uAG8IRfswNwOzDNUKDZ6sOxqFJIQKCMvntWB85EYTBf8oyGMqJqg.', 'Empleado'), 
('Alejandro García', 'garciafeijoalejandro@gmail.com', '$2y$10$b1iSAEpnkM.0zomdjiHeIeiIz6XGGtxr8XaG1CUF7OX86waer17Fq', 'Empleado'),
('Maximiliano Castellanos', 'castellanosmaxi030@gmail.com', '$2y$10$u1oJlW3og5gvu8EdHxqplOuyxQbcJO1giOpxH6A2BdV.pILTLHMVa', 'Empleado'),
('Admin', 'admin321@gmail.com', '$2y$10$zWICVbWeNTjKxayId/4B.eViEl3Enfa7btaOQdQN6h3NFPlSs9vvS', 'Administrador');

CREATE TABLE productos (
  id_producto int(11) PRIMARY KEY AUTO_INCREMENT,
  nombre varchar(100) NOT NULL,
  descripcion text DEFAULT NULL,
  precio int(11) NOT NULL,
  categoria varchar(50) NOT NULL,
  imagen_url varchar(255) DEFAULT NULL,
  disponible tinyint(1) DEFAULT 1,
  destacado tinyint(1) DEFAULT 0,
  dias_habilitados varchar(150) DEFAULT 'Lunes,Martes,Miercoles,Jueves,Viernes,Sabado,Domingo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE pedidos (
  id_pedido int(11) PRIMARY KEY AUTO_INCREMENT,
  nombre_cliente varchar(100) NOT NULL,
  telefono_cliente varchar(20) NOT NULL,
  fecha_retiro datetime NOT NULL,
  estado enum('Pendiente','En preparación','Listo','Entregado') DEFAULT 'Pendiente',
  fecha_modificacion datetime DEFAULT NULL ON UPDATE current_timestamp(),
  fecha_creacion datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE detalle_pedidos (
  id_detalle int(11) PRIMARY KEY AUTO_INCREMENT,
  id_pedido int(11) NOT NULL,
  id_producto int(11) NOT NULL,
  cantidad int(11) NOT NULL,
  precio_unitario int(11) DEFAULT NULL,
  FOREIGN KEY (id_pedido) REFERENCES pedidos (id_pedido) ON DELETE CASCADE,
  FOREIGN KEY (id_producto) REFERENCES productos (id_producto)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
