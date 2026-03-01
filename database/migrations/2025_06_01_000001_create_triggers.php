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
                        -- HUBER: V = (π/4) × dm² × L
                        IF cm IS NULL OR cm <= 0 OR cm > 5 THEN 
                            SIGNAL SQLSTATE '45000' 
                            SET MESSAGE_TEXT = 'Diámetro medio inválido: debe ser 0 < d <= 5 para Huber';
                        END IF;
                        SET v = TRUNCATE((pi_val / 4) * POW(cm, 2) * l, 30);

                    WHEN 2 THEN 
                        -- SMALIAN: V = (π/4) × ((d₀² + d₁²)/2) × L
                        IF c0 IS NULL OR c1 IS NULL OR c0 <= 0 OR c1 <= 0 OR c0 > 5 OR c1 > 5 THEN
                            SIGNAL SQLSTATE '45000' 
                            SET MESSAGE_TEXT = 'Diámetros extremos inválidos: deben ser 0 < d <= 5 para Smalian';
                        END IF;
                        SET v = TRUNCATE((pi_val / 4) * ((POW(c0, 2) + POW(c1, 2)) / 2) * l, 30);

                    WHEN 3 THEN 
                        -- TRONCO CONO: V = (π/12) × L × (d₀² + d₁² + d₀×d₁)
                        IF c0 IS NULL OR c1 IS NULL OR c0 <= 0 OR c1 <= 0 OR c0 > 5 OR c1 > 5 THEN
                            SIGNAL SQLSTATE '45000' 
                            SET MESSAGE_TEXT = 'Diámetros extremos inválidos: deben ser 0 < d <= 5 para Tronco de Cono';
                        END IF;
                        SET v = TRUNCATE((pi_val / 12) * l * (POW(c0, 2) + POW(c1, 2) + (c0 * c1)), 30);

                    WHEN 4 THEN 
                        -- NEWTON: V = (π/24) × L × (d₀² + 4×dm² + d₁²)
                        IF c0 IS NULL OR c1 IS NULL OR cm IS NULL OR 
                           c0 <= 0 OR c1 <= 0 OR cm <= 0 OR
                           c0 > 5 OR c1 > 5 OR cm > 5 THEN
                            SIGNAL SQLSTATE '45000' 
                            SET MESSAGE_TEXT = 'Diámetros inválidos: deben ser 0 < d <= 5 para Newton';
                        END IF;
                        SET v = TRUNCATE((pi_val / 24) * l * (POW(c0, 2) + 4 * POW(cm, 2) + POW(c1, 2)), 30);

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
                            -- HUBER: V = (π/4) × dm² × L
                            IF cm IS NULL THEN
                                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Diámetro medio requerido para Huber';
                            END IF;
                            SET v = (PI() / 4) * POW(cm, 2) * l;

                        WHEN 2 THEN 
                            -- SMALIAN: V = (π/4) × ((d₀² + d₁²)/2) × L
                            IF c0 IS NULL OR c1 IS NULL THEN
                                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Ambos diámetros extremos requeridos para Smalian';
                            END IF;
                            SET v = (PI() / 4) * ((POW(c0, 2) + POW(c1, 2)) / 2) * l;

                        WHEN 3 THEN 
                            -- TRONCO CONO: V = (π/12) × L × (d₀² + d₁² + d₀×d₁)
                            IF c0 IS NULL OR c1 IS NULL THEN
                                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Ambos diámetros extremos requeridos para Tronco de Cono';
                            END IF;
                            SET v = (PI() / 12) * l * (POW(c0, 2) + POW(c1, 2) + (c0 * c1));

                        WHEN 4 THEN 
                            -- NEWTON: V = (π/24) × L × (d₀² + 4×dm² + d₁²)
                            IF c0 IS NULL OR c1 IS NULL OR cm IS NULL THEN
                                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Diámetros extremos y medio requeridos para Newton';
                            END IF;
                            SET v = (PI() / 24) * l * (POW(c0, 2) + 4 * POW(cm, 2) + POW(c1, 2));

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
        // NOTA: El trigger calcular_estimaciones_arbol fue ELIMINADO.
        // Ahora las estimaciones de árboles son OPCIONALES y se crean manualmente
        // a través del seeder o la interfaz de usuario.
        // El trigger before_insert_estimaciones1 aún calcula los valores
        // cuando se inserta una estimación con calculo NULL o 0.
        // =====================================================================

        // =====================================================================
        // TRIGGER: before_insert_estimaciones1 (BEFORE INSERT en estimaciones1)
        // Calcula valores cuando se inserta manualmente una estimación de árbol
        // SOLO calcula si calculo es NULL o 0
        // Maneja tanto Volumen Maderable (id_formula NULL) como Biomasa (id_formula 5-8)
        // =====================================================================
        DB::unprepared('DROP TRIGGER IF EXISTS before_insert_estimaciones1');
        DB::unprepared("
            CREATE TRIGGER before_insert_estimaciones1 BEFORE INSERT ON estimaciones1
            FOR EACH ROW
            BEGIN
                DECLARE diametro_pecho_val DECIMAL(10,5);
                DECLARE altura_total_val DECIMAL(10,5);
                DECLARE factor_carbono DOUBLE DEFAULT 0.5;
                DECLARE d_cm DOUBLE;
                DECLARE area_basal DOUBLE;
                DECLARE id_tipo_volumen BIGINT;

                -- Solo calcular si no se proporcionó un valor
                IF NEW.calculo IS NULL OR NEW.calculo = 0 THEN
                    -- Obtener datos del árbol
                    SELECT a.diametro_pecho, a.altura_total
                    INTO diametro_pecho_val, altura_total_val
                    FROM arboles a
                    WHERE a.id_arbol = NEW.id_arbol;

                    -- Convertir diámetro a centímetros (asumiendo que viene en metros)
                    SET d_cm = diametro_pecho_val * 100;
                    
                    -- Calcular área basal (diámetro en metros)
                    SET area_basal = PI() * POW(diametro_pecho_val / 2, 2);
                    SET NEW.area_basal = area_basal;

                    -- Obtener ID de tipo Volumen Maderable
                    SELECT id_tipo_e INTO id_tipo_volumen FROM tipo_estimaciones WHERE desc_estimacion = 'Volumen Maderable' LIMIT 1;

                    -- Calcular según tipo de estimación y fórmula
                    IF NEW.id_formula IS NOT NULL THEN
                        CASE NEW.id_formula
                            WHEN 5 THEN -- Biomasa Pinus montezumae: 0.006 * D^3.038
                                SET NEW.calculo = 0.006 * POW(d_cm, 3.038);
                                SET NEW.biomasa = NEW.calculo;
                                SET NEW.carbono = NEW.biomasa * factor_carbono;

                            WHEN 6 THEN -- Biomasa Quercus crassifolia: 0.283 * (D²*H)^0.807
                                SET NEW.calculo = 0.283 * POW(POW(d_cm, 2) * altura_total_val, 0.807);
                                SET NEW.biomasa = NEW.calculo;
                                SET NEW.carbono = NEW.biomasa * factor_carbono;

                            WHEN 7 THEN -- Biomasa Quercus rugosa: 0.0192 * D^2.7569
                                SET NEW.calculo = 0.0192 * POW(d_cm, 2.7569);
                                SET NEW.biomasa = NEW.calculo;
                                SET NEW.carbono = NEW.biomasa * factor_carbono;

                            WHEN 8 THEN -- Biomasa Pinus pseudostrobus: 0.3553 * D^2.2245
                                SET NEW.calculo = 0.3553 * POW(d_cm, 2.2245);
                                SET NEW.biomasa = NEW.calculo;
                                SET NEW.carbono = NEW.biomasa * factor_carbono;

                            ELSE
                                -- Fórmulas 1-4 son para trozas, no aplicar aquí
                                SET NEW.calculo = IFNULL(NEW.calculo, 0);
                                SET NEW.biomasa = IFNULL(NEW.biomasa, 0);
                                SET NEW.carbono = IFNULL(NEW.carbono, 0);
                        END CASE;
                    ELSE
                        -- Sin fórmula: Calcular Volumen Maderable
                        -- V = área_basal * altura * factor_forma (0.5)
                        IF NEW.id_tipo_e = id_tipo_volumen OR NEW.id_tipo_e = 1 THEN
                            SET NEW.calculo = area_basal * altura_total_val * 0.5;
                            SET NEW.biomasa = 0;
                            SET NEW.carbono = 0;
                        ELSE
                            SET NEW.calculo = IFNULL(NEW.calculo, 0);
                            SET NEW.biomasa = IFNULL(NEW.biomasa, 0);
                            SET NEW.carbono = IFNULL(NEW.carbono, 0);
                        END IF;
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
        DB::unprepared('DROP TRIGGER IF EXISTS before_insert_estimaciones1');
    }
};
