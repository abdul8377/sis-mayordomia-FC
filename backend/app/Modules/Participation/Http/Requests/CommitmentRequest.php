<?php
namespace App\Modules\Participation\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class CommitmentRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array { return ['person_id'=>['required','integer','exists:people,id'],'acceptance_confirmed'=>['required','accepted']]; }
}
