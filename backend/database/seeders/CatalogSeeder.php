<?php

namespace Database\Seeders;

use App\Modules\Configuration\Models\SystemSetting;
use App\Modules\Identity\Models\Role;
use App\Modules\People\Models\AvailabilitySlot;
use App\Modules\People\Models\Interest;
use App\Modules\People\Models\Talent;
use App\Modules\YouthMinistry\Models\ActivityType;
use App\Modules\YouthMinistry\Models\Responsibility;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['admin' => 'Administrador', 'gp_leader' => 'Líder de Grupo Pequeño', 'ja_director' => 'Director de Ministerio Joven', 'member' => 'Miembro'] as $code => $name) {
            Role::firstOrCreate(['code' => $code], ['name' => $name]);
        }
        foreach (['Fotografía', 'Música', 'Cocina', 'Tecnología', 'Organización', 'Enseñanza', 'Diseño', 'Deporte', 'Sonido', 'Liderazgo'] as $name) {
            Talent::firstOrCreate(['name' => $name]);
        }
        foreach (['Servicio comunitario', 'Música', 'Tecnología', 'Deporte', 'Creatividad', 'Visitas', 'Talleres', 'Confraternización'] as $name) {
            Interest::firstOrCreate(['name' => $name]);
        }
        foreach (['Adoración', 'Relaciones', 'Talentos', 'Formación', 'Misión', 'Servicio', 'Salud', 'Creatividad', 'Especial'] as $name) {
            ActivityType::firstOrCreate(['name' => $name]);
        }
        foreach (['Fotografía', 'Música', 'Recepción', 'Multimedia', 'Sonido', 'Dinámica', 'Refrigerio', 'Visita', 'Logística'] as $name) {
            Responsibility::firstOrCreate(['name' => $name]);
        }
        AvailabilitySlot::firstOrCreate(['name' => 'Viernes por la noche'], ['weekday' => 5, 'starts_at' => '18:00', 'ends_at' => '23:00']);
        AvailabilitySlot::firstOrCreate(['name' => 'Sábado por la tarde'], ['weekday' => 6, 'starts_at' => '14:00', 'ends_at' => '21:00']);
        SystemSetting::firstOrCreate(['key' => 'absence_threshold'], ['value' => 3]);
        SystemSetting::firstOrCreate(['key' => 'church_name'], ['value' => 'Iglesia local']);
        foreach (['Servicio' => 'Servicio comunitario', 'Talentos' => 'Creatividad', 'Salud' => 'Deporte', 'Formación' => 'Talleres', 'Relaciones' => 'Confraternización'] as $type => $interest) {
            DB::table('activity_type_interests')->updateOrInsert(['activity_type_id' => ActivityType::where('name', $type)->value('id'), 'interest_id' => Interest::where('name', $interest)->value('id')], []);
        }
    }
}
