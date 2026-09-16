<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Modules\Identity\Models\User;
use App\Modules\Identity\Models\Role;
use App\Modules\People\Models\Person;
use App\Modules\SmallGroups\Models\SmallGroup;
use App\Modules\SmallGroups\Models\GroupMembership;
use App\Modules\SmallGroups\Models\GroupLeadership;
use App\Modules\SmallGroups\Models\WeeklyCycle;
use App\Modules\SmallGroups\Models\GpMeeting;
use App\Modules\SmallGroups\Models\GpAttendance;
use App\Modules\SmallGroups\Actions\RecalculateAbsences;
use App\Modules\YouthMinistry\Models\JaActivity;
use App\Modules\YouthMinistry\Models\Opportunity;
use App\Modules\YouthMinistry\Models\ActivityGroupAssignment;
use App\Modules\YouthMinistry\Models\ActivityResult;
use App\Modules\Participation\Models\JaAttendance;
use App\Modules\Participation\Models\Participation;
use App\Modules\Participation\Models\Commitment;
use App\Modules\Surveys\Models\Survey;
use App\Modules\Service\Models\ServiceNeed;
use Carbon\CarbonImmutable;
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        if (!app()->environment('local','testing')) { throw new \RuntimeException('DemoSeeder solo se permite en local o testing.'); }
        $this->call(CatalogSeeder::class);if (Person::exists()) { return; }
        DB::transaction(function (): void {
            $names=['Ana Torres','Carlos Mendoza','Ruth Castillo','Daniel Flores','Andrea Rojas','Pedro Vásquez','Lucía Herrera','Mateo Salazar','Valeria Díaz','José Ramírez','Sofía Campos','Samuel Cruz','David Paredes','Mariana León','Gabriel Ruiz','Elena Vargas','Luis Romero','Camila Santos','Diego Vega','Isabel Núñez','Javier Silva','Paola Medina','Ricardo López','Natalia Reyes','Esteban Ortiz','Victoria Ramos','Andrés Chávez','Fernanda Ríos','Miguel Molina','Daniela Castro','Sebastián Peña','Carolina Soto','Pablo Aguirre','Sara Fuentes','Nicolás Rivas','Adriana Mora','Jorge Espinoza','Claudia Acosta','Felipe Navarro','Teresa Cárdenas','Marco Benítez','Raquel Valdez','Ángel Arias','Patricia Solís','Emilio Torres','Lorena Bravo','Hugo Delgado','Alicia Fernández'];
            $people=collect();foreach ($names as $i=>$name) { $p=Person::create(['full_name'=>$name,'phone'=>$i%4===0?null:'5190000'.str_pad((string)$i,4,'0',STR_PAD_LEFT),'email'=>$i%3===0?'persona'.$i.'@example.test':null,'status'=>'active']);DB::table('person_talents')->insert(['person_id'=>$p->id,'talent_id'=>($i%10)+1,'kind'=>'possesses']);$p->interests()->attach(($i%8)+1);$p->availability()->attach(2);$people->push($p); }
            $admin=User::create(['person_id'=>$people[0]->id,'username'=>'admin','password'=>'Conecta.Demo2026!','must_change_password'=>false]);$admin->roles()->attach(Role::whereIn('code',['admin','gp_leader','member'])->pluck('id'));
            $ja=User::create(['person_id'=>$people[1]->id,'username'=>'director','password'=>'Conecta.Demo2026!','must_change_password'=>false]);$ja->roles()->attach(Role::whereIn('code',['ja_director','member'])->pluck('id'));
            $member=User::create(['person_id'=>$people[2]->id,'username'=>'miembro','password'=>'Conecta.Demo2026!','must_change_password'=>false]);$member->roles()->attach(Role::where('code','member')->value('id'));
            $leader=User::create(['person_id'=>$people[12]->id,'username'=>'lider','password'=>'Conecta.Demo2026!','must_change_password'=>false]);$leader->roles()->attach(Role::whereIn('code',['gp_leader','member'])->pluck('id'));
            $groups=collect();foreach (['Maranata','Betania','Nueva Esperanza','Ebenezer'] as $i=>$name) { $g=SmallGroup::create(['name'=>$name,'description'=>['Un espacio para encontrarnos y crecer en amistad.','Compartimos la vida, descubrimos nuestros talentos.','Nuevos comienzos, una misma comunidad.','Juntos aprendemos a servir con alegría.'][$i],'meeting_place'=>['Casa de la familia Torres','Salón Betania','Casa de la familia Ramos','Salón de jóvenes'][$i],'usual_weekday'=>5,'usual_time'=>'19:00','color'=>['sage','sand','lavender','rose'][$i]]);$groups->push($g);foreach ($people->slice($i*12,12) as $p) { GroupMembership::create(['person_id'=>$p->id,'group_id'=>$g->id,'starts_at'=>now()->subMonths(5),'assigned_by'=>$admin->id]); } }
            GroupLeadership::create(['group_id'=>$groups[0]->id,'user_id'=>$admin->id,'starts_at'=>now()->subMonths(5),'assigned_by'=>$admin->id]);GroupLeadership::create(['group_id'=>$groups[1]->id,'user_id'=>$leader->id,'starts_at'=>now()->subMonths(5),'assigned_by'=>$admin->id]);
            $last=CarbonImmutable::now('America/Lima')->previous('Saturday')->setTime(16,0);
            for ($week=5;$week>=0;$week--) {
                $saturday=$last->subWeeks($week);$cycle=WeeklyCycle::create(['saturday_date'=>$saturday->toDateString()]);$present=collect();
                foreach ($groups as $g) { $meeting=GpMeeting::create(['group_id'=>$g->id,'cycle_id'=>$cycle->id,'starts_at'=>$saturday->subDay()->setTime(19,0)->utc(),'status'=>'closed','closed_at'=>$saturday->subDay()->setTime(21,0)->utc(),'closed_by'=>$admin->id]);foreach ($g->memberships as $j=>$membership) { $here=($j<9+($week%2)) && !($g->id===$groups[0]->id && in_array($j,[7,8]) && $week<3);GpAttendance::create(['meeting_id'=>$meeting->id,'person_id'=>$membership->person_id,'membership_id'=>$membership->id,'status'=>$here?'present':'absent','recorded_by'=>$admin->id]);if ($here) { $present->push($membership); } } }
                $activity=JaActivity::create(['cycle_id'=>$cycle->id,'activity_type_id'=>3,'title'=>['Un encuentro para compartir','Juntos en comunidad','Pequeñas acciones, gran impacto','Tarde de talentos','Conexiones que crecen','Servir con alegría'][$week],'description'=>'Compartimos nuestros talentos y fortalecemos los lazos de nuestra comunidad.','place'=>'Salón de jóvenes','starts_at'=>$saturday->utc(),'ends_at'=>$saturday->addHours(2)->utc(),'is_primary'=>true,'status'=>'completed','created_by'=>$admin->id]);ActivityGroupAssignment::create(['activity_id'=>$activity->id,'group_id'=>$groups[$week%4]->id,'assigned_at'=>$saturday->subWeek()->utc(),'assigned_by'=>$admin->id]);
                $op=Opportunity::create(['activity_id'=>$activity->id,'responsibility_id'=>3,'title'=>'Equipo de bienvenida','capacity'=>12,'starts_at'=>$activity->starts_at,'ends_at'=>$activity->ends_at]);
                foreach ($present->take(22+(5-$week)*2) as $i=>$m) { $row=JaAttendance::create(['activity_id'=>$activity->id,'person_id'=>$m->person_id,'membership_id'=>$m->id,'status'=>'present','recorded_by'=>$admin->id]);if ($i<10) { Participation::create(['ja_attendance_id'=>$row->id,'opportunity_id'=>$op->id,'confirmed_by'=>$admin->id,'confirmed_at'=>$saturday->addHours(2)->utc()]); } }
                ActivityResult::create(['activity_id'=>$activity->id,'summary'=>'Una tarde de encuentro, música y servicio. Los equipos compartieron sus talentos y recibieron a nuevos amigos.','beneficiary_count'=>15,'closed_by'=>$admin->id,'closed_at'=>$saturday->addHours(2)->utc()]);
            }
            $next=CarbonImmutable::now('America/Lima')->next('Saturday')->setTime(16,30);
            foreach (['Talentos que transforman','Una mesa, muchas historias','Manos que acompañan'] as $i=>$title) {
                $date=$next->addWeeks($i);$cycle=WeeklyCycle::create(['saturday_date'=>$date->toDateString()]);$a=JaActivity::create(['cycle_id'=>$cycle->id,'activity_type_id'=>[3,2,6][$i],'title'=>$title,'description'=>['Cada persona tiene algo único para compartir. Un encuentro para descubrir nuestros talentos, conectar con nuevos amigos y poner nuestras habilidades al servicio de los demás.','Una tarde para conocernos mejor, compartir alimentos y construir nuevas amistades.','Llevemos compañía y ayuda práctica a las personas de nuestra comunidad.'][$i],'place'=>['Salón de jóvenes · Iglesia central','Jardín de la iglesia','Punto de encuentro · Iglesia central'][$i],'starts_at'=>$date->utc(),'ends_at'=>$date->addHours(2)->utc(),'is_primary'=>true,'status'=>'published','created_by'=>$admin->id]);ActivityGroupAssignment::create(['activity_id'=>$a->id,'group_id'=>$groups[$i]->id,'assigned_at'=>now(),'assigned_by'=>$admin->id]);
                foreach (['Fotografía','Recepción','Multimedia','Refrigerio'] as $j=>$title) { $op=Opportunity::create(['activity_id'=>$a->id,'responsibility_id'=>[1,3,4,7][$j],'title'=>$title,'description'=>['Captura los momentos que nos unen.','Haz que cada persona se sienta bienvenida.','Ayuda a que las ideas cobren vida.','Comparte hospitalidad con un detalle especial.'][$j],'capacity'=>[2,3,2,2][$j],'talent_id'=>[1,5,4,3][$j],'coordinator_person_id'=>$people[$j]->id,'starts_at'=>$a->starts_at,'ends_at'=>$a->ends_at]);if ($j<3) { Commitment::create(['opportunity_id'=>$op->id,'person_id'=>$people[$j+3]->id,'status'=>'confirmed','accepted_at'=>now(),'acceptance_source'=>'verbal','recorded_by'=>$admin->id]); } }
            }
            $survey=Survey::create(['title'=>'¿Qué te gustaría compartir este mes?','description'=>'Tu opinión nos ayuda a crear actividades con sentido. Puedes elegir más de una opción.','opens_at'=>now()->subDays(2),'closes_at'=>now()->addDays(10),'created_by'=>$admin->id]);foreach (['Caminata en grupo','Taller de fotografía','Servicio comunitario','Tarde de cocina'] as $i=>$label) { $survey->options()->create(['label'=>$label,'position'=>$i]); }
            ServiceNeed::create(['description'=>'Acompañamiento digital para adultos mayores: aprender a hacer videollamadas y comunicarse con su familia.','reported_at'=>now()->subDay(),'responsible_person_id'=>$people[1]->id,'created_by'=>$admin->id]);
            ServiceNeed::create(['description'=>'Jornada de recuperación del jardín comunitario y cuidado de sus áreas verdes.','reported_at'=>now()->subDays(3),'responsible_person_id'=>$people[5]->id,'created_by'=>$admin->id]);
            foreach ($groups as $g) { app(RecalculateAbsences::class)->handle($g->id); }
        });
    }
}
