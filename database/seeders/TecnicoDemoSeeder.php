<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Persona;
use App\Models\Tecnico;
use App\Models\Productor;
use App\Models\Rol;
use App\Models\Parcela;
use App\Models\Especie;
use App\Models\Troza;
use App\Models\Arbol;
use App\Models\Estimacion;
use App\Models\Estimacion1;
use App\Models\Tipo_Estimacion;
use App\Models\Formula;
use App\Models\Asigna_Parcela;
use Illuminate\Support\Facades\Hash;

class TecnicoDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear roles si no existen
        $rolTecnico = Rol::firstOrCreate(['nom_rol' => 'Tecnico']);
        $rolProductor = Rol::firstOrCreate(['nom_rol' => 'Productor']);

        // Obtener especies mexicanas
        $pinus_montezumae = Especie::firstOrCreate(
            ['nom_cientifico' => 'Pinus montezumae'],
            ['nom_comun' => 'Pino Montezuma']
        );
        $pinus_pseudostrobus = Especie::firstOrCreate(
            ['nom_cientifico' => 'Pinus pseudostrobus'],
            ['nom_comun' => 'Pino Lacio']
        );
        $quercus_rugosa = Especie::firstOrCreate(
            ['nom_cientifico' => 'Quercus rugosa'],
            ['nom_comun' => 'Encino Blanco']
        );
        $quercus_crassifolia = Especie::firstOrCreate(
            ['nom_cientifico' => 'Quercus crassifolia'],
            ['nom_comun' => 'Encino Avellano']
        );

        // Obtener/Crear fórmulas de biomasa (solo si existen en DB)
        // Las fórmulas deben estar previamente creadas en las migraciones
        // NO intentamos crearlas aquí para evitar conflictos de restricciones
        
        // Tipo de estimación Biomasa
        $tipo_biomasa = Tipo_Estimacion::where('desc_estimacion', 'Biomasa')->first() 
                        ?? Tipo_Estimacion::create(['desc_estimacion' => 'Biomasa']);

        // ====== TÉCNICO 1: CARLOS ======
        $persona1 = Persona::create([
            'nom' => 'Carlos',
            'ap' => 'Mendoza',
            'am' => 'García',
            'telefono' => '5551234567',
            'correo' => 'carlos.mendoza@email.com',
            'contrasena' => Hash::make('password123'),
            'id_rol' => $rolTecnico->id_rol,
        ]);

        $user1 = User::create([
            'name' => 'Carlos Mendoza García',
            'email' => 'carlos.tecnico@test.com',
            'password' => Hash::make('password123'),
            'id_persona' => $persona1->id_persona,
        ]);

        $tecnico1 = Tecnico::create([
            'id_persona' => $persona1->id_persona,
            'cedula_p' => '001-TEC-2026',
        ]);

        // Productor 1 para Carlos
        $persona_prod1 = Persona::create([
            'nom' => 'Roberto',
            'ap' => 'López',
            'am' => 'Hernández',
            'telefono' => '5559876543',
            'correo' => 'roberto.lopez@email.com',
            'contrasena' => Hash::make('password123'),
            'id_rol' => $rolProductor->id_rol,
        ]);

        $productor1 = Productor::create([
            'id_persona' => $persona_prod1->id_persona,
        ]);

        // Parcela 1 - Pinus montezumae
        $parcela1 = Parcela::create([
            'nom_parcela' => 'Parcela Bosque Pino Alto',
            'ubicacion' => 'Oaxaca, México',
            'id_productor' => $productor1->id_productor,
            'extension' => 15.5,
            'direccion' => 'Carretera Federal 190, Km 120',
            'CP' => '68100',
        ]);

        Asigna_Parcela::create([
            'id_tecnico' => $tecnico1->id_tecnico,
            'id_parcela' => $parcela1->id_parcela,
        ]);

        // Trozas en Parcela 1
        $troza1 = Troza::create([
            'id_parcela' => $parcela1->id_parcela,
            'longitud' => 6.5,
            'diametro' => 0.45,
            'diametro_otro_extremo' => 0.38,
            'diametro_medio' => 0.42,
            'densidad' => 0.575, // Pinus montezumae
            'id_especie' => $pinus_montezumae->id_especie ?? 3,
        ]);

        $troza2 = Troza::create([
            'id_parcela' => $parcela1->id_parcela,
            'longitud' => 5.2,
            'diametro' => 0.52,
            'diametro_otro_extremo' => 0.48,
            'diametro_medio' => 0.50,
            'densidad' => 0.575,
            'id_especie' => $pinus_montezumae->id_especie ?? 3,
        ]);

        // Estimaciones de Trozas (Fórmula Smalian)
        // Nota: Las fórmulas deben existir en la BD
        // Estimacion::create([
        //     'id_troza' => $troza1->id_troza,
        //     'id_formula' => 2, // Smalian
        //     'id_tipo_e' => 1,
        // ]);

        // Estimacion::create([
        //     'id_troza' => $troza2->id_troza,
        //     'id_formula' => 2, // Smalian
        //     'id_tipo_e' => 1,
        // ]);

        // Árboles en Parcela 1
        $arbol1 = Arbol::create([
            'id_parcela' => $parcela1->id_parcela,
            'altura_total' => 28.5,
            'diametro_pecho' => 0.68,
            'id_especie' => $pinus_montezumae->id_especie ?? 3,
        ]);

        $arbol2 = Arbol::create([
            'id_parcela' => $parcela1->id_parcela,
            'altura_total' => 25.3,
            'diametro_pecho' => 0.55,
            'id_especie' => $pinus_montezumae->id_especie ?? 3,
        ]);

        // Estimaciones de Árboles
        // Estimacion1::create([
        //     'id_arbol' => $arbol1->id_arbol,
        //     'id_tipo_e' => $tipo_biomasa->id_tipo_e,
        //     'id_formula' => 5, // Biomasa Pinus montezumae
        // ]);

        // Estimacion1::create([
        //     'id_arbol' => $arbol2->id_arbol,
        //     'id_tipo_e' => $tipo_biomasa->id_tipo_e,
        //     'id_formula' => 5,
        // ]);

        // ====== TÉCNICO 2: MARÍA ======
        $persona2 = Persona::create([
            'nom' => 'María',
            'ap' => 'Rodríguez',
            'am' => 'Sánchez',
            'telefono' => '5555555555',
            'correo' => 'maria.rodriguez@email.com',
            'contrasena' => Hash::make('password123'),
            'id_rol' => $rolTecnico->id_rol,
        ]);

        $user2 = User::create([
            'name' => 'María Rodríguez Sánchez',
            'email' => 'maria.tecnico@test.com',
            'password' => Hash::make('password123'),
            'id_persona' => $persona2->id_persona,
        ]);

        $tecnico2 = Tecnico::create([
            'id_persona' => $persona2->id_persona,
            'cedula_p' => '002-TEC-2026',
        ]);

        // Productor 2 para María
        $persona_prod2 = Persona::create([
            'nom' => 'Fernando',
            'ap' => 'Pérez',
            'am' => 'Gutiérrez',
            'telefono' => '5554444444',
            'correo' => 'fernando.perez@email.com',
            'contrasena' => Hash::make('password123'),
            'id_rol' => $rolProductor->id_rol,
        ]);

        $productor2 = Productor::create([
            'id_persona' => $persona_prod2->id_persona,
        ]);

        // Parcela 2 - Quercus rugosa
        $parcela2 = Parcela::create([
            'nom_parcela' => 'Parcela Encino Blanco',
            'ubicacion' => 'Guerrero, México',
            'id_productor' => $productor2->id_productor,
            'extension' => 22.3,
            'direccion' => 'Zona Montañosa, Sector Norte',
            'CP' => '40900',
        ]);

        Asigna_Parcela::create([
            'id_tecnico' => $tecnico2->id_tecnico,
            'id_parcela' => $parcela2->id_parcela,
        ]);

        // Trozas en Parcela 2
        $troza3 = Troza::create([
            'id_parcela' => $parcela2->id_parcela,
            'longitud' => 7.8,
            'diametro' => 0.65,
            'diametro_otro_extremo' => 0.60,
            'diametro_medio' => 0.63,
            'densidad' => 0.780, // Quercus rugosa
            'id_especie' => $quercus_rugosa->id_especie ?? 2,
        ]);

        $troza4 = Troza::create([
            'id_parcela' => $parcela2->id_parcela,
            'longitud' => 6.0,
            'diametro' => 0.58,
            'diametro_otro_extremo' => 0.52,
            'diametro_medio' => 0.55,
            'densidad' => 0.780,
            'id_especie' => $quercus_rugosa->id_especie ?? 2,
        ]);

        // Estimaciones de Trozas
        // Estimacion::create([
        //     'id_troza' => $troza3->id_troza,
        //     'id_formula' => 3, // Tronco de Cono
        //     'id_tipo_e' => 1,
        // ]);

        // Estimacion::create([
        //     'id_troza' => $troza4->id_troza,
        //     'id_formula' => 3,
        //     'id_tipo_e' => 1,
        // ]);

        // Árboles en Parcela 2
        $arbol3 = Arbol::create([
            'id_parcela' => $parcela2->id_parcela,
            'altura_total' => 20.8,
            'diametro_pecho' => 0.72,
            'id_especie' => $quercus_rugosa->id_especie ?? 2,
        ]);

        $arbol4 = Arbol::create([
            'id_parcela' => $parcela2->id_parcela,
            'altura_total' => 19.5,
            'diametro_pecho' => 0.64,
            'id_especie' => $quercus_rugosa->id_especie ?? 2,
        ]);

        // Estimaciones de Árboles
        // Estimacion1::create([
        //     'id_arbol' => $arbol3->id_arbol,
        //     'id_tipo_e' => $tipo_biomasa->id_tipo_e,
        //     'id_formula' => 7, // Biomasa Quercus rugosa
        // ]);

        // Estimacion1::create([
        //     'id_arbol' => $arbol4->id_arbol,
        //     'id_tipo_e' => $tipo_biomasa->id_tipo_e,
        //     'id_formula' => 7,
        // ]);

        // ====== TÉCNICO 3: JUAN ======
        $persona3 = Persona::create([
            'nom' => 'Juan',
            'ap' => 'Martínez',
            'am' => 'López',
            'telefono' => '5553333333',
            'correo' => 'juan.martinez@email.com',
            'contrasena' => Hash::make('password123'),
            'id_rol' => $rolTecnico->id_rol,
        ]);

        $user3 = User::create([
            'name' => 'Juan Martínez López',
            'email' => 'juan.tecnico@test.com',
            'password' => Hash::make('password123'),
            'id_persona' => $persona3->id_persona,
        ]);

        $tecnico3 = Tecnico::create([
            'id_persona' => $persona3->id_persona,
            'cedula_p' => '003-TEC-2026',
        ]);

        // Productor 3 para Juan
        $persona_prod3 = Persona::create([
            'nom' => 'Eduardo',
            'ap' => 'Flores',
            'am' => 'Morales',
            'telefono' => '5552222222',
            'correo' => 'eduardo.flores@email.com',
            'contrasena' => Hash::make('password123'),
            'id_rol' => $rolProductor->id_rol,
        ]);

        $productor3 = Productor::create([
            'id_persona' => $persona_prod3->id_persona,
        ]);

        // Parcela 3 - Quercus crassifolia
        $parcela3 = Parcela::create([
            'nom_parcela' => 'Parcela Encino Avellano',
            'ubicacion' => 'Estado de México',
            'id_productor' => $productor3->id_productor,
            'extension' => 18.9,
            'direccion' => 'Bosque de Nieve, Zona Protegida',
            'CP' => '52600',
        ]);

        Asigna_Parcela::create([
            'id_tecnico' => $tecnico3->id_tecnico,
            'id_parcela' => $parcela3->id_parcela,
        ]);

        // Trozas en Parcela 3
        $troza5 = Troza::create([
            'id_parcela' => $parcela3->id_parcela,
            'longitud' => 8.2,
            'diametro' => 0.70,
            'diametro_otro_extremo' => 0.65,
            'diametro_medio' => 0.68,
            'densidad' => 0.720, // Quercus crassifolia
            'id_especie' => $quercus_crassifolia->id_especie ?? 4,
        ]);

        $troza6 = Troza::create([
            'id_parcela' => $parcela3->id_parcela,
            'longitud' => 7.1,
            'diametro' => 0.62,
            'diametro_otro_extremo' => 0.56,
            'diametro_medio' => 0.59,
            'densidad' => 0.720,
            'id_especie' => $quercus_crassifolia->id_especie ?? 4,
        ]);

        // Estimaciones de Trozas
        // Estimacion::create([
        //     'id_troza' => $troza5->id_troza,
        //     'id_formula' => 4, // Newton
        //     'id_tipo_e' => 1,
        // ]);

        // Estimacion::create([
        //     'id_troza' => $troza6->id_troza,
        //     'id_formula' => 4,
        //     'id_tipo_e' => 1,
        // ]);

        // Árboles en Parcela 3
        $arbol5 = Arbol::create([
            'id_parcela' => $parcela3->id_parcela,
            'altura_total' => 22.4,
            'diametro_pecho' => 0.70,
            'id_especie' => $quercus_crassifolia->id_especie ?? 4,
        ]);

        $arbol6 = Arbol::create([
            'id_parcela' => $parcela3->id_parcela,
            'altura_total' => 21.0,
            'diametro_pecho' => 0.61,
            'id_especie' => $quercus_crassifolia->id_especie ?? 4,
        ]);

        // Estimaciones de Árboles
        // Estimacion1::create([
        //     'id_arbol' => $arbol5->id_arbol,
        //     'id_tipo_e' => $tipo_biomasa->id_tipo_e,
        //     'id_formula' => 6, // Biomasa Quercus crassifolia
        // ]);

        // Estimacion1::create([
        //     'id_arbol' => $arbol6->id_arbol,
        //     'id_tipo_e' => $tipo_biomasa->id_tipo_e,
        //     'id_formula' => 6,
        // ]);

        // ====== TÉCNICO 4: ANA ======
        $persona4 = Persona::create([
            'nom' => 'Ana',
            'ap' => 'Jiménez',
            'am' => 'Vargas',
            'telefono' => '5556666666',
            'correo' => 'ana.jimenez@email.com',
            'contrasena' => Hash::make('password123'),
            'id_rol' => $rolTecnico->id_rol,
        ]);

        $user4 = User::create([
            'name' => 'Ana Jiménez Vargas',
            'email' => 'ana.tecnico@test.com',
            'password' => Hash::make('password123'),
            'id_persona' => $persona4->id_persona,
        ]);

        $tecnico4 = Tecnico::create([
            'id_persona' => $persona4->id_persona,
            'cedula_p' => '004-TEC-2026',
        ]);

        // Productor 4 para Ana
        $persona_prod4 = Persona::create([
            'nom' => 'Vicente',
            'ap' => 'Castillo',
            'am' => 'Ramírez',
            'telefono' => '5557777777',
            'correo' => 'vicente.castillo@email.com',
            'contrasena' => Hash::make('password123'),
            'id_rol' => $rolProductor->id_rol,
        ]);

        $productor4 = Productor::create([
            'id_persona' => $persona_prod4->id_persona,
        ]);

        // Parcela 4 - Pinus pseudostrobus (Mezclada)
        $parcela4 = Parcela::create([
            'nom_parcela' => 'Parcela Pino Lacio Mixto',
            'ubicacion' => 'Chiapas, México',
            'id_productor' => $productor4->id_productor,
            'extension' => 25.6,
            'direccion' => 'Región Sur, Sistema Montañoso',
            'CP' => '29000',
        ]);

        Asigna_Parcela::create([
            'id_tecnico' => $tecnico4->id_tecnico,
            'id_parcela' => $parcela4->id_parcela,
        ]);

        // Trozas en Parcela 4
        $troza7 = Troza::create([
            'id_parcela' => $parcela4->id_parcela,
            'longitud' => 6.8,
            'diametro' => 0.48,
            'diametro_otro_extremo' => 0.42,
            'diametro_medio' => 0.45,
            'densidad' => 0.570, // Pinus pseudostrobus
            'id_especie' => $pinus_pseudostrobus->id_especie ?? 1,
        ]);

        $troza8 = Troza::create([
            'id_parcela' => $parcela4->id_parcela,
            'longitud' => 7.5,
            'diametro' => 0.55,
            'diametro_otro_extremo' => 0.50,
            'diametro_medio' => 0.53,
            'densidad' => 0.570,
            'id_especie' => $pinus_pseudostrobus->id_especie ?? 1,
        ]);

        // Estimaciones de Trozas
        // Estimacion::create([
        //     'id_troza' => $troza7->id_troza,
        //     'id_formula' => 1, // Huber
        //     'id_tipo_e' => 1,
        // ]);

        // Estimacion::create([
        //     'id_troza' => $troza8->id_troza,
        //     'id_formula' => 1,
        //     'id_tipo_e' => 1,
        // ]);

        // Árboles en Parcela 4
        $arbol7 = Arbol::create([
            'id_parcela' => $parcela4->id_parcela,
            'altura_total' => 26.2,
            'diametro_pecho' => 0.60,
            'id_especie' => $pinus_pseudostrobus->id_especie ?? 1,
        ]);

        $arbol8 = Arbol::create([
            'id_parcela' => $parcela4->id_parcela,
            'altura_total' => 24.0,
            'diametro_pecho' => 0.52,
            'id_especie' => $pinus_pseudostrobus->id_especie ?? 1,
        ]);

        // Estimaciones de Árboles
        // Estimacion1::create([
        //     'id_arbol' => $arbol7->id_arbol,
        //     'id_tipo_e' => $tipo_biomasa->id_tipo_e,
        //     'id_formula' => 8, // Biomasa Pinus pseudostrobus
        // ]);

        // Estimacion1::create([
        //     'id_arbol' => $arbol8->id_arbol,
        //     'id_tipo_e' => $tipo_biomasa->id_tipo_e,
        //     'id_formula' => 8,
        // ]);

        $this->command->info('✅ Seeder de técnicos completado exitosamente');
        $this->command->info('');
        $this->command->line('📧 Credenciales de acceso:');
        $this->command->line('─────────────────────────────────────────');
        $this->command->line('👨 TÉCNICO 1: Carlos Mendoza García');
        $this->command->line('   Email: carlos.tecnico@test.com');
        $this->command->line('   Clave: password123');
        $this->command->line('   Parcela: Bosque Pino Alto (15.5 ha) - Pinus montezumae');
        $this->command->line('');
        $this->command->line('👩 TÉCNICO 2: María Rodríguez Sánchez');
        $this->command->line('   Email: maria.tecnico@test.com');
        $this->command->line('   Clave: password123');
        $this->command->line('   Parcela: Encino Blanco (22.3 ha) - Quercus rugosa');
        $this->command->line('');
        $this->command->line('👨 TÉCNICO 3: Juan Martínez López');
        $this->command->line('   Email: juan.tecnico@test.com');
        $this->command->line('   Clave: password123');
        $this->command->line('   Parcela: Encino Avellano (18.9 ha) - Quercus crassifolia');
        $this->command->line('');
        $this->command->line('👩 TÉCNICO 4: Ana Jiménez Vargas');
        $this->command->line('   Email: ana.tecnico@test.com');
        $this->command->line('   Clave: password123');
        $this->command->line('   Parcela: Pino Lacio Mixto (25.6 ha) - Pinus pseudostrobus');
        $this->command->line('─────────────────────────────────────────');
    }
}
