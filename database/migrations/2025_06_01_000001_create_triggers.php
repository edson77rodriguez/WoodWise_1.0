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
        //
        // NOTA IMPORTANTE: c0, c1, cm son CIRCUNFERENCIAS (no diámetros),
        // igual que en el libro de referencia (sección 4.4/4.5). Por eso
        // las fórmulas dividen entre 4π, 12π y 24π en vez de multiplicar
        // por π/4, π/12, π/24 (que serían las fórmulas equivalentes si
        // se usara diámetro).
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
                        -- HUBER: V = L/(4π) × c0.5²
                        IF cm IS NULL OR cm <= 0 OR cm > 5 THEN 
                            SIGNAL SQLSTATE '45000' 
                            SET MESSAGE_TEXT = 'Circunferencia media inválida: debe ser 0 < c <= 5 para Huber';
                        END IF;
                        SET v = TRUNCATE((l / (4 * pi_val)) * POW(cm, 2), 30);

                    WHEN 2 THEN 
                        -- SMALIAN: V = L/(4π) × ((c0² + c1²)/2)
                        IF c0 IS NULL OR c1 IS NULL OR c0 <= 0 OR c1 <= 0 OR c0 > 5 OR c1 > 5 THEN
                            SIGNAL SQLSTATE '45000' 
                            SET MESSAGE_TEXT = 'Circunferencias extremas inválidas: deben ser 0 < c <= 5 para Smalian';
                        END IF;
                        SET v = TRUNCATE((l / (4 * pi_val)) * ((POW(c0, 2) + POW(c1, 2)) / 2), 30);

                    WHEN 3 THEN 
                        -- TRONCO CONO: V = L/(12π) × (c0² + c1² + c0×c1)
                        IF c0 IS NULL OR c1 IS NULL OR c0 <= 0 OR c1 <= 0 OR c0 > 5 OR c1 > 5 THEN
                            SIGNAL SQLSTATE '45000' 
                            SET MESSAGE_TEXT = 'Circunferencias extremas inválidas: deben ser 0 < c <= 5 para Tronco de Cono';
                        END IF;
                        SET v = TRUNCATE((l / (12 * pi_val)) * (POW(c0, 2) + POW(c1, 2) + (c0 * c1)), 30);

                    WHEN 4 THEN 
                        -- NEWTON: V = L/(24π) × (c0² + 4×cm² + c1²)
                        IF c0 IS NULL OR c1 IS NULL OR cm IS NULL OR 
                           c0 <= 0 OR c1 <= 0 OR cm <= 0 OR
                           c0 > 5 OR c1 > 5 OR cm > 5 THEN
                            SIGNAL SQLSTATE '45000' 
                            SET MESSAGE_TEXT = 'Circunferencias inválidas: deben ser 0 < c <= 5 para Newton';
                        END IF;
                        SET v = TRUNCATE((l / (24 * pi_val)) * (POW(c0, 2) + 4 * POW(cm, 2) + POW(c1, 2)), 30);

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
        // Mismas fórmulas (circunferencia) que el trigger de INSERT.
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
                            -- HUBER: V = L/(4π) × c0.5²
                            IF cm IS NULL THEN
                                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Circunferencia media requerida para Huber';
                            END IF;
                            SET v = (l / (4 * PI())) * POW(cm, 2);

                        WHEN 2 THEN 
                            -- SMALIAN: V = L/(4π) × ((c0² + c1²)/2)
                            IF c0 IS NULL OR c1 IS NULL THEN
                                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Ambas circunferencias extremas requeridas para Smalian';
                            END IF;
                            SET v = (l / (4 * PI())) * ((POW(c0, 2) + POW(c1, 2)) / 2);

                        WHEN 3 THEN 
                            -- TRONCO CONO: V = L/(12π) × (c0² + c1² + c0×c1)
                            IF c0 IS NULL OR c1 IS NULL THEN
                                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Ambas circunferencias extremas requeridas para Tronco de Cono';
                            END IF;
                            SET v = (l / (12 * PI())) * (POW(c0, 2) + POW(c1, 2) + (c0 * c1));

                        WHEN 4 THEN 
                            -- NEWTON: V = L/(24π) × (c0² + 4×cm² + c1²)
                            IF c0 IS NULL OR c1 IS NULL OR cm IS NULL THEN
                                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Circunferencias extremas y media requeridas para Newton';
                            END IF;
                            SET v = (l / (24 * PI())) * (POW(c0, 2) + 4 * POW(cm, 2) + POW(c1, 2));

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
        // Sin cambios: no depende de las fórmulas de volumen de trozas.
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
        // Biomasa en TONELADAS, Volumen Maderable Aproximado en m³
        // Estas son fórmulas alométricas independientes (no de trozas), no
        // requieren corrección de circunferencia/diámetro.
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
                DECLARE biomasa_kg DOUBLE;
                DECLARE densidad_basica DOUBLE;
                DECLARE volumen_maderable DOUBLE;

                -- Solo calcular si no se proporcionó un valor
                IF NEW.calculo IS NULL OR NEW.calculo = 0 THEN
                    -- Obtener datos del árbol
                    SELECT a.diametro_pecho, a.altura_total
                    INTO diametro_pecho_val, altura_total_val
                    FROM arboles a
                    WHERE a.id_arbol = NEW.id_arbol;

                    -- Convertir diámetro a centímetros
                    SET d_cm = diametro_pecho_val * 100;

                    -- Calcular según tipo de estimación y fórmula
                    IF NEW.id_formula IS NOT NULL THEN
                        CASE NEW.id_formula
                            WHEN 5 THEN -- Biomasa Pinus montezumae: 0.006 * D^3.038 (en kg)
                                SET biomasa_kg = 0.006 * POW(d_cm, 3.038);
                                SET densidad_basica = 575; -- kg/m³

                            WHEN 6 THEN -- Biomasa Quercus crassifolia: 0.283 * (D²*H)^0.807 (en kg)
                                SET biomasa_kg = 0.283 * POW(POW(d_cm, 2) * altura_total_val, 0.807);
                                SET densidad_basica = 720; -- kg/m³

                            WHEN 7 THEN -- Biomasa Quercus rugosa: 0.0192 * D^2.7569 (en kg)
                                SET biomasa_kg = 0.0192 * POW(d_cm, 2.7569);
                                SET densidad_basica = 780; -- kg/m³

                            WHEN 8 THEN -- Biomasa Pinus pseudostrobus: 0.3553 * D^2.2245 (en kg)
                                SET biomasa_kg = 0.3553 * POW(d_cm, 2.2245);
                                SET densidad_basica = 570; -- kg/m³

                            ELSE
                                -- Fórmulas 1-4 son para trozas, no aplicar aquí
                                SET biomasa_kg = 0;
                                SET densidad_basica = 0;
                        END CASE;

                        -- Convertir biomasa de kg a toneladas
                        SET NEW.biomasa = ROUND(biomasa_kg / 1000, 10);
                        
                        -- Calcular Volumen Maderable Aproximado = Biomasa (kg) / Densidad Básica
                        SET volumen_maderable = biomasa_kg / densidad_basica;
                        SET NEW.calculo = ROUND(volumen_maderable, 10);
                        
                        -- Carbono: 50% de la biomasa (en toneladas)
                        SET NEW.carbono = ROUND(NEW.biomasa * factor_carbono, 10);
                    ELSE
                        -- Sin fórmula: valores por defecto
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