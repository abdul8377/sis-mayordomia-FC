<?php
namespace App\Modules\SmallGroups\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class SaveMeetingRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()->hasRole('admin','gp_leader'); }
    public function rules(): array { return ['group_id'=>['required','integer','exists:small_groups,id'],'starts_at'=>['required','date'],'next_activity_note'=>['nullable','string','max:1000'],'attendances'=>['sometimes','array'],'attendances.*.person_id'=>['required','integer','distinct','exists:people,id'],'attendances.*.status'=>['required','in:present,absent,unrecorded'],'close'=>['sometimes','boolean']]; }
}
