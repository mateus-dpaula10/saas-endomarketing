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

-- Copiando estrutura para tabela saas_endomarketing.diagnostics
CREATE TABLE IF NOT EXISTS `diagnostics` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `plain_id` bigint unsigned DEFAULT NULL,
  `type` enum('cultura','comunicacao','comunicacao_campanhas') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'cultura',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `diagnostics_plain_id_foreign` (`plain_id`),
  CONSTRAINT `diagnostics_plain_id_foreign` FOREIGN KEY (`plain_id`) REFERENCES `plains` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Copiando dados para a tabela saas_endomarketing.diagnostics: ~3 rows (aproximadamente)
INSERT IGNORE INTO `diagnostics` (`id`, `title`, `description`, `plain_id`, `type`, `created_at`, `updated_at`) VALUES
	(1, 'Diagnóstico de Cultura', 'Avaliação da cultura organizacional', 1, 'cultura', '2025-09-01 17:59:35', '2025-09-01 17:59:35'),
	(2, 'Diagnóstico de Comunicação Interna', 'Avaliação da comunicação interna', 2, 'comunicacao', '2025-09-01 17:59:35', '2025-09-01 17:59:35'),
	(3, 'Diagnóstico de Comunicação Interna com Campanhas', 'Avaliação da comunicação + campanhas automáticas', 3, 'comunicacao_campanhas', '2025-09-01 17:59:35', '2025-09-01 17:59:35');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
