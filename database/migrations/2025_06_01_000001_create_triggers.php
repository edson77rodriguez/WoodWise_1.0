<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // =====================================================================
        // TRIGGER: calcular_todo_estimacion (BEFORE INSERT en estimaciones)
        // Calcula volumen, biomasa y carbono para TROZAS
        // =====================================================================
        DB::unprepared('DROP TRIGGER IF EXISTS calcular_todo_estimacion');
        DB::unprepared("
            CREATE TRIGGER calcular_todo_estimacion BEFORE INSERT ON estimaciones
            FOR EACH ROW
            BEGIN
                DECLARE l DECIMAL(65,30);
                DECLARE c0 DECIMAL(65,30);
                DECLARE c1 DECIMAL(65,30);
                DECLARE cm DECIMAL(65,30);
                DECLARE densidad_val DECIMAL(65,30);
                DECLARE v DECIMAL(65,30);
                DECLARE pi_val DECIMAL(65,30) DEFAULT 3.1415926535897932384626433832795;

                SELECT 
                    CAST(longitud AS DECIMAL(65,30)), 
                    CAST(diametro AS DECIMAL(65,30)), 
                    CAST(diametro_otro_extremo AS DECIMAL(65,30)), 
                    CAST(diametro_medio AS DECIMAL(65,30)), 
                    CAST(densidad AS DECIMAL(65,30))
                INTO l, c0, c1, cm, densidad_val
                FROM trozas
                WHERE id_troza = NEW.id_troza;

                IF l IS NULL OR l <= 0 OR l > 100 THEN 
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'Longitud inválida: debe ser valor positivo <= 100';
                END IF;

                IF densidad_val IS NULL OR densidad_val <= 0 OR densidad_val > 2 THEN 
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'Densidad inválida: debe ser valor positivo <= 2';
                END IF;

                CASE NEW.id_formula
                    WHEN 1 THEN 
                        IF cm IS NULL OR cm <= 0 OR cm > 5 THEN 
                            SIGNAL SQLSTATE '45000' 
                            SET MESSAGE_TEXT = 'Diámetro medio inválido: debe ser 0 < d <= 5 para Huber';
                        END IF;
                        SET v = TRUNCATE((l / (4 * pi_val)) * POW(cm, 2), 30);

                    WHEN 2 THEN 
                        IF c0 IS NULL OR c1 IS NULL OR c0 <= 0 OR c1 <= 0 OR c0 > 5 OR c1 > 5 THEN
                            SIGNAL SQLSTATE '45000' 
                            SET MESSAGE_TEXT = 'Diámetros extremos inválidos: deben ser 0 < d <= 5 para Smalian';
                        END IF;
                        SET v = TRUNCATE((l / (4 * pi_val)) * ((POW(c0, 2) + POW(c1, 2)) / 2), 30);

                    WHEN 3 THEN 
                        IF c0 IS NULL OR c1 IS NULL OR c0 <= 0 OR c1 <= 0 OR c0 > 5 OR c1 > 5 THEN
                            SIGNAL SQLSTATE '45000' 
                            SET MESSAGE_TEXT = 'Diámetros extremos inválidos: deben ser 0 < d <= 5 para Tronco de Cono';
                        END IF;
                        SET v = TRUNCATE((l / (12 * pi_val)) * (POW(c0, 2) + POW(c1, 2) + (c0 * c1)), 30);

                    WHEN 4 THEN 
                        IF c0 IS NULL OR c1 IS NULL OR cm IS NULL OR 
                           c0 <= 0 OR c1 <= 0 OR cm <= 0 OR
                           c0 > 5 OR c1 > 5 OR cm > 5 THEN
                            SIGNAL SQLSTATE '45000' 
                            SET MESSAGE_TEXT = 'Diámetros inválidos: deben ser 0 < d <= 5 para Newton';
                        END IF;
                        SET v = TRUNCATE((l / (24 * pi_val)) * (POW(c0, 2) + POW(c1, 2) + 4 * POW(cm, 2)), 30);

                    ELSE
                        SIGNAL SQLSTATE '45000' 
                        SET MESSAGE_TEXT = 'Fórmula no reconocida. Valores aceptados: 1-Huber, 2-Smalian, 3-Tronco de Cono, 4-Newton';
                END CASE;

                SET NEW.calculo = ROUND(v, 10); 
                SET NEW.biomasa = ROUND(v * densidad_val, 10);
                SET NEW.carbono = ROUND(NEW.biomasa * 0.5, 10);

                IF NEW.calculo <= 0 OR NEW.calculo > 1000 THEN 
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'Error en cálculo: volumen resultante fuera de rango (0-1000)';
                END IF;

                IF NEW.biomasa <= 0 OR NEW.biomasa > 2000 THEN 
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'Error en cálculo: biomasa resultante fuera de rango (0-2000)';
                END IF;
            END
        ");

        // =====================================================================
        // TRIGGER: actualizar_todo_estimacion (BEFORE UPDATE en estimaciones)
        // Recalcula cuando cambia la fórmula o troza
        // =====================================================================
        DB::unprepared('DROP TRIGGER IF EXISTS actualizar_todo_estimacion');
        DB::unprepared("
            CREATE TRIGGER actualizar_todo_estimacion BEFORE UPDATE ON estimaciones
            FOR EACH ROW
            BEGIN
                DECLARE l DOUBLE;
                DECLARE c0 DOUBLE;
                DECLARE c1 DOUBLE;
                DECLARE cm DOUBLE;
                DECLARE densidad_val DOUBLE;
                DECLARE v DOUBLE;

                IF NEW.id_formula <> OLD.id_formula OR NEW.id_troza <> OLD.id_troza OR NEW.calculo IS NULL THEN
                    SELECT longitud, diametro, diametro_otro_extremo, diametro_medio, densidad
                    INTO l, c0, c1, cm, densidad_val
                    FROM trozas
                    WHERE id_troza = NEW.id_troza;

                    IF l IS NULL OR densidad_val IS NULL THEN
                        SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'Longitud y densidad son requeridos';
                    END IF;

                    CASE NEW.id_formula
                        WHEN 1 THEN 
                            IF cm IS NULL THEN
                                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Diámetro medio requerido para Huber';
                            END IF;
                            SET v = (l / (4 * PI())) * (POW(cm, 2));

                        WHEN 2 THEN 
                            IF c0 IS NULL OR c1 IS NULL THEN
                                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Ambos diámetros extremos requeridos para Smalian';
                            END IF;
                            SET v = (PI() * l / 4) * ((POW(c0, 2) + POW(c1, 2)) / 2);

                        WHEN 3 THEN 
                            IF c0 IS NULL OR c1 IS NULL THEN
                                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Ambos diámetros extremos requeridos para Tronco de Cono';
                            END IF;
                            SET v = (l / (12 * PI())) * (POW(c0, 2) + POW(c1, 2) + (c0 * c1));

                        WHEN 4 THEN 
                            IF c0 IS NULL OR c1 IS NULL OR cm IS NULL THEN
                                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Diámetros extremos y medio requeridos para Newton';
                            END IF;
                            SET v = (l / (24 * PI())) * (POW(c0, 2) + POW(c1, 2) + 4 * POW(cm, 2));

                        ELSE
                            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Fórmula no reconocida';
                    END CASE;

                    SET NEW.calculo = v;
                    SET NEW.biomasa = v * densidad_val;
                    SET NEW.carbono = NEW.biomasa * 0.5;
                END IF;
            END
        ");

        // =====================================================================
        // TRIGGER: validar_arbol (BEFORE INSERT en arboles)
        // Valida datos del árbol antes de insertar
        // =====================================================================
        DB::unprepared('DROP TRIGGER IF EXISTS validar_arbol');
        DB::unprepared("
            CREATE TRIGGER validar_arbol BEFORE INSERT ON arboles
            FOR EACH ROW
            BEGIN
                IF NEW.diametro_pecho IS NULL THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'El diámetro a la altura del pecho (DBH) es requerido';
                END IF;

                IF NEW.altura_total IS NULL THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'La altura total del árbol es requerida';
                END IF;

                IF NEW.diametro_pecho <= 0 THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'El DBH debe ser mayor que cero';
                END IF;

                IF NEW.altura_total <= 0 THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'La altura debe ser mayor que cero';
                END IF;

                SET NEW.created_at = NOW();
                SET NEW.updated_at = NOW();
            END
        ");

        // =====================================================================
        // TRIGGER: calcular_estimaciones_arbol (AFTER INSERT en arboles)
        // Crea automáticamente las estimaciones de biomasa/carbono
        // =====================================================================
        DB::unprepared('DROP TRIGGER IF EXISTS calcular_estimaciones_arbol');
        DB::unprepared("
            CREATE TRIGGER calcular_estimaciones_arbol AFTER INSERT ON arboles
            FOR EACH ROW
            BEGIN
                DECLARE volumen DOUBLE;
                DECLARE biomasa DOUBLE DEFAULT NULL;
                DECLARE carbono DOUBLE DEFAULT NULL;
                DECLARE formula_id BIGINT DEFAULT NULL;
                DECLARE especie_nombre VARCHAR(255);
                DECLARE tipo_volumen_id BIGINT;
                DECLARE tipo_biomasa_id BIGINT;
                DECLARE tipo_carbono_id BIGINT;
                DECLARE factor_carbono DOUBLE DEFAULT 0.5;
                DECLARE area_basal DOUBLE;

                -- Calcular área basal y volumen (diámetro en metros)
                SET area_basal = PI() * POW(NEW.diametro_pecho / 2, 2);
                SET volumen = area_basal * NEW.altura_total * 0.5;

                SELECT nom_cientifico INTO especie_nombre 
                FROM especies 
                WHERE id_especie = NEW.id_especie;

                -- Obtener IDs de tipos de estimación
                SELECT id_tipo_e INTO tipo_volumen_id FROM tipo_estimaciones WHERE desc_estimacion = 'Volumen Maderable' LIMIT 1;
                SELECT id_tipo_e INTO tipo_biomasa_id FROM tipo_estimaciones WHERE desc_estimacion = 'Biomasa' LIMIT 1;
                SELECT id_tipo_e INTO tipo_carbono_id FROM tipo_estimaciones WHERE desc_estimacion = 'Carbono' LIMIT 1;

                -- Insertar estimación de Volumen Maderable
                IF tipo_volumen_id IS NOT NULL THEN
                    INSERT INTO estimaciones1 (
                        id_tipo_e, id_formula, calculo, area_basal, biomasa, carbono, id_arbol, created_at, updated_at
                    ) VALUES (
                        tipo_volumen_id, NULL, volumen, area_basal, 0, 0, NEW.id_arbol, NOW(), NOW()
                    );
                END IF;

                -- Calcular biomasa según especie
                IF especie_nombre = 'Quercus crassifolia' THEN
                    SELECT id_formula INTO formula_id FROM formulas 
                    WHERE nom_formula = 'Biomasa Quercus crassifolia' LIMIT 1;
                    IF formula_id IS NOT NULL THEN
                        SET biomasa = 0.283 * POW(POW(NEW.diametro_pecho * 100, 2) * NEW.altura_total, 0.807);
                    END IF;

                ELSEIF especie_nombre = 'Quercus rugosa' THEN
                    SELECT id_formula INTO formula_id FROM formulas 
                    WHERE nom_formula = 'Biomasa Quercus rugosa' LIMIT 1;
                    IF formula_id IS NOT NULL THEN
                        SET biomasa = 0.0192 * POW(NEW.diametro_pecho * 100, 2.7569);
                    END IF;

                ELSEIF especie_nombre = 'Pinus pseudostrobus' THEN
                    SELECT id_formula INTO formula_id FROM formulas 
                    WHERE nom_formula = 'Biomasa Pinus pseudostrobus' LIMIT 1;
                    IF formula_id IS NOT NULL THEN
                        SET biomasa = 0.3553 * POW(NEW.diametro_pecho * 100, 2.2245);
                    END IF;

                ELSEIF especie_nombre = 'Pinus montezumae' THEN
                    SELECT id_formula INTO formula_id FROM formulas 
                    WHERE nom_formula = 'Biomasa Pinus montezumae' LIMIT 1;
                    IF formula_id IS NOT NULL THEN
                        SET biomasa = 0.006 * POW(NEW.diametro_pecho * 100, 3.038);
                    END IF;
                END IF;

                -- Insertar estimación de Biomasa si aplica
                IF biomasa IS NOT NULL AND formula_id IS NOT NULL AND tipo_biomasa_id IS NOT NULL THEN
                    SET carbono = biomasa * factor_carbono;

                    INSERT INTO estimaciones1 (
                        id_tipo_e, id_formula, calculo, area_basal, biomasa, carbono, id_arbol, created_at, updated_at
                    ) VALUES (
                        tipo_biomasa_id, formula_id, biomasa, area_basal, biomasa, carbono, NEW.id_arbol, NOW(), NOW()
                    );

                    -- Insertar estimación de Carbono
                    IF tipo_carbono_id IS NOT NULL THEN
                        INSERT INTO estimaciones1 (
                            id_tipo_e, id_formula, calculo, area_basal, biomasa, carbono, id_arbol, created_at, updated_at
                        ) VALUES (
                            tipo_carbono_id, formula_id, carbono, area_basal, biomasa, carbono, NEW.id_arbol, NOW(), NOW()
                        );
                    END IF;
                END IF;
            END
        ");

        // =====================================================================
        // TRIGGER: before_insert_estimaciones1 (BEFORE INSERT en estimaciones1)
        // Calcula valores cuando se inserta manualmente una estimación de árbol
        // =====================================================================
        DB::unprepared('DROP TRIGGER IF EXISTS before_insert_estimaciones1');
        DB::unprepared("
            CREATE TRIGGER before_insert_estimaciones1 BEFORE INSERT ON estimaciones1
            FOR EACH ROW
            BEGIN
                DECLARE diametro_pecho DECIMAL(10,5);
                DECLARE altura_total DECIMAL(10,5);
                DECLARE especie_nombre VARCHAR(255);
                DECLARE factor_carbono DOUBLE DEFAULT 0.5;
                DECLARE nombre_formula VARCHAR(255);
                DECLARE area_basal DOUBLE;

                SELECT a.diametro_pecho, a.altura_total, e.nom_cientifico
                INTO diametro_pecho, altura_total, especie_nombre
                FROM arboles a
                JOIN especies e ON a.id_especie = e.id_especie
                WHERE a.id_arbol = NEW.id_arbol;

                IF NEW.id_formula IS NOT NULL THEN
                    SELECT nom_formula INTO nombre_formula
                    FROM formulas
                    WHERE id_formula = NEW.id_formula;
                END IF;

                IF NEW.id_formula IS NOT NULL THEN
                    CASE nombre_formula
                        WHEN 'Biomasa Quercus crassifolia' THEN
                            SET NEW.calculo = 0.283 * POW(POW(diametro_pecho * 100, 2) * altura_total, 0.807);
                            SET NEW.biomasa = NEW.calculo;
                            SET NEW.carbono = NEW.biomasa * factor_carbono;

                        WHEN 'Biomasa Quercus rugosa' THEN
                            SET NEW.calculo = 0.0192 * POW(diametro_pecho * 100, 2.7569);
                            SET NEW.biomasa = NEW.calculo;
                            SET NEW.carbono = NEW.biomasa * factor_carbono;

                        WHEN 'Biomasa Pinus pseudostrobus' THEN
                            SET NEW.calculo = 0.3553 * POW(diametro_pecho * 100, 2.2245);
                            SET NEW.biomasa = NEW.calculo;
                            SET NEW.carbono = NEW.biomasa * factor_carbono;

                        WHEN 'Biomasa Pinus montezumae' THEN
                            SET NEW.calculo = 0.006 * POW(diametro_pecho * 100, 3.038);
                            SET NEW.biomasa = NEW.calculo;
                            SET NEW.carbono = NEW.biomasa * factor_carbono;

                        ELSE
                            -- Si no es fórmula de biomasa, no recalcular
                            IF NEW.calculo IS NULL THEN
                                SET NEW.calculo = 0;
                            END IF;
                            IF NEW.biomasa IS NULL THEN
                                SET NEW.biomasa = 0;
                            END IF;
                            IF NEW.carbono IS NULL THEN
                                SET NEW.carbono = 0;
                            END IF;
                    END CASE;
                ELSE
                    IF NEW.calculo IS NULL THEN
                        SET NEW.calculo = 0;
                    END IF;
                    IF NEW.biomasa IS NULL THEN
                        SET NEW.biomasa = 0;
                    END IF;
                    IF NEW.carbono IS NULL THEN
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
        ");
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS calcular_todo_estimacion');
        DB::unprepared('DROP TRIGGER IF EXISTS actualizar_todo_estimacion');
        DB::unprepared('DROP TRIGGER IF EXISTS validar_arbol');
        DB::unprepared('DROP TRIGGER IF EXISTS calcular_estimaciones_arbol');
        DB::unprepared('DROP TRIGGER IF EXISTS before_insert_estimaciones1');
    }
};
