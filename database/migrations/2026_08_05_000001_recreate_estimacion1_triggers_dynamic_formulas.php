<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS before_insert_estimaciones1');
        DB::unprepared(<<<'SQL'
CREATE TRIGGER before_insert_estimaciones1 BEFORE INSERT ON estimaciones1
FOR EACH ROW
BEGIN
    DECLARE formula_mode VARCHAR(20) DEFAULT 'trigger';
    DECLARE diametro_pecho_val DECIMAL(10,5);
    DECLARE altura_total_val DECIMAL(10,5);
    DECLARE factor_carbono DOUBLE DEFAULT 0.5;
    DECLARE d_cm DOUBLE;
    DECLARE biomasa_kg DOUBLE;
    DECLARE densidad_basica DOUBLE;
    DECLARE volumen_maderable DOUBLE;

    SELECT COALESCE(modo_ejecucion, 'trigger')
    INTO formula_mode
    FROM formulas
    WHERE id_formula = NEW.id_formula;

    IF formula_mode <> 'app' AND (NEW.calculo IS NULL OR NEW.calculo = 0) THEN
        SELECT a.diametro_pecho, a.altura_total
        INTO diametro_pecho_val, altura_total_val
        FROM arboles a
        WHERE a.id_arbol = NEW.id_arbol;

        IF diametro_pecho_val IS NULL OR diametro_pecho_val <= 0 THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'El diámetro a la altura del pecho es requerido y debe ser mayor que cero';
        END IF;

        IF altura_total_val IS NULL OR altura_total_val <= 0 THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'La altura total del árbol es requerida y debe ser mayor que cero';
        END IF;

        SET d_cm = diametro_pecho_val * 100;

        IF NEW.id_formula IS NOT NULL THEN
            CASE NEW.id_formula
                WHEN 5 THEN
                    SET biomasa_kg = 0.013 * POW(d_cm, 3.046);
                    SET densidad_basica = 575;

                WHEN 6 THEN
                    SET biomasa_kg = 0.283 * POW(POW(d_cm, 2) * altura_total_val, 0.807);
                    SET densidad_basica = 720;

                WHEN 7 THEN
                    SET biomasa_kg = 0.0402 * POW(d_cm, 2.757);
                    SET densidad_basica = 780;

                WHEN 8 THEN
                    SET biomasa_kg = 0.35179 * POW(d_cm, 2);
                    SET densidad_basica = 570;

                ELSE
                    SET biomasa_kg = 0;
                    SET densidad_basica = 0;
            END CASE;

            SET NEW.biomasa = ROUND(biomasa_kg / 1000, 10);
            SET volumen_maderable = biomasa_kg / densidad_basica;
            SET NEW.calculo = ROUND(volumen_maderable, 10);
            SET NEW.carbono = ROUND(NEW.biomasa * factor_carbono, 10);
        ELSE
            SET NEW.calculo = 0;
            SET NEW.biomasa = 0;
            SET NEW.carbono = 0;
        END IF;
    END IF;

    IF NEW.created_at IS NULL THEN
        SET NEW.created_at = NOW();
    END IF;

    IF NEW.updated_at IS NULL THEN
        SET NEW.updated_at = NOW();
    END IF;
END
SQL);

        DB::unprepared('DROP TRIGGER IF EXISTS before_update_estimaciones1');
        DB::unprepared(<<<'SQL'
CREATE TRIGGER before_update_estimaciones1 BEFORE UPDATE ON estimaciones1
FOR EACH ROW
BEGIN
    DECLARE formula_mode VARCHAR(20) DEFAULT 'trigger';
    DECLARE diametro_pecho_val DECIMAL(10,5);
    DECLARE altura_total_val DECIMAL(10,5);
    DECLARE factor_carbono DOUBLE DEFAULT 0.5;
    DECLARE d_cm DOUBLE;
    DECLARE biomasa_kg DOUBLE;
    DECLARE densidad_basica DOUBLE;
    DECLARE volumen_maderable DOUBLE;

    SELECT COALESCE(modo_ejecucion, 'trigger')
    INTO formula_mode
    FROM formulas
    WHERE id_formula = NEW.id_formula;

    IF formula_mode <> 'app' AND (NEW.id_formula <> OLD.id_formula OR NEW.id_arbol <> OLD.id_arbol OR NEW.calculo IS NULL OR NEW.calculo = 0) THEN
        SELECT a.diametro_pecho, a.altura_total
        INTO diametro_pecho_val, altura_total_val
        FROM arboles a
        WHERE a.id_arbol = NEW.id_arbol;

        IF diametro_pecho_val IS NULL OR diametro_pecho_val <= 0 THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'El diámetro a la altura del pecho es requerido y debe ser mayor que cero';
        END IF;

        IF altura_total_val IS NULL OR altura_total_val <= 0 THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'La altura total del árbol es requerida y debe ser mayor que cero';
        END IF;

        SET d_cm = diametro_pecho_val * 100;

        IF NEW.id_formula IS NOT NULL THEN
            CASE NEW.id_formula
                WHEN 5 THEN
                    SET biomasa_kg = 0.013 * POW(d_cm, 3.046);
                    SET densidad_basica = 575;

                WHEN 6 THEN
                    SET biomasa_kg = 0.283 * POW(POW(d_cm, 2) * altura_total_val, 0.807);
                    SET densidad_basica = 720;

                WHEN 7 THEN
                    SET biomasa_kg = 0.0402 * POW(d_cm, 2.757);
                    SET densidad_basica = 780;

                WHEN 8 THEN
                    SET biomasa_kg = 0.35179 * POW(d_cm, 2);
                    SET densidad_basica = 570;

                ELSE
                    SET biomasa_kg = 0;
                    SET densidad_basica = 0;
            END CASE;

            SET NEW.biomasa = ROUND(biomasa_kg / 1000, 10);
            SET volumen_maderable = biomasa_kg / densidad_basica;
            SET NEW.calculo = ROUND(volumen_maderable, 10);
            SET NEW.carbono = ROUND(NEW.biomasa * factor_carbono, 10);
        ELSE
            SET NEW.calculo = 0;
            SET NEW.biomasa = 0;
            SET NEW.carbono = 0;
        END IF;
    END IF;

    IF NEW.updated_at IS NULL THEN
        SET NEW.updated_at = NOW();
    END IF;
END
SQL);
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS before_update_estimaciones1');
        DB::unprepared('DROP TRIGGER IF EXISTS before_insert_estimaciones1');
    }
};