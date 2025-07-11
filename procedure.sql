DELIMITER $$

CREATE PROCEDURE sp_inserir_feedback(
    IN p_id_participante INT,
    IN p_id_evento INT,
    IN p_nota INT,
    IN p_comentario VARCHAR(255)
)
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM Inscricao
        WHERE id_participante = p_id_participante
          AND id_evento = p_id_evento
    ) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Participante não inscrito neste evento.';
    ELSE
        INSERT INTO Feedback (
            nota,
            comentario,
            id_participante,
            id_evento
        ) VALUES (
            p_nota,
            p_comentario,
            p_id_participante,
            p_id_evento
        );
    END IF;
END$$

DELIMITER ;
