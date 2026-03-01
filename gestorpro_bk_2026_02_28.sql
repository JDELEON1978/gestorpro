-- MySQL dump 10.13  Distrib 8.4.3, for Win64 (x86_64)
--
-- Host: localhost    Database: torayaco_gestorpro
-- ------------------------------------------------------
-- Server version	8.4.3

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `audit_logs`
--

DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `usuario_id` bigint unsigned DEFAULT NULL,
  `entidad` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `entidad_id` bigint unsigned DEFAULT NULL,
  `accion` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `antes_json` json DEFAULT NULL,
  `despues_json` json DEFAULT NULL,
  `ip` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `audit_logs_usuario_id_foreign` (`usuario_id`),
  KEY `audit_logs_entidad_entidad_id_index` (`entidad`,`entidad_id`),
  CONSTRAINT `audit_logs_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_logs`
--

LOCK TABLES `audit_logs` WRITE;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
INSERT INTO `audit_logs` VALUES (1,2,'expedientes',1,'BOOTSTRAP',NULL,'{\"nodo_inicial_id\": 1}','127.0.0.1','2026-02-22 05:42:18','2026-02-22 05:42:18');
/*!40000 ALTER TABLE `audit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `catalogo_items`
--

DROP TABLE IF EXISTS `catalogo_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `catalogo_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `catalogo_id` bigint unsigned NOT NULL,
  `codigo` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `valor` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `catalogo_items_catalogo_id_activo_index` (`catalogo_id`,`activo`),
  CONSTRAINT `catalogo_items_catalogo_id_foreign` FOREIGN KEY (`catalogo_id`) REFERENCES `catalogos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `catalogo_items`
--

LOCK TABLES `catalogo_items` WRITE;
/*!40000 ALTER TABLE `catalogo_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `catalogo_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `catalogos`
--

DROP TABLE IF EXISTS `catalogos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `catalogos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `catalogos_nombre_unique` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `catalogos`
--

LOCK TABLES `catalogos` WRITE;
/*!40000 ALTER TABLE `catalogos` DISABLE KEYS */;
/*!40000 ALTER TABLE `catalogos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `evidencias`
--

DROP TABLE IF EXISTS `evidencias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `evidencias` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `expediente_item_id` bigint unsigned NOT NULL,
  `archivo_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime_type` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tamano_bytes` bigint unsigned DEFAULT NULL,
  `hash_sha256` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subido_por` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `evidencias_subido_por_foreign` (`subido_por`),
  KEY `evidencias_expediente_item_id_index` (`expediente_item_id`),
  CONSTRAINT `evidencias_expediente_item_id_foreign` FOREIGN KEY (`expediente_item_id`) REFERENCES `expediente_items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `evidencias_subido_por_foreign` FOREIGN KEY (`subido_por`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `evidencias`
--

LOCK TABLES `evidencias` WRITE;
/*!40000 ALTER TABLE `evidencias` DISABLE KEYS */;
/*!40000 ALTER TABLE `evidencias` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `expediente_items`
--

DROP TABLE IF EXISTS `expediente_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `expediente_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `expediente_id` bigint unsigned NOT NULL,
  `item_id` bigint unsigned NOT NULL,
  `nodo_id` bigint unsigned DEFAULT NULL,
  `estado` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendiente',
  `entregado_en` datetime DEFAULT NULL,
  `revisado_en` datetime DEFAULT NULL,
  `recibido_por` bigint unsigned DEFAULT NULL,
  `revisado_por` bigint unsigned DEFAULT NULL,
  `aprobado` tinyint(1) NOT NULL DEFAULT '0',
  `rechazado_regresar_a_nodo_id` bigint unsigned DEFAULT NULL,
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `exp_item_unique` (`expediente_id`,`nodo_id`,`item_id`),
  KEY `expediente_items_item_id_foreign` (`item_id`),
  KEY `expediente_items_nodo_id_foreign` (`nodo_id`),
  KEY `expediente_items_recibido_por_foreign` (`recibido_por`),
  KEY `expediente_items_revisado_por_foreign` (`revisado_por`),
  KEY `expediente_items_rechazado_regresar_a_nodo_id_foreign` (`rechazado_regresar_a_nodo_id`),
  KEY `expediente_items_expediente_id_estado_index` (`expediente_id`,`estado`),
  CONSTRAINT `expediente_items_expediente_id_foreign` FOREIGN KEY (`expediente_id`) REFERENCES `expedientes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `expediente_items_item_id_foreign` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `expediente_items_nodo_id_foreign` FOREIGN KEY (`nodo_id`) REFERENCES `nodos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `expediente_items_rechazado_regresar_a_nodo_id_foreign` FOREIGN KEY (`rechazado_regresar_a_nodo_id`) REFERENCES `nodos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `expediente_items_recibido_por_foreign` FOREIGN KEY (`recibido_por`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `expediente_items_revisado_por_foreign` FOREIGN KEY (`revisado_por`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `expediente_items`
--

LOCK TABLES `expediente_items` WRITE;
/*!40000 ALTER TABLE `expediente_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `expediente_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `expediente_productos_servicios`
--

DROP TABLE IF EXISTS `expediente_productos_servicios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `expediente_productos_servicios` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `expediente_id` bigint unsigned NOT NULL,
  `producto_servicio_id` bigint unsigned NOT NULL,
  `cantidad` decimal(14,4) NOT NULL DEFAULT '1.0000',
  `precio` decimal(14,4) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_eps_exp_prod` (`expediente_id`,`producto_servicio_id`),
  KEY `expediente_productos_servicios_producto_servicio_id_foreign` (`producto_servicio_id`),
  CONSTRAINT `expediente_productos_servicios_expediente_id_foreign` FOREIGN KEY (`expediente_id`) REFERENCES `expedientes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `expediente_productos_servicios_producto_servicio_id_foreign` FOREIGN KEY (`producto_servicio_id`) REFERENCES `productos_servicios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `expediente_productos_servicios`
--

LOCK TABLES `expediente_productos_servicios` WRITE;
/*!40000 ALTER TABLE `expediente_productos_servicios` DISABLE KEYS */;
/*!40000 ALTER TABLE `expediente_productos_servicios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `expediente_transiciones`
--

DROP TABLE IF EXISTS `expediente_transiciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `expediente_transiciones` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `expediente_id` bigint unsigned NOT NULL,
  `from_nodo_id` bigint unsigned DEFAULT NULL,
  `to_nodo_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `motivo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `expediente_transiciones_from_nodo_id_foreign` (`from_nodo_id`),
  KEY `expediente_transiciones_to_nodo_id_foreign` (`to_nodo_id`),
  KEY `expediente_transiciones_user_id_foreign` (`user_id`),
  KEY `exp_trans_exp_fecha_idx` (`expediente_id`,`created_at`),
  CONSTRAINT `expediente_transiciones_expediente_id_foreign` FOREIGN KEY (`expediente_id`) REFERENCES `expedientes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `expediente_transiciones_from_nodo_id_foreign` FOREIGN KEY (`from_nodo_id`) REFERENCES `nodos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `expediente_transiciones_to_nodo_id_foreign` FOREIGN KEY (`to_nodo_id`) REFERENCES `nodos` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `expediente_transiciones_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `expediente_transiciones`
--

LOCK TABLES `expediente_transiciones` WRITE;
/*!40000 ALTER TABLE `expediente_transiciones` DISABLE KEYS */;
/*!40000 ALTER TABLE `expediente_transiciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `expedientes`
--

DROP TABLE IF EXISTS `expedientes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `expedientes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `project_id` bigint unsigned DEFAULT NULL,
  `proceso_id` bigint unsigned NOT NULL,
  `nodo_actual_id` bigint unsigned DEFAULT NULL,
  `correlativo` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `titulo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'abierto',
  `creado_por` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `expedientes_proceso_id_correlativo_unique` (`proceso_id`,`correlativo`),
  KEY `expedientes_creado_por_foreign` (`creado_por`),
  KEY `expedientes_nodo_actual_id_foreign` (`nodo_actual_id`),
  KEY `expedientes_proc_nodo_actual_idx` (`proceso_id`,`nodo_actual_id`),
  KEY `exp_project_proceso_idx` (`project_id`,`proceso_id`),
  CONSTRAINT `expedientes_creado_por_foreign` FOREIGN KEY (`creado_por`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `expedientes_nodo_actual_id_foreign` FOREIGN KEY (`nodo_actual_id`) REFERENCES `nodos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `expedientes_proceso_id_foreign` FOREIGN KEY (`proceso_id`) REFERENCES `procesos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `expedientes_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `expedientes`
--

LOCK TABLES `expedientes` WRITE;
/*!40000 ALTER TABLE `expedientes` DISABLE KEYS */;
INSERT INTO `expedientes` VALUES (1,NULL,1,1,'20260221234218-1-T6YA','Expediente','abierto',2,'2026-02-22 05:42:18','2026-02-22 05:42:18'),(2,NULL,1,NULL,'PRJ-10','Nuevo proyecto','abierto',2,'2026-02-26 02:10:00','2026-02-26 02:10:00');
/*!40000 ALTER TABLE `expedientes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `funcion_role`
--

DROP TABLE IF EXISTS `funcion_role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `funcion_role` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `role_id` bigint unsigned NOT NULL,
  `funcion_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `funcion_role_role_id_funcion_id_unique` (`role_id`,`funcion_id`),
  KEY `funcion_role_funcion_id_foreign` (`funcion_id`),
  CONSTRAINT `funcion_role_funcion_id_foreign` FOREIGN KEY (`funcion_id`) REFERENCES `funciones` (`id`) ON DELETE CASCADE,
  CONSTRAINT `funcion_role_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `funcion_role`
--

LOCK TABLES `funcion_role` WRITE;
/*!40000 ALTER TABLE `funcion_role` DISABLE KEYS */;
/*!40000 ALTER TABLE `funcion_role` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `funciones`
--

DROP TABLE IF EXISTS `funciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `funciones` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `funciones_nombre_unique` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `funciones`
--

LOCK TABLES `funciones` WRITE;
/*!40000 ALTER TABLE `funciones` DISABLE KEYS */;
/*!40000 ALTER TABLE `funciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `indicador_valores`
--

DROP TABLE IF EXISTS `indicador_valores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `indicador_valores` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `indicador_id` bigint unsigned NOT NULL,
  `expediente_id` bigint unsigned DEFAULT NULL,
  `fecha` date NOT NULL,
  `valor` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `indicador_valores_expediente_id_foreign` (`expediente_id`),
  KEY `indicador_valores_indicador_id_fecha_index` (`indicador_id`,`fecha`),
  CONSTRAINT `indicador_valores_expediente_id_foreign` FOREIGN KEY (`expediente_id`) REFERENCES `expedientes` (`id`) ON DELETE SET NULL,
  CONSTRAINT `indicador_valores_indicador_id_foreign` FOREIGN KEY (`indicador_id`) REFERENCES `indicadores` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `indicador_valores`
--

LOCK TABLES `indicador_valores` WRITE;
/*!40000 ALTER TABLE `indicador_valores` DISABLE KEYS */;
/*!40000 ALTER TABLE `indicador_valores` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `indicadores`
--

DROP TABLE IF EXISTS `indicadores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `indicadores` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `formula` text COLLATE utf8mb4_unicode_ci,
  `frecuencia` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `indicadores_codigo_unique` (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `indicadores`
--

LOCK TABLES `indicadores` WRITE;
/*!40000 ALTER TABLE `indicadores` DISABLE KEYS */;
/*!40000 ALTER TABLE `indicadores` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `item_example_files`
--

DROP TABLE IF EXISTS `item_example_files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `item_example_files` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `item_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `original_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `size_bytes` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `item_example_files_item_id_foreign` (`item_id`),
  KEY `item_example_files_user_id_foreign` (`user_id`),
  CONSTRAINT `item_example_files_item_id_foreign` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `item_example_files_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `item_example_files`
--

LOCK TABLES `item_example_files` WRITE;
/*!40000 ALTER TABLE `item_example_files` DISABLE KEYS */;
INSERT INTO `item_example_files` VALUES (2,2,2,'check.pdf','item_examples/2/69a35e2325f94_check.pdf','application/pdf',268294,'2026-03-01 03:29:07','2026-03-01 03:29:07');
/*!40000 ALTER TABLE `item_example_files` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `items`
--

DROP TABLE IF EXISTS `items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `proceso_id` bigint unsigned NOT NULL,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `categoria` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_id` bigint unsigned DEFAULT NULL,
  `requiere_evidencia` tinyint(1) NOT NULL DEFAULT '1',
  `allowed_extensions` json NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `items_tipo_id_foreign` (`tipo_id`),
  KEY `items_proceso_id_categoria_index` (`proceso_id`,`categoria`),
  CONSTRAINT `items_proceso_id_foreign` FOREIGN KEY (`proceso_id`) REFERENCES `procesos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `items_tipo_id_foreign` FOREIGN KEY (`tipo_id`) REFERENCES `tipos_item` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `items`
--

LOCK TABLES `items` WRITE;
/*!40000 ALTER TABLE `items` DISABLE KEYS */;
INSERT INTO `items` VALUES (1,1,'Orden de Compra y Pago','DOCUMENTO',NULL,1,'[\"pdf\", \"docx\"]',1,'2026-02-19 03:52:14','2026-03-01 03:16:49'),(2,1,'Carta de credito','DOCUMENTO',NULL,1,'[\"pdf\", \"docx\"]',1,'2026-02-19 03:53:10','2026-02-28 22:40:58'),(3,1,'Forma 63-A2','FORMULARIO',NULL,1,'null',1,'2026-02-19 03:53:36','2026-02-19 03:53:36'),(4,1,'Creación de código','OPERACION',NULL,1,'null',1,'2026-02-19 03:54:10','2026-02-19 03:54:10');
/*!40000 ALTER TABLE `items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=63 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_02_14_191543_create_workspaces_table',1),(5,'2026_02_14_191552_create_workspace_user_table',1),(6,'2026_02_14_191600_create_projects_table',1),(7,'2026_02_14_191619_create_project_statuses_table',1),(8,'2026_02_14_191625_create_tasks_table',1),(9,'2026_02_14_191631_create_task_comments_table',1),(10,'2026_02_14_191636_create_task_files_table',1),(11,'2026_02_14_191642_create_task_activities_table',1),(12,'2026_02_14_211430_add_role_to_users_table',1),(13,'2026_02_14_212137_add_role_to_users_table',1),(14,'2026_02_14_222522_add_role_to_users_table',1),(15,'2026_02_15_051611_create_user_workspace_table',1),(16,'2026_02_15_063834_add_slug_to_projects_table',2),(17,'2026_02_15_073202_fix_tasks_table_for_mvp',3),(18,'2026_02_15_180823_add_color_to_project_statuses_table',4),(19,'2026_02_16_001927_add_start_at_to_tasks_table',5),(20,'2026_02_18_000001_create_procesos_table',6),(21,'2026_02_18_000002_create_nodos_table',6),(22,'2026_02_18_000003_create_nodo_relaciones_table',6),(23,'2026_02_18_000004_create_tipos_item_table',6),(24,'2026_02_18_000005_create_items_table',6),(25,'2026_02_18_000006_create_nodo_items_table',6),(26,'2026_02_18_000007_create_expedientes_table',6),(27,'2026_02_18_000008_create_expediente_items_table',6),(28,'2026_02_18_000009_create_evidencias_table',6),(29,'2026_02_18_000010_create_roles_funciones_tables',6),(30,'2026_02_18_000011_create_catalogos_tables',6),(31,'2026_02_18_000012_create_productos_servicios_tables',7),(32,'2026_02_18_000013_create_indicadores_tables',7),(33,'2026_02_18_000014_create_variables_control_table',7),(34,'2026_02_18_000015_create_audit_logs_table',7),(35,'2026_02_18_000016_add_process_fields_to_users_table',7),(36,'2026_02_18_213745_update_estado_column_in_procesos_table',8),(37,'2026_02_19_133129_position_to_nodos_table',9),(38,'2026_02_21_001415_add_responsable_descripcion_to_nodos_table',10),(39,'2026_02_21_224257_add_nodo_actual_id_to_expedientes_table',11),(40,'2026_02_21_224859_create_expediente_transiciones_table',12),(41,'2026_02_21_232823_expedientes.nodo_actual_id',13),(42,'2026_02_21_232959_add_unique_to_expediente_items',14),(43,'2026_02_22_152510_add_ports_to_nodos',15),(44,'2026_02_22_152538_add_ports_to_nodo_relaciones',15),(45,'2026_02_22_152854_2add_ports_to_nodos',16),(46,'2026_02_22_152905_2add_ports_to_nodo_relaciones',16),(47,'2026_02_22_161958_add_bend_to_nodo_relaciones',17),(48,'2026_02_22_191204_add_unique_nodo_id_item_id_to_nodo_items_table',18),(49,'2026_02_22_191340_add_fks_to_nodo_items_table',19),(50,'2026_02_22_192026_add_nodo_actual_id_to_expedientes_table',19),(51,'2026_02_25_040708_add_proceso_id_to_projects_table',20),(52,'2026_02_25_042525_add_project_id_to_expedientes_table',21),(53,'2026_02_25_043324_add_runtime_fields_to_tasks_table',22),(54,'2026_02_25_203300_add_nodo_id_to_tasks',23),(55,'2026_02_28_143120_add_trace_fields_to_tasks_table',24),(56,'2026_02_28_155713_add_tipos_json',25),(57,'2026_02_28_165301_crear_item_example_files',26),(58,'2026_02_28_165812_crear_relacion_task_files_item_id',27),(59,'2026_02_28_170439_crear_estado_para_files',28),(60,'2026_02_28_170938_crear_task_file_reviews',29),(61,'2026_02_28_171046_crear_task_file_review_comments',30),(62,'2026_03_01_004432_crear_task_evidences',31);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nodo_items`
--

DROP TABLE IF EXISTS `nodo_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `nodo_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nodo_id` bigint unsigned NOT NULL,
  `item_id` bigint unsigned NOT NULL,
  `obligatorio` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nodo_items_nodo_id_item_id_unique` (`nodo_id`,`item_id`),
  UNIQUE KEY `nodo_items_unique` (`nodo_id`,`item_id`),
  KEY `nodo_items_item_id_foreign` (`item_id`),
  CONSTRAINT `nodo_items_item_id_foreign` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `nodo_items_nodo_id_foreign` FOREIGN KEY (`nodo_id`) REFERENCES `nodos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nodo_items`
--

LOCK TABLES `nodo_items` WRITE;
/*!40000 ALTER TABLE `nodo_items` DISABLE KEYS */;
INSERT INTO `nodo_items` VALUES (2,1,1,1,'2026-02-23 05:24:17','2026-02-23 07:36:58'),(7,2,3,1,'2026-02-23 06:26:28','2026-02-24 10:06:20'),(8,1,3,1,'2026-02-23 07:36:58','2026-02-23 07:36:58'),(9,4,2,1,'2026-02-26 02:19:08','2026-03-01 03:25:19');
/*!40000 ALTER TABLE `nodo_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nodo_relaciones`
--

DROP TABLE IF EXISTS `nodo_relaciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `nodo_relaciones` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `proceso_id` bigint unsigned NOT NULL,
  `nodo_origen_id` bigint unsigned NOT NULL,
  `nodo_destino_id` bigint unsigned NOT NULL,
  `condicion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `prioridad` int unsigned NOT NULL DEFAULT '0',
  `out_side` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `out_offset` smallint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `bend_x` int DEFAULT NULL COMMENT 'Control X de la curva (canvas coords)',
  `bend_y` int DEFAULT NULL COMMENT 'Control Y de la curva (canvas coords)',
  PRIMARY KEY (`id`),
  KEY `nodo_relaciones_nodo_origen_id_foreign` (`nodo_origen_id`),
  KEY `nodo_relaciones_nodo_destino_id_foreign` (`nodo_destino_id`),
  KEY `nodo_relaciones_proceso_id_nodo_origen_id_index` (`proceso_id`,`nodo_origen_id`),
  CONSTRAINT `nodo_relaciones_nodo_destino_id_foreign` FOREIGN KEY (`nodo_destino_id`) REFERENCES `nodos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `nodo_relaciones_nodo_origen_id_foreign` FOREIGN KEY (`nodo_origen_id`) REFERENCES `nodos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `nodo_relaciones_proceso_id_foreign` FOREIGN KEY (`proceso_id`) REFERENCES `procesos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nodo_relaciones`
--

LOCK TABLES `nodo_relaciones` WRITE;
/*!40000 ALTER TABLE `nodo_relaciones` DISABLE KEYS */;
INSERT INTO `nodo_relaciones` VALUES (1,1,1,2,'Importacion',3,NULL,NULL,'2026-02-20 18:39:30','2026-02-23 07:36:57',NULL,NULL),(3,1,1,3,'Local',1,NULL,NULL,'2026-02-22 06:40:56','2026-02-23 07:36:57',NULL,NULL),(6,1,2,3,'Solicitar carta de credito',1,NULL,NULL,'2026-02-22 07:46:55','2026-02-24 10:06:19',NULL,NULL),(15,1,4,1,'Verifica carta de crédito.',1,NULL,NULL,'2026-02-23 06:41:37','2026-03-01 03:25:18',NULL,NULL),(16,1,3,5,'Verificar',1,NULL,NULL,'2026-02-23 07:01:02','2026-02-23 07:01:02',NULL,NULL),(17,1,5,6,'siguiente',1,NULL,NULL,'2026-02-27 19:22:09','2026-02-27 19:22:09',NULL,NULL);
/*!40000 ALTER TABLE `nodo_relaciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nodos`
--

DROP TABLE IF EXISTS `nodos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `nodos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `proceso_id` bigint unsigned NOT NULL,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_nodo` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'actividad',
  `responsable_rol_id` bigint unsigned DEFAULT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `orden` int unsigned NOT NULL DEFAULT '0',
  `pos_x` int NOT NULL DEFAULT '120',
  `pos_y` int NOT NULL DEFAULT '120',
  `in_side` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `in_offset` smallint unsigned DEFAULT NULL,
  `out_side` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `out_offset` smallint unsigned DEFAULT NULL,
  `sla_horas` int unsigned DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `nodos_proceso_id_orden_index` (`proceso_id`,`orden`),
  KEY `nodos_responsable_rol_id_foreign` (`responsable_rol_id`),
  CONSTRAINT `nodos_proceso_id_foreign` FOREIGN KEY (`proceso_id`) REFERENCES `procesos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `nodos_responsable_rol_id_foreign` FOREIGN KEY (`responsable_rol_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nodos`
--

LOCK TABLES `nodos` WRITE;
/*!40000 ALTER TABLE `nodos` DISABLE KEYS */;
INSERT INTO `nodos` VALUES (1,1,'1.- Iniciar trámite de apertura de carta de crédito','decision',4,'Determina el tipo de carta de crédito, crédito a aperturar, si es carta de crédito de importación continúa en Actividad 1.1; si es carta de crédito local continúa en Actividad 2.',2,480,17,NULL,NULL,NULL,NULL,1,1,'2026-02-19 04:00:35','2026-02-23 06:44:09'),(2,1,'1.1.- Solicitar documentos a contratista para el inicio de trámite de apertura de carta de crédito de importación','actividad',4,NULL,3,930,21,NULL,NULL,NULL,NULL,NULL,1,'2026-02-19 19:00:57','2026-02-24 10:06:20'),(3,1,'2.- Solicitar apertura de cata de crédito','actividad',2,NULL,3,483,204,NULL,NULL,NULL,NULL,NULL,1,'2026-02-19 19:01:27','2026-02-23 07:01:02'),(4,1,'Inicio','inicio',4,'solicitado por el proveedor.',1,126,20,NULL,NULL,NULL,NULL,NULL,1,'2026-02-22 07:39:37','2026-03-01 03:24:43'),(5,1,'3.- Verificar partida presupuestaria y renglón de gasto','actividad',2,NULL,5,479,399,NULL,NULL,NULL,NULL,NULL,1,'2026-02-22 07:40:18','2026-02-27 19:22:28'),(6,1,'4.- Envia expediente completo para revisión, visa y control','actividad',4,'Envia Orden de compra y pagpo Manual en expediente completo con los documentos para apertura de carta de crédito, (Anexo 1 y 2) para primera revisión al departamento de control de calidad financiera,',6,484,615,NULL,NULL,NULL,NULL,1,1,'2026-02-27 19:21:11','2026-02-27 22:55:12');
/*!40000 ALTER TABLE `nodos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `procesos`
--

DROP TABLE IF EXISTS `procesos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `procesos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `version` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'activo',
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `procesos_codigo_unique` (`codigo`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `procesos`
--

LOCK TABLES `procesos` WRITE;
/*!40000 ALTER TABLE `procesos` DISABLE KEYS */;
INSERT INTO `procesos` VALUES (1,'04-08-00-38-30-00-00-00-10-016','Apertura, pago y liquidación de cartas de crédito del INDE','2.1','activo','Establecer los lineamientos para la apertura, pago y liquidacón de las cartas de crédito que resulten necesarias en las operaciones comerciales que lleve a cabo el Instituto Nacional de Electrificacón -INDE-.','2026-02-19 03:39:47','2026-03-01 03:24:05');
/*!40000 ALTER TABLE `procesos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `productos_servicios`
--

DROP TABLE IF EXISTS `productos_servicios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `productos_servicios` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tipo` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `productos_servicios_tipo_activo_index` (`tipo`,`activo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `productos_servicios`
--

LOCK TABLES `productos_servicios` WRITE;
/*!40000 ALTER TABLE `productos_servicios` DISABLE KEYS */;
/*!40000 ALTER TABLE `productos_servicios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `project_statuses`
--

DROP TABLE IF EXISTS `project_statuses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `project_statuses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `project_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `color` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `position` int unsigned NOT NULL DEFAULT '0',
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `project_statuses_project_id_slug_unique` (`project_id`,`slug`),
  CONSTRAINT `project_statuses_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_statuses`
--

LOCK TABLES `project_statuses` WRITE;
/*!40000 ALTER TABLE `project_statuses` DISABLE KEYS */;
INSERT INTO `project_statuses` VALUES (1,1,'To do','todo','#6B7280',1,1,'2026-02-15 12:47:26','2026-02-15 12:47:26'),(2,1,'Doing','doing','#2563EB',2,1,'2026-02-15 12:47:26','2026-02-15 12:47:26'),(3,1,'Done','done','#16A34A',3,1,'2026-02-15 12:47:26','2026-02-15 12:47:26'),(4,1,'SUPERVISION','supervision','#F97316',4,0,'2026-02-15 14:34:36','2026-02-15 14:34:36'),(5,2,'To do','todo','#6B7280',1,1,'2026-02-16 03:17:43','2026-02-16 03:17:43'),(6,2,'Doing','doing','#2563EB',2,1,'2026-02-16 03:17:43','2026-02-16 03:17:43'),(7,2,'Done','done','#16A34A',3,1,'2026-02-16 03:17:43','2026-02-16 03:17:43'),(8,3,'To do','todo','#6B7280',1,1,'2026-02-16 03:58:45','2026-02-16 03:58:45'),(9,3,'Doing','doing','#2563EB',2,1,'2026-02-16 03:58:45','2026-02-16 03:58:45'),(10,3,'Done','done','#16A34A',3,1,'2026-02-16 03:58:45','2026-02-16 03:58:45'),(11,4,'To do','todo','#6B7280',1,1,'2026-02-16 05:25:17','2026-02-16 05:25:17'),(12,4,'Doing','doing','#2563EB',2,1,'2026-02-16 05:25:17','2026-02-16 05:25:17'),(13,4,'Done','done','#16A34A',3,1,'2026-02-16 05:25:17','2026-02-16 05:25:17'),(14,5,'To do','todo','#6B7280',1,1,'2026-02-16 20:26:37','2026-02-16 20:26:37'),(15,5,'Doing','doing','#2563EB',2,1,'2026-02-16 20:26:38','2026-02-16 20:26:38'),(16,5,'Done','done','#16A34A',3,1,'2026-02-16 20:26:38','2026-02-16 20:26:38'),(17,6,'To do','todo','#6B7280',1,1,'2026-02-16 20:27:33','2026-02-16 20:27:33'),(18,6,'Doing','doing','#2563EB',2,1,'2026-02-16 20:27:33','2026-02-16 20:27:33'),(19,6,'Done','done','#16A34A',3,1,'2026-02-16 20:27:33','2026-02-16 20:27:33'),(29,10,'To do','todo','#6B7280',1,1,'2026-02-26 02:10:00','2026-02-26 02:10:00'),(30,10,'Doing','doing','#2563EB',2,1,'2026-02-26 02:10:00','2026-02-26 02:10:00'),(31,10,'Done','done','#16A34A',3,1,'2026-02-26 02:10:00','2026-02-26 02:10:00');
/*!40000 ALTER TABLE `project_statuses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `projects`
--

DROP TABLE IF EXISTS `projects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `projects` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `workspace_id` bigint unsigned NOT NULL,
  `proceso_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(170) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `archived` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `projects_workspace_id_slug_unique` (`workspace_id`,`slug`),
  KEY `projects_workspace_id_created_at_index` (`workspace_id`,`created_at`),
  KEY `projects_proceso_id_foreign` (`proceso_id`),
  CONSTRAINT `projects_proceso_id_foreign` FOREIGN KEY (`proceso_id`) REFERENCES `procesos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `projects_workspace_id_foreign` FOREIGN KEY (`workspace_id`) REFERENCES `workspaces` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `projects`
--

LOCK TABLES `projects` WRITE;
/*!40000 ALTER TABLE `projects` DISABLE KEYS */;
INSERT INTO `projects` VALUES (1,1,NULL,'Proyecto Demo','proyecto-demo','demo',0,'2026-02-15 12:47:26','2026-02-15 12:47:26'),(2,3,NULL,'Proyecto #1 WorkSpaces 003','proyecto-1-workspaces-003','ver',0,'2026-02-16 03:17:43','2026-02-16 03:17:43'),(3,3,NULL,'proyecto #0101','proyecto-0101','proyecto #0101',0,'2026-02-16 03:58:45','2026-02-16 03:58:45'),(4,5,NULL,'Proyecto X 5','proyecto-x-5','proyecto',0,'2026-02-16 05:25:17','2026-02-16 05:25:17'),(5,6,NULL,'proyecto MM','proyecto-mm',NULL,0,'2026-02-16 20:26:37','2026-02-16 20:26:37'),(6,5,NULL,'Proyecto MM','proyecto-mm','proyecto mm',0,'2026-02-16 20:27:33','2026-02-16 20:27:33'),(10,1,1,'Proyecto / Proceso #1','nuevo-proyecto','.....',0,'2026-02-26 02:10:00','2026-02-26 02:10:00');
/*!40000 ALTER TABLE `projects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_user`
--

DROP TABLE IF EXISTS `role_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_user` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `role_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `role_user_role_id_user_id_unique` (`role_id`,`user_id`),
  KEY `role_user_user_id_foreign` (`user_id`),
  CONSTRAINT `role_user_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_user`
--

LOCK TABLES `role_user` WRITE;
/*!40000 ALTER TABLE `role_user` DISABLE KEYS */;
INSERT INTO `role_user` VALUES (1,2,2,'2026-02-24 10:04:07','2026-02-24 10:04:07'),(3,8,2,'2026-02-24 10:04:07','2026-02-24 10:04:07'),(4,6,2,'2026-02-24 10:04:07','2026-02-24 10:04:07'),(5,5,2,'2026-02-24 10:04:07','2026-02-24 10:04:07'),(6,4,2,'2026-02-24 10:04:07','2026-02-24 10:04:07'),(7,1,2,'2026-02-24 10:04:07','2026-02-24 10:04:07'),(8,3,2,'2026-02-24 10:04:07','2026-02-24 10:04:07'),(9,9,2,'2026-02-24 10:04:07','2026-02-24 10:04:07'),(10,4,3,'2026-02-24 10:04:41','2026-02-24 10:04:41'),(11,7,1,'2026-02-24 10:04:46','2026-02-24 10:04:46');
/*!40000 ALTER TABLE `role_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_nombre_unique` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'SOLICITANTE','2026-02-21 09:43:22','2026-02-21 09:43:22'),(2,'ADMINISTRADOR_CONTRATO','2026-02-21 09:43:22','2026-02-21 09:43:22'),(3,'SUPERVISOR_CONTRATO','2026-02-21 09:43:22','2026-02-21 09:43:22'),(4,'JEFE_DAF','2026-02-21 09:43:22','2026-02-21 09:43:22'),(5,'ENCARGADO_FINANCIAMIENTO','2026-02-21 09:43:22','2026-02-21 09:43:22'),(6,'DIVISION_FINANCIERA','2026-02-21 09:43:22','2026-02-21 09:43:22'),(7,'CONTABILIDAD','2026-02-21 09:43:22','2026-02-21 09:43:22'),(8,'CONTROL_FINANCIERO','2026-02-21 09:43:22','2026-02-21 09:43:22'),(9,'TESORERIA','2026-02-21 09:43:22','2026-02-21 09:43:22');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('fjHJXrLb6lYUFzdIMP0fBNCxGtOJ5fnZ56wAvKVH',2,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','YTo1OntzOjY6Il90b2tlbiI7czo0MDoiUG1jZXZkUDJGOWFOU0NKdzFsV05temYxMlZwamNKQUxrWmVFSlZWSyI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjU4OiJodHRwOi8vbG9jYWxob3N0OjgwMDAvZGFzaGJvYXJkP3Byb2plY3RfaWQ9MTAmdmlldz10YWJsZXJvIjtzOjU6InJvdXRlIjtzOjk6ImRhc2hib2FyZCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjI7fQ==',1772329509);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `task_activities`
--

DROP TABLE IF EXISTS `task_activities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `task_activities` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `task_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `event` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `meta` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `task_activities_task_id_foreign` (`task_id`),
  KEY `task_activities_user_id_foreign` (`user_id`),
  CONSTRAINT `task_activities_task_id_foreign` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE,
  CONSTRAINT `task_activities_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `task_activities`
--

LOCK TABLES `task_activities` WRITE;
/*!40000 ALTER TABLE `task_activities` DISABLE KEYS */;
INSERT INTO `task_activities` VALUES (1,1,2,'created','{\"position\": 1, \"status_id\": \"1\"}','2026-02-15 13:55:11','2026-02-15 13:55:11'),(2,1,2,'moved','{\"to_status_id\": 3, \"ordered_count\": 1, \"from_status_id\": 1}','2026-02-15 14:09:28','2026-02-15 14:09:28'),(3,1,2,'moved','{\"to_status_id\": 2, \"ordered_count\": 1, \"from_status_id\": 3}','2026-02-15 14:09:34','2026-02-15 14:09:34'),(4,1,2,'moved','{\"to_status_id\": 3, \"ordered_count\": 1, \"from_status_id\": 2}','2026-02-15 14:10:05','2026-02-15 14:10:05'),(5,1,2,'moved','{\"to_status_id\": 2, \"ordered_count\": 1, \"from_status_id\": 3}','2026-02-15 14:10:47','2026-02-15 14:10:47'),(6,1,2,'moved','{\"to_status_id\": 3, \"ordered_count\": 1, \"from_status_id\": 2}','2026-02-15 14:11:22','2026-02-15 14:11:22'),(7,2,2,'created','{\"position\": 1, \"status_id\": \"1\"}','2026-02-15 14:13:30','2026-02-15 14:13:30'),(8,2,2,'moved','{\"to_status_id\": 3, \"ordered_count\": 2, \"from_status_id\": 1}','2026-02-15 14:13:36','2026-02-15 14:13:36'),(9,2,2,'moved','{\"to_status_id\": 3, \"ordered_count\": 2, \"from_status_id\": 3}','2026-02-15 14:13:43','2026-02-15 14:13:43'),(10,1,2,'moved','{\"to_status_id\": 2, \"ordered_count\": 1, \"from_status_id\": 3}','2026-02-15 14:23:59','2026-02-15 14:23:59'),(11,2,2,'moved','{\"to_status_id\": 4, \"ordered_count\": 1, \"from_status_id\": 3}','2026-02-15 14:34:43','2026-02-15 14:34:43'),(12,1,2,'moved','{\"to_status_id\": 3, \"ordered_count\": 1, \"from_status_id\": 2}','2026-02-15 15:08:48','2026-02-15 15:08:48'),(13,1,2,'moved','{\"to_status_id\": 3, \"ordered_count\": 1, \"from_status_id\": 3}','2026-02-15 15:08:52','2026-02-15 15:08:52'),(14,1,2,'moved','{\"to_status_id\": 4, \"ordered_count\": 2, \"from_status_id\": 3}','2026-02-15 15:08:55','2026-02-15 15:08:55');
/*!40000 ALTER TABLE `task_activities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `task_comments`
--

DROP TABLE IF EXISTS `task_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `task_comments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `task_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `body` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `task_comments_task_id_foreign` (`task_id`),
  KEY `task_comments_user_id_foreign` (`user_id`),
  CONSTRAINT `task_comments_task_id_foreign` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE,
  CONSTRAINT `task_comments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `task_comments`
--

LOCK TABLES `task_comments` WRITE;
/*!40000 ALTER TABLE `task_comments` DISABLE KEYS */;
/*!40000 ALTER TABLE `task_comments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `task_evidences`
--

DROP TABLE IF EXISTS `task_evidences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `task_evidences` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `task_id` bigint unsigned NOT NULL,
  `nodo_item_id` bigint unsigned NOT NULL,
  `estado` enum('PENDIENTE','SUBIDO','EN_REVISION','APROBADO','RECHAZADO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PENDIENTE',
  `disk` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'public',
  `path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `original_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `size_bytes` bigint unsigned DEFAULT NULL,
  `uploaded_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `task_evidences_task_id_nodo_item_id_unique` (`task_id`,`nodo_item_id`),
  KEY `task_evidences_nodo_item_id_foreign` (`nodo_item_id`),
  KEY `task_evidences_uploaded_by_foreign` (`uploaded_by`),
  CONSTRAINT `task_evidences_nodo_item_id_foreign` FOREIGN KEY (`nodo_item_id`) REFERENCES `nodo_items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `task_evidences_task_id_foreign` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE,
  CONSTRAINT `task_evidences_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `task_evidences`
--

LOCK TABLES `task_evidences` WRITE;
/*!40000 ALTER TABLE `task_evidences` DISABLE KEYS */;
INSERT INTO `task_evidences` VALUES (1,24,2,'SUBIDO','public','task_evidences/24/dq6HnBKxpcVsUTf5hZqMLr5yW10iQ6Vdl1boTjpZ.pdf','check.pdf',268294,2,'2026-03-01 07:43:14','2026-03-01 07:43:14'),(2,24,8,'SUBIDO','public','task_evidences/24/o15nOFiRfd4RRLM2oXGAz8Fa4J7fzZbum0q2oMWa.pdf','acta.pdf',402481,2,'2026-03-01 07:45:08','2026-03-01 07:45:08');
/*!40000 ALTER TABLE `task_evidences` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `task_file_review_comments`
--

DROP TABLE IF EXISTS `task_file_review_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `task_file_review_comments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `task_file_review_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'COMMENT',
  `payload` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `task_file_review_comments_task_file_review_id_foreign` (`task_file_review_id`),
  KEY `task_file_review_comments_user_id_foreign` (`user_id`),
  CONSTRAINT `task_file_review_comments_task_file_review_id_foreign` FOREIGN KEY (`task_file_review_id`) REFERENCES `task_file_reviews` (`id`) ON DELETE CASCADE,
  CONSTRAINT `task_file_review_comments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `task_file_review_comments`
--

LOCK TABLES `task_file_review_comments` WRITE;
/*!40000 ALTER TABLE `task_file_review_comments` DISABLE KEYS */;
/*!40000 ALTER TABLE `task_file_review_comments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `task_file_reviews`
--

DROP TABLE IF EXISTS `task_file_reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `task_file_reviews` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `task_file_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'EN_REVISION',
  `summary` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `task_file_reviews_task_file_id_foreign` (`task_file_id`),
  KEY `task_file_reviews_user_id_foreign` (`user_id`),
  CONSTRAINT `task_file_reviews_task_file_id_foreign` FOREIGN KEY (`task_file_id`) REFERENCES `task_files` (`id`) ON DELETE CASCADE,
  CONSTRAINT `task_file_reviews_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `task_file_reviews`
--

LOCK TABLES `task_file_reviews` WRITE;
/*!40000 ALTER TABLE `task_file_reviews` DISABLE KEYS */;
/*!40000 ALTER TABLE `task_file_reviews` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `task_files`
--

DROP TABLE IF EXISTS `task_files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `task_files` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `task_id` bigint unsigned NOT NULL,
  `item_id` bigint unsigned DEFAULT NULL,
  `user_id` bigint unsigned NOT NULL,
  `original_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `size_bytes` bigint unsigned DEFAULT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'SUBIDO',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `task_files_user_id_foreign` (`user_id`),
  KEY `task_files_item_id_foreign` (`item_id`),
  KEY `task_files_task_id_item_id_status_index` (`task_id`,`item_id`,`status`),
  CONSTRAINT `task_files_item_id_foreign` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE SET NULL,
  CONSTRAINT `task_files_task_id_foreign` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE,
  CONSTRAINT `task_files_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `task_files`
--

LOCK TABLES `task_files` WRITE;
/*!40000 ALTER TABLE `task_files` DISABLE KEYS */;
INSERT INTO `task_files` VALUES (1,1,NULL,2,'1533143105_4487_manual_inde.pdf','tasks/1/70e5f64f-2af2-46a2-b3f9-3140423cc452.pdf','application/pdf',9841450,'SUBIDO','2026-02-16 19:59:56','2026-02-16 19:59:56'),(2,2,NULL,2,'logoindeoficial2022.png','tasks/2/2a506df1-407a-49e6-b459-20872c2d5225.png','image/png',54391,'SUBIDO','2026-02-16 20:02:24','2026-02-16 20:02:24'),(3,1,NULL,2,'logoindeoficial2022.png','tasks/1/8b20a56f-568b-4eb1-b377-f4739ceb980d.png','image/png',54391,'SUBIDO','2026-02-16 20:25:23','2026-02-16 20:25:23'),(4,1,NULL,2,'J98xdpwKAcnLtiqQRjQZL6.png','tasks/1/abe4cd15-02c2-4a08-b242-9eecf4ef8e12.png','image/png',258586,'SUBIDO','2026-02-16 20:29:23','2026-02-16 20:29:23');
/*!40000 ALTER TABLE `task_files` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tasks`
--

DROP TABLE IF EXISTS `tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tasks` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `parent_task_id` bigint unsigned DEFAULT NULL,
  `project_id` bigint unsigned NOT NULL,
  `expediente_id` bigint unsigned DEFAULT NULL,
  `nodo_id` bigint unsigned DEFAULT NULL,
  `from_nodo_id` bigint unsigned DEFAULT NULL,
  `from_nodo_relacion_id` bigint unsigned DEFAULT NULL,
  `status_id` bigint unsigned DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `priority` tinyint unsigned NOT NULL DEFAULT '3',
  `start_at` date DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `assignee_id` bigint unsigned DEFAULT NULL,
  `position` int unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `project_status_id` bigint unsigned DEFAULT NULL,
  `assigned_to` bigint unsigned DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `due_at` datetime DEFAULT NULL,
  `started_at` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `archived_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tasks_status_id_foreign` (`status_id`),
  KEY `tasks_assignee_id_foreign` (`assignee_id`),
  KEY `tasks_project_id_status_id_index` (`project_id`,`status_id`),
  KEY `tasks_project_status_id_foreign` (`project_status_id`),
  KEY `tasks_created_by_foreign` (`created_by`),
  KEY `idx_tasks_project_status_pos` (`project_id`,`project_status_id`,`position`),
  KEY `idx_tasks_assigned_status` (`assigned_to`,`project_status_id`),
  KEY `idx_tasks_due_at` (`due_at`),
  KEY `tasks_start_at_index` (`start_at`),
  KEY `tasks_nodo_id_foreign` (`nodo_id`),
  KEY `tasks_exp_nodo_idx` (`expediente_id`,`nodo_id`),
  KEY `tasks_parent_task_id_index` (`parent_task_id`),
  KEY `tasks_from_nodo_id_index` (`from_nodo_id`),
  KEY `tasks_from_nodo_relacion_id_index` (`from_nodo_relacion_id`),
  CONSTRAINT `tasks_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tasks_assignee_id_foreign` FOREIGN KEY (`assignee_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tasks_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tasks_expediente_id_foreign` FOREIGN KEY (`expediente_id`) REFERENCES `expedientes` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tasks_from_nodo_id_foreign` FOREIGN KEY (`from_nodo_id`) REFERENCES `nodos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tasks_from_nodo_relacion_id_foreign` FOREIGN KEY (`from_nodo_relacion_id`) REFERENCES `nodo_relaciones` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tasks_nodo_id_foreign` FOREIGN KEY (`nodo_id`) REFERENCES `nodos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tasks_parent_task_id_foreign` FOREIGN KEY (`parent_task_id`) REFERENCES `tasks` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tasks_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tasks_project_status_id_foreign` FOREIGN KEY (`project_status_id`) REFERENCES `project_statuses` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tasks_status_id_foreign` FOREIGN KEY (`status_id`) REFERENCES `project_statuses` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tasks`
--

LOCK TABLES `tasks` WRITE;
/*!40000 ALTER TABLE `tasks` DISABLE KEYS */;
INSERT INTO `tasks` VALUES (1,NULL,1,NULL,NULL,NULL,NULL,1,'Tarea #1','Tarea',2,'2026-02-10',NULL,NULL,1,'2026-02-15 13:55:11','2026-02-28 01:07:27',4,1,2,'2026-02-17 00:00:00','2026-02-16 01:54:00',NULL,NULL),(2,NULL,1,NULL,NULL,NULL,NULL,1,'Tarea #2','tarea #2',2,'2026-02-25',NULL,NULL,3,'2026-02-15 14:13:30','2026-02-28 01:07:27',2,2,2,'2026-03-12 00:00:00','2026-02-23 02:13:00',NULL,NULL),(3,NULL,1,NULL,NULL,NULL,NULL,1,'Tarea #3','tarea #3',1,'2026-02-16',NULL,NULL,5,'2026-02-16 01:51:42','2026-02-28 01:07:27',NULL,NULL,2,'2026-02-17 00:00:00',NULL,NULL,NULL),(4,NULL,1,NULL,NULL,NULL,NULL,1,'Tarea #4','tarea #4',2,'2026-02-17',NULL,NULL,2,'2026-02-16 01:52:19','2026-02-28 01:07:27',NULL,NULL,2,'2026-02-26 00:00:00',NULL,NULL,NULL),(5,NULL,4,NULL,NULL,NULL,NULL,12,'Tarea #1','tarea 1',1,NULL,NULL,NULL,1,'2026-02-16 05:54:20','2026-02-16 05:54:20',NULL,NULL,2,'2026-02-24 00:00:00','2026-02-09 18:56:11',NULL,NULL),(6,NULL,1,NULL,NULL,NULL,NULL,1,'Tarea #5','tarea 5',1,'2026-02-17',NULL,NULL,6,'2026-02-16 07:44:31','2026-02-28 01:07:27',NULL,NULL,2,'2026-02-24 00:00:00',NULL,NULL,NULL),(7,NULL,6,NULL,NULL,NULL,NULL,18,'Tarea #1 MM','ded',2,'2026-02-17',NULL,NULL,1,'2026-02-16 20:28:06','2026-02-16 20:28:10',NULL,NULL,2,'2026-02-20 00:00:00',NULL,NULL,NULL),(8,NULL,1,NULL,NULL,NULL,NULL,1,'Tarea #6','.....',1,'2026-02-27',NULL,NULL,7,'2026-02-27 18:50:27','2026-02-28 01:07:27',NULL,NULL,2,'2026-02-27 00:00:00',NULL,NULL,NULL),(9,NULL,1,NULL,NULL,NULL,NULL,4,'Tarea #7','...',1,'2026-03-01',NULL,NULL,2,'2026-02-27 19:38:12','2026-02-28 04:25:57',NULL,NULL,2,'2026-03-02 00:00:00',NULL,NULL,NULL),(12,NULL,1,NULL,NULL,NULL,NULL,4,'Tarea #8','..',1,'2026-03-12',NULL,NULL,1,'2026-02-28 02:48:39','2026-02-28 04:25:57',NULL,NULL,2,'2026-03-14 00:00:00',NULL,NULL,NULL),(23,NULL,10,NULL,4,NULL,NULL,31,'Inicio','solicitado por el proveedor. uno',1,'2026-03-01',NULL,NULL,1,'2026-03-01 03:58:28','2026-03-01 03:58:41',31,NULL,2,'2026-03-02 00:00:00',NULL,'2026-02-28 21:58:41',NULL),(24,23,10,NULL,1,4,NULL,29,'1.- Iniciar trámite de apertura de carta de crédito','Determina el tipo de carta de crédito, crédito a aperturar, si es carta de crédito de importación continúa en Actividad 1.1; si es carta de crédito local continúa en Actividad 2.',3,'2026-03-03',NULL,NULL,0,'2026-03-01 03:58:41','2026-03-01 05:18:13',29,NULL,2,'2026-03-04 00:00:00',NULL,NULL,NULL),(25,NULL,10,NULL,4,NULL,NULL,31,'Inicio','solicitado por el proveedor. Uno Dos',1,NULL,NULL,NULL,1,'2026-03-01 04:25:07','2026-03-01 04:26:02',31,NULL,2,NULL,NULL,'2026-02-28 22:26:02',NULL),(26,25,10,NULL,1,4,NULL,29,'1.- Iniciar trámite de apertura de carta de crédito','Determina el tipo de carta de crédito, crédito a aperturar, si es carta de crédito de importación continúa en Actividad 1.1; si es carta de crédito local continúa en Actividad 2.',3,NULL,NULL,NULL,0,'2026-03-01 04:26:02','2026-03-01 04:26:02',29,NULL,2,NULL,NULL,NULL,NULL),(27,NULL,1,NULL,NULL,NULL,NULL,3,'Tarea #9',NULL,1,NULL,NULL,NULL,1,'2026-03-01 04:44:20','2026-03-01 04:44:26',NULL,NULL,2,NULL,NULL,NULL,NULL),(28,NULL,10,NULL,4,NULL,NULL,31,'Inicio','solicitado por el proveedor.',1,NULL,NULL,NULL,1,'2026-03-01 04:50:37','2026-03-01 04:50:44',31,NULL,2,NULL,NULL,'2026-02-28 22:50:44',NULL),(29,28,10,NULL,1,4,NULL,29,'1.- Iniciar trámite de apertura de carta de crédito','Determina el tipo de carta de crédito, crédito a aperturar, si es carta de crédito de importación continúa en Actividad 1.1; si es carta de crédito local continúa en Actividad 2.',3,NULL,NULL,NULL,0,'2026-03-01 04:50:44','2026-03-01 04:50:44',29,NULL,2,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `tasks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tipos_item`
--

DROP TABLE IF EXISTS `tipos_item`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tipos_item` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `categoria` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tipos_item_categoria_nombre_index` (`categoria`,`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tipos_item`
--

LOCK TABLES `tipos_item` WRITE;
/*!40000 ALTER TABLE `tipos_item` DISABLE KEYS */;
/*!40000 ALTER TABLE `tipos_item` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_workspace`
--

DROP TABLE IF EXISTS `user_workspace`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_workspace` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `workspace_id` bigint unsigned NOT NULL,
  `role` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'member',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_workspace_user_id_workspace_id_unique` (`user_id`,`workspace_id`),
  KEY `user_workspace_workspace_id_foreign` (`workspace_id`),
  CONSTRAINT `user_workspace_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_workspace_workspace_id_foreign` FOREIGN KEY (`workspace_id`) REFERENCES `workspaces` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_workspace`
--

LOCK TABLES `user_workspace` WRITE;
/*!40000 ALTER TABLE `user_workspace` DISABLE KEYS */;
INSERT INTO `user_workspace` VALUES (1,2,1,'owner','2026-02-15 11:54:33','2026-02-15 11:54:33'),(2,1,2,'owner','2026-02-15 13:14:06','2026-02-15 13:14:06'),(3,2,3,'owner','2026-02-16 02:45:57','2026-02-16 02:45:57'),(4,2,4,'owner','2026-02-16 02:48:20','2026-02-16 02:48:20'),(5,2,5,'owner','2026-02-16 05:24:56','2026-02-16 05:24:56'),(6,2,6,'owner','2026-02-16 20:26:16','2026-02-16 20:26:16');
/*!40000 ALTER TABLE `user_workspace` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `phone` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `employee_code` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'member',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Owner Demo','owner@gestorpro.test',1,NULL,NULL,'owner',NULL,'$2y$12$ie.CRE3u4sg/30axrGg1MOrwPM7EdwPs4Sxl.EHD28FXkVuJCJi2i',NULL,'2026-02-15 11:53:08','2026-02-15 11:53:08'),(2,'Admin Demo','admin@gestorpro.test',1,NULL,NULL,'admin',NULL,'$2y$12$iwFtE.RngHHAVG9n/iHA6eEEWggC///NONKSTJqBDVIeC9sBmSUdG','JqYCQ3rZAcM8KGudM8bYGKhaRVfpsGuCWflaB9jovPyDt2lnT38VbaoBGP7Y','2026-02-15 11:53:08','2026-02-15 11:53:08'),(3,'Member Demo','member@gestorpro.test',1,NULL,NULL,'member',NULL,'$2y$12$x4lQ4sQOiuXViRpkIf8nGer.sxDpIkh5wMSdO8lopxzqn2HQl0wrq',NULL,'2026-02-15 11:53:09','2026-02-15 11:53:09');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `variables_control`
--

DROP TABLE IF EXISTS `variables_control`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `variables_control` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `clave` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `valor` text COLLATE utf8mb4_unicode_ci,
  `tipo` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'string',
  `scope` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'GLOBAL',
  `proceso_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `variables_control_clave_unique` (`clave`),
  KEY `variables_control_proceso_id_foreign` (`proceso_id`),
  KEY `variables_control_scope_proceso_id_index` (`scope`,`proceso_id`),
  CONSTRAINT `variables_control_proceso_id_foreign` FOREIGN KEY (`proceso_id`) REFERENCES `procesos` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `variables_control`
--

LOCK TABLES `variables_control` WRITE;
/*!40000 ALTER TABLE `variables_control` DISABLE KEYS */;
/*!40000 ALTER TABLE `variables_control` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `workspace_user`
--

DROP TABLE IF EXISTS `workspace_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `workspace_user` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `workspace_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `role` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'member',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `workspace_user_workspace_id_user_id_unique` (`workspace_id`,`user_id`),
  KEY `workspace_user_user_id_foreign` (`user_id`),
  CONSTRAINT `workspace_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `workspace_user_workspace_id_foreign` FOREIGN KEY (`workspace_id`) REFERENCES `workspaces` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `workspace_user`
--

LOCK TABLES `workspace_user` WRITE;
/*!40000 ALTER TABLE `workspace_user` DISABLE KEYS */;
INSERT INTO `workspace_user` VALUES (1,1,1,'admin','2026-02-15 12:25:35','2026-02-15 12:25:42'),(2,1,2,'owner','2026-02-15 13:16:07','2026-02-15 13:16:07');
/*!40000 ALTER TABLE `workspace_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `workspaces`
--

DROP TABLE IF EXISTS `workspaces`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `workspaces` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner_user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `workspaces_owner_user_id_foreign` (`owner_user_id`),
  CONSTRAINT `workspaces_owner_user_id_foreign` FOREIGN KEY (`owner_user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `workspaces`
--

LOCK TABLES `workspaces` WRITE;
/*!40000 ALTER TABLE `workspaces` DISABLE KEYS */;
INSERT INTO `workspaces` VALUES (1,'001 Administración',2,'2026-02-15 11:54:33','2026-02-15 11:54:33'),(2,'002 Comunicaciones',1,'2026-02-15 13:14:06','2026-02-15 13:14:06'),(3,'003 Redes',2,'2026-02-16 02:45:57','2026-02-16 02:45:57'),(4,'004 Protecciones',2,'2026-02-16 02:48:20','2026-02-16 02:48:20'),(5,'005 Prueba',2,'2026-02-16 05:24:56','2026-02-16 05:24:56'),(6,'005 DEMO',2,'2026-02-16 20:26:16','2026-02-16 20:26:16');
/*!40000 ALTER TABLE `workspaces` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-03-01 12:10:54
