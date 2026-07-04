-- =============================================
-- CATEGORIAS GNÓSTICAS
-- =============================================

-- Categorias de Áudio
INSERT INTO `categorias` (`nome`, `slug`, `descricao`, `icone`, `cor`, `ordem`) VALUES
('Gnose e Esoterismo', 'gnose-esoterismo', 'Conhecimento gnóstico, esoterismo e saberes ocultos antigos', 'bi bi-lightning-charge', '#9b59b6', 1),
('Cristianismo Esotérico', 'cristianismo-esoterico', 'Ensinamentos esotéricos de Jesus e cristianismo primitivo', 'bi bi-cross', '#e74c3c', 2),
('Hermetismo e Teosofia', 'hermetismo-teosofia', 'Filosofia hermética, teosofia e sabedoria antiga', 'bi bi-stars', '#f39c12', 3),
('Consciência e Meditação', 'consciencia-meditacao', 'Expansão da consciência, meditação e mindfulness', 'bi bi-moon-stars', '#3498db', 4),
('Corpo e Regeneração', 'corpo-regeneracao', 'Saúde holística, regeneração e autofagia', 'bi bi-heart-pulse', '#2ecc71', 5),
('Música e Sons', 'musica-sons', 'Música sagrada, frequências curativas e sons vibracionais', 'bi bi-music-note-beamed', '#1abc9c', 6)
ON DUPLICATE KEY UPDATE `slug` = `slug`;

-- Categorias de Vídeo
INSERT INTO `categorias` (`nome`, `slug`, `descricao`, `icone`, `cor`, `ordem`) VALUES
('Frequências de Cura', 'frequencias-cura', 'Frequências solfeggio, sons curativos e vibração', 'bi bi-waveform', '#9b59b6', 7),
('Filosofia e Consciência', 'filosofia-consciencia', 'Filosofia perene, consciência e metafísica', 'bi bi-book', '#3498db', 8),
('História e Cultura', 'historia-cultura', 'História antiga, civilizações e cultura ancestral', 'bi bi-globe', '#f39c12', 9),
('Momentos Pessoais', 'momentos-pessoais', 'Reflexões pessoais e momentos de contemplação', 'bi bi-camera-video', '#e74c3c', 10),
('Animes e Animações', 'animes-animacoes', 'Animes com conteúdo filosófico e espiritual', 'bi bi-film', '#1abc9c', 11)
ON DUPLICATE KEY UPDATE `slug` = `slug`;
