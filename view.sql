CREATE VIEW vw_resumo_eventos AS
SELECT
    e.id_evento,
    e.nome AS nome_evento,
    e.data_inicio,
    e.data_fim,
    c.nome AS categoria,
    l.campus,
    l.sala,
    COUNT(DISTINCT a.id_atividade) AS qtd_atividades,
    COUNT(DISTINCT i.id_participante) AS qtd_participantes
FROM Evento e
JOIN Categoria c ON e.id_categoria = c.id_categoria
JOIN Local l ON e.id_local = l.id_local
LEFT JOIN Atividade a ON e.id_evento = a.id_evento
LEFT JOIN Inscricao i ON e.id_evento = i.id_evento
GROUP BY
    e.id_evento,
    e.nome,
    e.data_inicio,
    e.data_fim,
    c.nome,
    l.campus,
    l.sala;
