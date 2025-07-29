-- --------------------------------------------------------
-- Servidor:                     127.0.0.1
-- Versão do servidor:           10.4.32-MariaDB - mariadb.org binary distribution
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

-- Copiando estrutura para tabela saas-endomarketing.plains
CREATE TABLE IF NOT EXISTS `plains` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `price` decimal(8,2) DEFAULT NULL,
  `characteristics` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`characteristics`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Copiando dados para a tabela saas-endomarketing.plains: ~3 rows (aproximadamente)
INSERT IGNORE INTO `plains` (`id`, `name`, `price`, `characteristics`, `created_at`, `updated_at`) VALUES
	(1, 'Básico', 49.90, '{\r\n    "users_limit": 10,\r\n    "campaigns_enabled": false,\r\n    "diagnostics_per_month": 1,\r\n    "data_export": "none"\r\n  }', '2025-07-28 16:01:18', '2025-07-28 16:01:18'),
	(2, 'Intermediário', 99.90, '{\r\n    "users_limit": 20,\r\n    "campaigns_enabled": true,\r\n    "diagnostics_per_month": 2,\r\n    "data_export": "data"\r\n  }', '2025-07-28 16:01:18', '2025-07-28 16:01:18'),
	(3, 'Avançado', 199.90, '{\r\n    "users_limit": 50,\r\n    "campaigns_enabled": true,\r\n    "diagnostics_per_month": 3,\r\n    "data_export": "comparative_graph_and_data"\r\n  }', '2025-07-28 16:01:18', '2025-07-28 16:01:18');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
