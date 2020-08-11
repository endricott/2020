-- phpMyAdmin SQL Dump
-- version 5.0.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Erstellungszeit: 11. Aug 2020 um 05:55
-- Server-Version: 10.4.11-MariaDB
-- PHP-Version: 7.4.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `caldb`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `calendarinfo`
--

CREATE TABLE `calendarinfo` (
  `id` int(10) NOT NULL,
  `subject` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `note` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `userid` int(10) NOT NULL,
  `person` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `ittakes` time DEFAULT NULL,
  `place` varchar(255) CHARACTER SET utf8 NOT NULL,
  `description` varchar(255) CHARACTER SET utf8 NOT NULL,
  `longlat` point DEFAULT NULL,
  `startdatetime` datetime DEFAULT NULL,
  `enddatetime` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Daten für Tabelle `calendarinfo`
--

INSERT INTO `calendarinfo` (`id`, `subject`, `note`, `userid`, `person`, `ittakes`, `place`, `description`, `longlat`, `startdatetime`, `enddatetime`) VALUES
(1, 'control management', 'take a book with', 1, 'tomas', '09:11:32', '', '', 0x, '2020-08-12 00:00:00', '0000-00-00 00:00:00'),
(2, 'physics', 'need to call prof.', 2, 'librarian should change some books', '05:52:47', 'bebardowicker 7, hannover 30496', 'directions', 0x, '2020-08-11 05:48:47', '2020-08-11 05:48:47');

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `calendarinfo`
--
ALTER TABLE `calendarinfo`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `calendarinfo`
--
ALTER TABLE `calendarinfo`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
