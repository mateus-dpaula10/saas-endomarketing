-- --------------------------------------------------------
-- Servidor:                     127.0.0.1
-- Versão do servidor:           8.4.3 - MySQL Community Server - GPL
-- OS do Servidor:               Win64
-- HeidiSQL Versão:              12.11.0.7065
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Copiando estrutura para tabela saas_endomarketing.categories
CREATE TABLE IF NOT EXISTS `categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Copiando dados para a tabela saas_endomarketing.categories: ~9 rows (aproximadamente)
INSERT IGNORE INTO `categories` (`id`, `name`, `created_at`, `updated_at`) VALUES
	(1, 'Contratar', '2025-10-07 18:51:33', '2025-10-07 18:51:33'),
	(2, 'Celebrar', '2025-10-07 18:51:33', '2025-10-07 18:51:33'),
	(3, 'Compartilhar', '2025-10-07 18:51:33', '2025-10-07 18:51:33'),
	(4, 'Inspirar', '2025-10-07 18:51:33', '2025-10-07 18:51:33'),
	(5, 'Falar', '2025-10-07 18:51:33', '2025-10-07 18:51:33'),
	(6, 'Escutar', '2025-10-07 18:51:33', '2025-10-07 18:51:33'),
	(7, 'Cuidar', '2025-10-07 18:51:33', '2025-10-07 18:51:33'),
	(8, 'Desenvolver', '2025-10-07 18:51:33', '2025-10-07 18:51:33'),
	(9, 'Agradecer', '2025-10-07 18:51:33', '2025-10-07 18:51:33');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
