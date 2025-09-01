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

-- Copiando estrutura para tabela saas_endomarketing.questions
CREATE TABLE IF NOT EXISTS `questions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `text` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` enum('aberta','fechada') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'aberta',
  `diagnostic_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'cultura',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Copiando dados para a tabela saas_endomarketing.questions: ~41 rows (aproximadamente)
INSERT IGNORE INTO `questions` (`id`, `text`, `category`, `type`, `diagnostic_type`, `created_at`, `updated_at`) VALUES
	(1, 'Por que a empresa existe? Qual é o propósito que vai além do lucro?', 'identidade_proposito', 'aberta', 'cultura', '2025-09-01 20:10:19', '2025-09-01 20:10:19'),
	(2, 'Como vocês descreveriam a missão e visão na prática do dia a dia?', 'identidade_proposito', 'aberta', 'cultura', '2025-09-01 20:10:19', '2025-09-01 20:10:19'),
	(3, 'Quais comportamentos são mais valorizados aqui? Quais são inaceitáveis?', 'valores_comportamentos', 'aberta', 'cultura', '2025-09-01 20:10:19', '2025-09-01 20:10:19'),
	(4, 'Se alguém novo perguntasse: “o que é preciso para ter sucesso nesta empresa?”, o que vocês responderiam?', 'valores_comportamentos', 'aberta', 'cultura', '2025-09-01 20:10:19', '2025-09-01 20:10:19'),
	(5, 'Qual o nível de comprometimento das pessoas?', 'valores_comportamentos', 'fechada', 'cultura', '2025-09-01 20:10:19', '2025-09-01 20:10:19'),
	(6, 'Como é trabalhar aqui? Como descreveriam o clima em poucas palavras?', 'ambiente_clima', 'aberta', 'cultura', '2025-09-01 20:10:19', '2025-09-01 20:10:19'),
	(7, 'O que motiva as pessoas a ficarem? O que leva alguém a sair?', 'ambiente_clima', 'aberta', 'cultura', '2025-09-01 20:10:19', '2025-09-01 20:10:19'),
	(8, 'Conte uma situação em que você sentiu orgulho de trabalhar aqui.', 'ambiente_clima', 'aberta', 'cultura', '2025-09-01 20:10:19', '2025-09-01 20:10:19'),
	(9, 'O que acontece quando alguém erra nesta empresa?', 'ambiente_clima', 'fechada', 'cultura', '2025-09-01 20:10:19', '2025-09-01 20:10:19'),
	(10, 'Quais canais de comunicação são úteis?', 'comunicacao_lideranca', 'aberta', 'cultura', '2025-09-01 20:10:19', '2025-09-01 20:10:19'),
	(11, 'Como acontece a comunicação com a liderança em via de mão dupla?', 'comunicacao_lideranca', 'aberta', 'cultura', '2025-09-01 20:10:19', '2025-09-01 20:10:19'),
	(12, 'Como os líderes se comunicam? Existe espaço para diálogo aberto?', 'comunicacao_lideranca', 'aberta', 'cultura', '2025-09-01 20:10:19', '2025-09-01 20:10:19'),
	(13, 'Como o feedback é dado e recebido?', 'comunicacao_lideranca', 'aberta', 'cultura', '2025-09-01 20:10:19', '2025-09-01 20:10:19'),
	(14, 'Como são tomadas as decisões importantes?', 'processos_praticas', 'aberta', 'cultura', '2025-09-01 20:10:19', '2025-09-01 20:10:19'),
	(15, 'Há coerência entre o que a liderança diz e o que faz?', 'processos_praticas', 'fechada', 'cultura', '2025-09-01 20:10:19', '2025-09-01 20:10:19'),
	(16, 'Como as conquistas são celebradas? O que se comemora (resultados, aniversários, tempo de casa, etc.)?', 'reconhecimento_celebracao', 'aberta', 'cultura', '2025-09-01 20:10:19', '2025-09-01 20:10:19'),
	(17, 'Como é reconhecido um trabalho bem feito?', 'reconhecimento_celebracao', 'aberta', 'cultura', '2025-09-01 20:10:19', '2025-09-01 20:10:19'),
	(18, 'Quão confortável as pessoas se sentem em serem elas mesmas?', 'diversidade_pertencimento', 'fechada', 'cultura', '2025-09-01 20:10:19', '2025-09-01 20:10:19'),
	(19, 'Há práticas formais ou informais para promover diversidade?', 'diversidade_pertencimento', 'aberta', 'cultura', '2025-09-01 20:10:19', '2025-09-01 20:10:19'),
	(20, 'Que tipo de cultura vocês desejam ter daqui a 5 anos?', 'aspiracoes_futuro', 'aberta', 'cultura', '2025-09-01 20:10:19', '2025-09-01 20:10:19'),
	(21, 'O que precisaria mudar para chegar lá?', 'aspiracoes_futuro', 'aberta', 'cultura', '2025-09-01 20:10:19', '2025-09-01 20:10:19'),
	(22, 'Como melhorar o engajamento e motivação da equipe?', 'aspiracoes_futuro', 'aberta', 'cultura', '2025-09-01 20:10:19', '2025-09-01 20:10:19'),
	(23, 'Quais oportunidades de desenvolvimento e crescimento são percebidas?', 'aspiracoes_futuro', 'aberta', 'cultura', '2025-09-01 20:10:19', '2025-09-01 20:10:19'),
	(24, 'Como avaliamos a comunicação durante o processo de contratação?', 'contratar', 'fechada', 'comunicacao', '2025-09-01 20:10:33', '2025-09-01 20:10:33'),
	(25, 'Quais informações são mais importantes compartilhar com novos colaboradores?', 'contratar', 'aberta', 'comunicacao', '2025-09-01 20:10:33', '2025-09-01 20:10:33'),
	(26, 'Como comemoramos conquistas e marcos importantes?', 'celebrar', 'aberta', 'comunicacao', '2025-09-01 20:10:33', '2025-09-01 20:10:33'),
	(27, 'As celebrações impactam a motivação da equipe?', 'celebrar', 'fechada', 'comunicacao', '2025-09-01 20:10:33', '2025-09-01 20:10:33'),
	(28, 'Como a informação é compartilhada entre equipes?', 'compartilhar', 'aberta', 'comunicacao', '2025-09-01 20:10:33', '2025-09-01 20:10:33'),
	(29, 'O compartilhamento de informações é eficiente?', 'compartilhar', 'fechada', 'comunicacao', '2025-09-01 20:10:33', '2025-09-01 20:10:33'),
	(30, 'Como a liderança inspira a equipe através da comunicação?', 'inspirar', 'aberta', 'comunicacao', '2025-09-01 20:10:33', '2025-09-01 20:10:33'),
	(31, 'A comunicação da liderança é motivadora?', 'inspirar', 'fechada', 'comunicacao', '2025-09-01 20:10:33', '2025-09-01 20:10:33'),
	(32, 'As pessoas se sentem à vontade para falar e expressar opiniões?', 'falar', 'fechada', 'comunicacao', '2025-09-01 20:10:33', '2025-09-01 20:10:33'),
	(33, 'Quais situações dificultam que as pessoas falem abertamente?', 'falar', 'aberta', 'comunicacao', '2025-09-01 20:10:33', '2025-09-01 20:10:33'),
	(34, 'A liderança escuta efetivamente as sugestões da equipe?', 'escutar', 'fechada', 'comunicacao', '2025-09-01 20:10:33', '2025-09-01 20:10:33'),
	(35, 'Como os feedbacks são recebidos e interpretados?', 'escutar', 'aberta', 'comunicacao', '2025-09-01 20:10:33', '2025-09-01 20:10:33'),
	(36, 'De que forma a comunicação demonstra cuidado com as pessoas?', 'cuidar', 'aberta', 'comunicacao', '2025-09-01 20:10:33', '2025-09-01 20:10:33'),
	(37, 'As ações de cuidado são percebidas pela equipe?', 'cuidar', 'fechada', 'comunicacao', '2025-09-01 20:10:33', '2025-09-01 20:10:33'),
	(38, 'Como a comunicação contribui para o desenvolvimento das pessoas?', 'desenvolver', 'aberta', 'comunicacao', '2025-09-01 20:10:33', '2025-09-01 20:10:33'),
	(39, 'As oportunidades de crescimento são claras e bem comunicadas?', 'desenvolver', 'fechada', 'comunicacao', '2025-09-01 20:10:33', '2025-09-01 20:10:33'),
	(40, 'Como expressamos gratidão e reconhecimento através da comunicação?', 'agradecer', 'aberta', 'comunicacao', '2025-09-01 20:10:33', '2025-09-01 20:10:33'),
	(41, 'O agradecimento é percebido e valorizado pela equipe?', 'agradecer', 'fechada', 'comunicacao', '2025-09-01 20:10:33', '2025-09-01 20:10:33');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
