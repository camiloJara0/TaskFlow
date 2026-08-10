<?php

namespace Database\Seeders;

use App\Models\Logro;
use Illuminate\Database\Seeder;

class LogrosSeeder extends Seeder
{
    public function run(): void
    {
        $logros = [
            ['slug' => 'primera-victoria', 'titulo' => 'Primera Victoria', 'descripcion' => 'Completa tu primera tarea.', 'icono' => 'i-lucide-trophy', 'rareza' => 'comun', 'categoria' => 'productividad', 'condicion' => 'Completa cualquier tarea', 'meta' => 1],
            ['slug' => 'productivo', 'titulo' => 'Productivo', 'descripcion' => 'Completa 25 tareas.', 'icono' => 'i-lucide-zap', 'rareza' => 'raro', 'categoria' => 'productividad', 'condicion' => 'Completa 25 tareas', 'meta' => 25],
            ['slug' => 'incansable', 'titulo' => 'Incansable', 'descripcion' => 'Completa 100 tareas.', 'icono' => 'i-lucide-flame', 'rareza' => 'epico', 'categoria' => 'productividad', 'condicion' => 'Completa 100 tareas', 'meta' => 100],
            ['slug' => 'maestro-organizacion', 'titulo' => 'Maestro de la Organización', 'descripcion' => 'Completa 500 tareas.', 'icono' => 'i-lucide-crown', 'rareza' => 'legendario', 'categoria' => 'organizacion', 'condicion' => 'Completa 500 tareas', 'meta' => 500],
            ['slug' => 'racha-semana', 'titulo' => 'Constancia Semanal', 'descripcion' => 'Mantén una racha de 7 días.', 'icono' => 'i-lucide-calendar-check', 'rareza' => 'comun', 'categoria' => 'constancia', 'condicion' => 'Racha de 7 días', 'meta' => 7],
            ['slug' => 'racha-quince', 'titulo' => 'Quince Días', 'descripcion' => 'Mantén una racha de 15 días.', 'icono' => 'i-lucide-calendar-check', 'rareza' => 'raro', 'categoria' => 'constancia', 'condicion' => 'Racha de 15 días', 'meta' => 15],
            ['slug' => 'imparable', 'titulo' => 'Imparable', 'descripcion' => 'Mantén una racha de 30 días.', 'icono' => 'i-lucide-flame', 'rareza' => 'epico', 'categoria' => 'constancia', 'condicion' => 'Racha de 30 días', 'meta' => 30],
            ['slug' => 'leyenda', 'titulo' => 'Leyenda', 'descripcion' => 'Mantén una racha de 100 días.', 'icono' => 'i-lucide-gem', 'rareza' => 'legendario', 'categoria' => 'constancia', 'condicion' => 'Racha de 100 días', 'meta' => 100],
            ['slug' => 'rapido', 'titulo' => 'Rápido', 'descripcion' => 'Completa 10 tareas en un día.', 'icono' => 'i-lucide-rocket', 'rareza' => 'epico', 'categoria' => 'productividad', 'condicion' => '10 tareas en un día', 'meta' => 10],
            ['slug' => 'perfeccionista', 'titulo' => 'Perfeccionista', 'descripcion' => 'Completa 20 tareas en una semana.', 'icono' => 'i-lucide-target', 'rareza' => 'epico', 'categoria' => 'disciplina', 'condicion' => '20 tareas en una semana', 'meta' => 20],
            ['slug' => 'madrugador', 'titulo' => 'Madrugador', 'descripcion' => 'Completa una tarea antes de las 6 AM.', 'icono' => 'i-lucide-sunrise', 'rareza' => 'raro', 'categoria' => 'disciplina', 'condicion' => 'Completa antes de las 6:00', 'meta' => null],
            ['slug' => 'nocturno', 'titulo' => 'Nocturno', 'descripcion' => 'Completa una tarea después de las 11 PM.', 'icono' => 'i-lucide-moon-star', 'rareza' => 'raro', 'categoria' => 'disciplina', 'condicion' => 'Completa después de las 23:00', 'meta' => null],
            ['slug' => 'nivel-10', 'titulo' => 'Nivel 10', 'descripcion' => 'Alcanza el nivel 10.', 'icono' => 'i-lucide-arrow-up', 'rareza' => 'raro', 'categoria' => 'organizacion', 'condicion' => 'Alcanza el nivel 10', 'meta' => 10],
            ['slug' => 'nivel-20', 'titulo' => 'Nivel 20', 'descripcion' => 'Alcanza el nivel 20.', 'icono' => 'i-lucide-arrow-up', 'rareza' => 'epico', 'categoria' => 'organizacion', 'condicion' => 'Alcanza el nivel 20', 'meta' => 20],
            ['slug' => 'nivel-35', 'titulo' => 'Nivel 35', 'descripcion' => 'Alcanza el nivel 35.', 'icono' => 'i-lucide-star', 'rareza' => 'epico', 'categoria' => 'organizacion', 'condicion' => 'Alcanza el nivel 35', 'meta' => 35],
            ['slug' => 'nivel-50', 'titulo' => 'Nivel 50', 'descripcion' => 'Alcanza el nivel 50.', 'icono' => 'i-lucide-crown', 'rareza' => 'legendario', 'categoria' => 'organizacion', 'condicion' => 'Alcanza el nivel 50', 'meta' => 50],
        ];

        foreach ($logros as $logro) {
            Logro::updateOrCreate(['slug' => $logro['slug']], $logro);
        }
    }
}
