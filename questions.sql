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

-- Copiando estrutura para tabela saas-endomarketing.questions
CREATE TABLE IF NOT EXISTS `questions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `text` text NOT NULL,
  `category` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Copiando dados para a tabela saas-endomarketing.questions: ~40 rows (aproximadamente)
INSERT IGNORE INTO `questions` (`id`, `text`, `category`, `created_at`, `updated_at`) VALUES
	(1, 'A comunicação entre os setores é eficiente?', 'comu_inte', '2025-07-29 16:15:07', '2025-07-29 16:15:07'),
	(2, 'As informações importantes chegam até você com clareza?', 'comu_inte', '2025-07-29 16:15:07', '2025-07-29 16:15:07'),
	(3, 'Os canais de comunicação interna são acessíveis e funcionais?', 'comu_inte', '2025-07-29 16:15:07', '2025-07-29 16:15:07'),
	(4, 'Você sente que há espaço para diálogo na organização?', 'comu_inte', '2025-07-29 16:15:07', '2025-07-29 16:15:07'),
	(5, 'As decisões da liderança são comunicadas de forma transparente?', 'comu_inte', '2025-07-29 16:15:07', '2025-07-29 16:15:07'),
	(6, 'Você se sente reconhecido pelo seu desempenho?', 'reco_valo', '2025-07-29 16:15:07', '2025-07-29 16:15:07'),
	(7, 'Os esforços da equipe são valorizados pela liderança?', 'reco_valo', '2025-07-29 16:15:07', '2025-07-29 16:15:07'),
	(8, 'Existe reconhecimento público por conquistas profissionais?', 'reco_valo', '2025-07-29 16:15:07', '2025-07-29 16:15:07'),
	(9, 'O bom desempenho é recompensado na empresa?', 'reco_valo', '2025-07-29 16:15:07', '2025-07-29 16:15:07'),
	(10, 'A empresa incentiva o desenvolvimento individual dos colaboradores?', 'reco_valo', '2025-07-29 16:15:07', '2025-07-29 16:15:07'),
	(11, 'O ambiente de trabalho é agradável e acolhedor?', 'clim_orga', '2025-07-29 16:15:07', '2025-07-29 16:15:07'),
	(12, 'Há respeito entre os colegas de equipe?', 'clim_orga', '2025-07-29 16:15:07', '2025-07-29 16:15:07'),
	(13, 'Você sente-se confortável para expressar suas opiniões?', 'clim_orga', '2025-07-29 16:15:07', '2025-07-29 16:15:07'),
	(14, 'Existe cooperação entre os membros da equipe?', 'clim_orga', '2025-07-29 16:15:07', '2025-07-29 16:15:07'),
	(15, 'Você sente motivação para realizar seu trabalho diariamente?', 'clim_orga', '2025-07-29 16:15:07', '2025-07-29 16:15:07'),
	(16, 'Os valores da empresa estão presentes no dia a dia?', 'cult_orga', '2025-07-29 16:15:07', '2025-07-29 16:15:07'),
	(17, 'A missão da empresa é clara para todos?', 'cult_orga', '2025-07-29 16:15:07', '2025-07-29 16:15:07'),
	(18, 'A cultura organizacional é praticada por líderes e gestores?', 'cult_orga', '2025-07-29 16:15:07', '2025-07-29 16:15:07'),
	(19, 'Você sente que há coerência entre discurso e prática?', 'cult_orga', '2025-07-29 16:15:07', '2025-07-29 16:15:07'),
	(20, 'A empresa promove uma cultura inclusiva e diversa?', 'cult_orga', '2025-07-29 16:15:07', '2025-07-29 16:15:07'),
	(21, 'A empresa oferece treinamentos com frequência?', 'dese_capa', '2025-07-29 16:15:07', '2025-07-29 16:15:07'),
	(22, 'Você tem oportunidades de crescimento profissional?', 'dese_capa', '2025-07-29 16:15:07', '2025-07-29 16:15:07'),
	(23, 'Existe incentivo à qualificação dos colaboradores?', 'dese_capa', '2025-07-29 16:15:07', '2025-07-29 16:15:07'),
	(24, 'Os treinamentos são relevantes para sua função?', 'dese_capa', '2025-07-29 16:15:07', '2025-07-29 16:15:07'),
	(25, 'A empresa investe no desenvolvimento técnico e comportamental?', 'dese_capa', '2025-07-29 16:15:07', '2025-07-29 16:15:07'),
	(26, 'Os gestores são acessíveis e abertos ao diálogo?', 'lide_gest', '2025-07-29 16:15:07', '2025-07-29 16:15:07'),
	(27, 'A liderança demonstra competência técnica e emocional?', 'lide_gest', '2025-07-29 16:15:07', '2025-07-29 16:15:07'),
	(28, 'Você recebe feedbacks construtivos da sua liderança?', 'lide_gest', '2025-07-29 16:15:07', '2025-07-29 16:15:07'),
	(29, 'A tomada de decisões pela gestão é justa?', 'lide_gest', '2025-07-29 16:15:07', '2025-07-29 16:15:07'),
	(30, 'A liderança inspira confiança e respeito?', 'lide_gest', '2025-07-29 16:15:07', '2025-07-29 16:15:07'),
	(31, 'Você sente que possui equilíbrio entre vida pessoal e profissional?', 'qual_vida_trab', '2025-07-29 16:15:07', '2025-07-29 16:15:07'),
	(32, 'A empresa oferece recursos para o bem-estar dos colaboradores?', 'qual_vida_trab', '2025-07-29 16:15:07', '2025-07-29 16:15:07'),
	(33, 'O ambiente físico de trabalho é adequado?', 'qual_vida_trab', '2025-07-29 16:15:07', '2025-07-29 16:15:07'),
	(34, 'Há flexibilidade nas jornadas ou condições de trabalho?', 'qual_vida_trab', '2025-07-29 16:15:07', '2025-07-29 16:15:07'),
	(35, 'A empresa promove iniciativas voltadas à saúde mental?', 'qual_vida_trab', '2025-07-29 16:15:07', '2025-07-29 16:15:07'),
	(36, 'Você se sente parte do time e da empresa?', 'pert_enga', '2025-07-29 16:15:07', '2025-07-29 16:15:07'),
	(37, 'A empresa valoriza a opinião dos colaboradores?', 'pert_enga', '2025-07-29 16:15:07', '2025-07-29 16:15:07'),
	(38, 'Você tem orgulho de trabalhar nesta organização?', 'pert_enga', '2025-07-29 16:15:07', '2025-07-29 16:15:07'),
	(39, 'Sente-se motivado a contribuir com ideias e soluções?', 'pert_enga', '2025-07-29 16:15:07', '2025-07-29 16:15:07'),
	(40, 'A empresa promove o engajamento de todos os setores?', 'pert_enga', '2025-07-29 16:15:07', '2025-07-29 16:15:07');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
