-- phpMyAdmin SQL Dump
-- version 5.1.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Tempo de geração: 06-Abr-2025 às 21:45
-- Versão do servidor: 5.7.36
-- versão do PHP: 8.1.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `necroterio`
--
CREATE DATABASE IF NOT EXISTS `necroterio` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `necroterio`;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tb_usuarios`
--

DROP TABLE IF EXISTS `tb_usuarios`;
CREATE TABLE `tb_usuarios` (
  `id_usuario` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `login` varchar(50) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `nivel` enum('admin','funcionario') NOT NULL DEFAULT 'funcionario',
  `email` varchar(100) DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `data_cadastro` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `idx_login` (`login`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dados iniciais para tabela `tb_usuarios`
--
INSERT INTO `tb_usuarios` (`nome`, `login`, `senha`, `nivel`, `email`, `ativo`) VALUES
('Administrador', 'admin', '$2y$12$x0/wCjNH21NkzF7oPM9kR.GtnFp6gmoNNTFThoS8PMM1av2..wOcu', 'admin', 'admin@exemplo.com', 1);

-- --------------------------------------------------------

--
-- Estrutura da tabela `tb_camara`
--

DROP TABLE IF EXISTS `tb_camara`;
CREATE TABLE `tb_camara` (
  `id_camara` int(11) NOT NULL AUTO_INCREMENT,
  `status_camara` varchar(100) NOT NULL DEFAULT 'Disponível',
  `capacidade` int(11) NOT NULL DEFAULT 1,
  `temperatura` decimal(5,2) DEFAULT NULL,
  `ultima_manutencao` date DEFAULT NULL,
  PRIMARY KEY (`id_camara`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tb_recepcao`
--

DROP TABLE IF EXISTS `tb_recepcao`;
CREATE TABLE `tb_recepcao` (
  `id_morto` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) DEFAULT NULL,
  `origem` varchar(255) NOT NULL,
  `data_entrada` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` varchar(100) NOT NULL DEFAULT 'Recebido',
  `identificacao` varchar(100) DEFAULT NULL,
  `id_camara` int(11) DEFAULT NULL,
  `observacao` text,
  `sexo` char(1) DEFAULT NULL,
  `idade_aproximada` int(11) DEFAULT NULL,
  `causa_presumida` varchar(255) DEFAULT NULL,
  `responsavel_recepcao` varchar(255) NOT NULL,
  PRIMARY KEY (`id_morto`),
  KEY `idx_nome` (`nome`),
  KEY `idx_data_entrada` (`data_entrada`),
  KEY `idx_status` (`status`),
  KEY `fk_camara` (`id_camara`),
  CONSTRAINT `fk_recepcao_camara` FOREIGN KEY (`id_camara`) REFERENCES `tb_camara` (`id_camara`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tb_saida`
--

DROP TABLE IF EXISTS `tb_saida`;
CREATE TABLE `tb_saida` (
  `id_saida` int(11) NOT NULL AUTO_INCREMENT,
  `id_morto` int(11) NOT NULL,
  `data_saida` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `destino` varchar(255) NOT NULL,
  `responsavel_liberacao` varchar(255) NOT NULL,
  `documento_autorizacao` varchar(100) DEFAULT NULL,
  `observacao` text,
  PRIMARY KEY (`id_saida`),
  KEY `idx_data_saida` (`data_saida`),
  KEY `fk_morto` (`id_morto`),
  CONSTRAINT `fk_saida_morto` FOREIGN KEY (`id_morto`) REFERENCES `tb_recepcao` (`id_morto`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tb_historico`
--

DROP TABLE IF EXISTS `tb_historico`;
CREATE TABLE `tb_historico` (
  `id_historico` int(11) NOT NULL AUTO_INCREMENT,
  `id_morto` int(11) NOT NULL,
  `data_alteracao` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `descricao` text NOT NULL,
  `usuario` varchar(100) NOT NULL,
  PRIMARY KEY (`id_historico`),
  KEY `fk_historico_morto` (`id_morto`),
  CONSTRAINT `fk_historico_morto` FOREIGN KEY (`id_morto`) REFERENCES `tb_recepcao` (`id_morto`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Adicionar dados de demonstração para câmaras
--

INSERT INTO `tb_camara` (`status_camara`, `capacidade`, `temperatura`) VALUES
('Disponível', 1, -5.0),
('Disponível', 1, -5.0),
('Disponível', 2, -4.5),
('Em manutenção', 1, NULL);

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
