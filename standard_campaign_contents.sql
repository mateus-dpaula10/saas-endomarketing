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

-- Copiando estrutura para tabela saas-endomarketing.standard_campaign_contents
CREATE TABLE IF NOT EXISTS `standard_campaign_contents` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `goal` text DEFAULT NULL,
  `video_url` text DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `actions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`actions`)),
  `resources` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`resources`)),
  `quiz` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`quiz`)),
  `standard_campaign_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `standard_campaign_contents_standard_campaign_id_foreign` (`standard_campaign_id`),
  CONSTRAINT `standard_campaign_contents_standard_campaign_id_foreign` FOREIGN KEY (`standard_campaign_id`) REFERENCES `standard_campaigns` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Copiando dados para a tabela saas-endomarketing.standard_campaign_contents: ~8 rows (aproximadamente)
INSERT IGNORE INTO `standard_campaign_contents` (`id`, `goal`, `video_url`, `image_url`, `actions`, `resources`, `quiz`, `standard_campaign_id`, `created_at`, `updated_at`) VALUES
	(1, 'Incentivar a escuta ativa, clareza nas mensagens e fortalecimento dos canais de comunicação interna.', 'https://www.youtube.com/embed/abc123', 'https://example.com/images/comunicacao_interna.jpg', '["Promover reuniões semanais", "Criar newsletter interna", "Utilizar mural de avisos"]', '["https://docs.example.com/guia-comunicacao", "https://blog.example.com/comunicacao-efetiva"]', '["Como posso melhorar minha comunicação?", "Quais canais internos uso mais?"]', 1, '2025-07-14 18:26:45', '2025-07-14 18:26:45'),
	(2, 'Fomentar o reconhecimento dos esforços dos colaboradores para aumentar motivação e engajamento.', 'https://www.youtube.com/embed/def456', 'https://example.com/images/reconhecimento_valorizacao.jpg', '["Implementar programa de reconhecimento mensal", "Enviar e-mails de agradecimento personalizados"]', '["https://docs.example.com/reconhecimento", "https://blog.example.com/motivacao-no-trabalho"]', '["Quais formas de reconhecimento me motivam?", "Como valorizo colegas de equipe?"]', 2, '2025-07-14 18:26:45', '2025-07-14 18:26:45'),
	(3, 'Melhorar o ambiente de trabalho, promovendo bem-estar e colaboração entre equipes.', 'https://www.youtube.com/embed/ghi789', 'https://example.com/images/clima_organizacional.jpg', '["Realizar pesquisas de clima", "Organizar eventos de integração"]', '["https://docs.example.com/clima-trabalho", "https://blog.example.com/bem-estar-no-trabalho"]', '["Sinto que meu ambiente é colaborativo?", "O que pode melhorar no clima da equipe?"]', 3, '2025-07-14 18:26:45', '2025-07-14 18:26:45'),
	(4, 'Fortalecer os valores e missão da empresa para alinhar comportamentos e decisões.', 'https://www.youtube.com/embed/jkl012', 'https://example.com/images/cultura_organizacional.jpg', '["Workshops sobre valores", "Comunicação clara da missão"]', '["https://docs.example.com/cultura", "https://blog.example.com/cultura-empresa"]', '["Como vivencio a cultura da empresa?", "Quais valores são mais importantes?"]', 4, '2025-07-14 18:26:45', '2025-07-14 18:26:45'),
	(5, 'Estimular o aprendizado contínuo e crescimento profissional dos colaboradores.', 'https://www.youtube.com/embed/mno345', 'https://example.com/images/desenvolvimento_capacitacao.jpg', '["Cursos online", "Mentorias internas"]', '["https://docs.example.com/capacitacao", "https://blog.example.com/desenvolvimento-profissional"]', '["Quais habilidades quero desenvolver?", "Como posso aplicar o aprendizado no trabalho?"]', 5, '2025-07-14 18:26:45', '2025-07-14 18:26:45'),
	(6, 'Aprimorar habilidades de liderança para melhorar gestão de equipes e resultados.', 'https://www.youtube.com/embed/pqr678', 'https://example.com/images/lideranca_gestao.jpg', '["Treinamentos em liderança", "Feedbacks constantes"]', '["https://docs.example.com/lideranca", "https://blog.example.com/gestao-de-equipes"]', '["Como sou como líder?", "Como posso apoiar minha equipe?"]', 6, '2025-07-14 18:26:45', '2025-07-14 18:26:45'),
	(7, 'Promover saúde física e mental para aumentar a satisfação e produtividade.', 'https://www.youtube.com/embed/stu901', 'https://example.com/images/qualidade_vida_trabalho.jpg', '["Programa de ginástica laboral", "Campanhas de saúde mental"]', '["https://docs.example.com/qualidade-vida", "https://blog.example.com/saude-trabalho"]', '["Cuido bem da minha saúde no trabalho?", "O que posso melhorar na minha rotina?"]', 7, '2025-07-14 18:26:45', '2025-07-14 18:26:45'),
	(8, 'Fortalecer o sentimento de pertencimento e engajamento dos colaboradores na empresa.', 'https://www.youtube.com/embed/vwx234', 'https://example.com/images/pertencimento_engajamento.jpg', '["Eventos de integração", "Comunicação transparente"]', '["https://docs.example.com/engajamento", "https://blog.example.com/pertencimento-trabalho"]', '["Me sinto parte da empresa?", "Como posso me engajar mais?"]', 8, '2025-07-14 18:26:45', '2025-07-14 18:26:45');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
