<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('user_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('role_id')->constrained('roles')->restrictOnDelete();
            $table->unique(['user_id', 'role_id']);
            $table->timestamps();
        });

        Schema::create('talents', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(1);
            $table->timestamps();
        });

        Schema::create('interests', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('is_active')->default(1);
            $table->timestamps();
        });

        Schema::create('person_talents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('person_id')->constrained('people')->restrictOnDelete();
            $table->foreignId('talent_id')->constrained('talents')->restrictOnDelete();
            $table->string('kind')->default('possesses');
            $table->unique(['person_id', 'talent_id']);
            $table->timestamps();
        });

        Schema::create('person_interests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('person_id')->constrained('people')->restrictOnDelete();
            $table->foreignId('interest_id')->constrained('interests')->restrictOnDelete();
            $table->unique(['person_id', 'interest_id']);
            $table->timestamps();
        });

        Schema::create('availability_slots', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedInteger('weekday');
            $table->time('starts_at');
            $table->time('ends_at');
            $table->boolean('is_active')->default(1);
            $table->timestamps();
        });

        Schema::create('person_availability', function (Blueprint $table) {
            $table->id();
            $table->foreignId('person_id')->constrained('people')->restrictOnDelete();
            $table->foreignId('availability_slot_id')->constrained('availability_slots')->restrictOnDelete();
            $table->unique(['person_id', 'availability_slot_id']);
            $table->timestamps();
        });

        Schema::create('small_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->string('meeting_place')->nullable();
            $table->unsignedInteger('usual_weekday')->default(5);
            $table->time('usual_time');
            $table->string('status')->default('active');
            $table->string('color')->default('sage');
            $table->timestamps();
        });

        Schema::create('group_leaderships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('small_groups')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at')->nullable();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->unsignedBigInteger('active_group_id')->nullable()->virtualAs('CASE WHEN ends_at IS NULL THEN group_id ELSE NULL END')->unique();
            $table->unsignedBigInteger('active_user_id')->nullable()->virtualAs('CASE WHEN ends_at IS NULL THEN user_id ELSE NULL END')->unique();
            $table->timestamps();
        });

        Schema::create('group_memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('small_groups')->restrictOnDelete();
            $table->foreignId('person_id')->constrained('people')->restrictOnDelete();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at')->nullable();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->unsignedBigInteger('active_person_id')->nullable()->virtualAs('CASE WHEN ends_at IS NULL THEN person_id ELSE NULL END')->unique();
            $table->timestamps();
        });

        Schema::create('weekly_cycles', function (Blueprint $table) {
            $table->id();
            $table->date('saturday_date')->unique();
            $table->timestamps();
        });

        Schema::create('gp_meetings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('small_groups')->restrictOnDelete();
            $table->foreignId('cycle_id')->constrained('weekly_cycles')->restrictOnDelete();
            $table->dateTime('starts_at');
            $table->string('status')->default('open');
            $table->text('next_activity_note')->nullable();
            $table->dateTime('closed_at')->nullable();
            $table->foreignId('closed_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->unique(['group_id', 'starts_at']);
            $table->index(['group_id', 'status']);
            $table->timestamps();
        });

        Schema::create('gp_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meeting_id')->constrained('gp_meetings')->restrictOnDelete();
            $table->foreignId('person_id')->constrained('people')->restrictOnDelete();
            $table->foreignId('membership_id')->nullable()->constrained('group_memberships')->restrictOnDelete();
            $table->string('status')->default('unrecorded');
            $table->foreignId('recorded_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->unique(['meeting_id', 'person_id']);
            $table->timestamps();
        });

        Schema::create('prayer_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meeting_id')->constrained('gp_meetings')->restrictOnDelete();
            $table->foreignId('person_id')->nullable()->constrained('people')->restrictOnDelete();
            $table->text('encrypted_content');
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });

        Schema::create('absence_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('membership_id')->constrained('group_memberships')->restrictOnDelete();
            $table->foreignId('first_missed_meeting_id')->constrained('gp_meetings')->restrictOnDelete();
            $table->foreignId('last_missed_meeting_id')->constrained('gp_meetings')->restrictOnDelete();
            $table->unsignedInteger('consecutive_count');
            $table->string('status')->default('open');
            $table->unique(['membership_id', 'first_missed_meeting_id']);
            $table->timestamps();
        });

        Schema::create('absence_follow_ups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alert_id')->constrained('absence_alerts')->restrictOnDelete();
            $table->string('action');
            $table->foreignId('performed_by')->constrained('users')->restrictOnDelete();
            $table->dateTime('performed_at');
            $table->timestamps();
        });

        Schema::create('activity_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('is_active')->default(1);
            $table->timestamps();
        });

        Schema::create('activity_type_interests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_type_id')->constrained('activity_types')->restrictOnDelete();
            $table->foreignId('interest_id')->constrained('interests')->restrictOnDelete();
            $table->unique(['activity_type_id', 'interest_id']);
            $table->timestamps();
        });

        Schema::create('responsibilities', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(1);
            $table->timestamps();
        });

        Schema::create('ja_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cycle_id')->nullable()->constrained('weekly_cycles')->restrictOnDelete();
            $table->foreignId('activity_type_id')->constrained('activity_types')->restrictOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('place')->nullable();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->boolean('is_primary')->default(0);
            $table->string('status')->default('draft');
            $table->dateTime('archived_at')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->index(['starts_at', 'status']);
            $table->timestamps();
        });

        Schema::create('activity_group_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_id')->constrained('ja_activities')->restrictOnDelete();
            $table->foreignId('group_id')->constrained('small_groups')->restrictOnDelete();
            $table->dateTime('assigned_at');
            $table->dateTime('ended_at')->nullable();
            $table->foreignId('assigned_by')->constrained('users')->restrictOnDelete();
            $table->unsignedBigInteger('active_activity_id')->nullable()->virtualAs('CASE WHEN ended_at IS NULL THEN activity_id ELSE NULL END')->unique();
            $table->timestamps();
        });

        Schema::create('opportunities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_id')->constrained('ja_activities')->restrictOnDelete();
            $table->foreignId('responsibility_id')->constrained('responsibilities')->restrictOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedInteger('capacity');
            $table->foreignId('talent_id')->nullable()->constrained('talents')->restrictOnDelete();
            $table->foreignId('coordinator_person_id')->nullable()->constrained('people')->restrictOnDelete();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->dateTime('closed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('commitments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('opportunity_id')->constrained('opportunities')->restrictOnDelete();
            $table->foreignId('person_id')->constrained('people')->restrictOnDelete();
            $table->string('status')->default('confirmed');
            $table->dateTime('accepted_at');
            $table->string('acceptance_source');
            $table->foreignId('recorded_by')->constrained('users')->restrictOnDelete();
            $table->dateTime('cancelled_at')->nullable();
            $table->unique(['opportunity_id', 'person_id']);
            $table->index(['person_id', 'status']);
            $table->timestamps();
        });

        Schema::create('ja_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_id')->constrained('ja_activities')->restrictOnDelete();
            $table->foreignId('person_id')->constrained('people')->restrictOnDelete();
            $table->foreignId('membership_id')->nullable()->constrained('group_memberships')->restrictOnDelete();
            $table->string('status')->default('present');
            $table->foreignId('recorded_by')->constrained('users')->restrictOnDelete();
            $table->unique(['activity_id', 'person_id']);
            $table->timestamps();
        });

        Schema::create('participations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ja_attendance_id')->constrained('ja_attendances')->restrictOnDelete();
            $table->foreignId('opportunity_id')->constrained('opportunities')->restrictOnDelete();
            $table->foreignId('commitment_id')->nullable()->constrained('commitments')->restrictOnDelete();
            $table->foreignId('confirmed_by')->constrained('users')->restrictOnDelete();
            $table->dateTime('confirmed_at');
            $table->dateTime('revoked_at')->nullable();
            $table->unique(['ja_attendance_id', 'opportunity_id']);
            $table->timestamps();
        });

        Schema::create('participation_talents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('participation_id')->constrained('participations')->restrictOnDelete();
            $table->foreignId('talent_id')->constrained('talents')->restrictOnDelete();
            $table->unique(['participation_id', 'talent_id']);
            $table->timestamps();
        });

        Schema::create('activity_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_id')->constrained('ja_activities')->restrictOnDelete()->unique();
            $table->text('summary');
            $table->unsignedInteger('beneficiary_count')->nullable();
            $table->text('final_note')->nullable();
            $table->foreignId('closed_by')->constrained('users')->restrictOnDelete();
            $table->dateTime('closed_at');
            $table->timestamps();
        });

        Schema::create('activity_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_result_id')->constrained('activity_results')->restrictOnDelete();
            $table->string('storage_path');
            $table->string('mime_type');
            $table->unsignedInteger('size_bytes');
            $table->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });

        Schema::create('surveys', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('opens_at');
            $table->dateTime('closes_at');
            $table->string('status')->default('open');
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });

        Schema::create('survey_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained('surveys')->restrictOnDelete();
            $table->string('label');
            $table->foreignId('interest_id')->nullable()->constrained('interests')->restrictOnDelete();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });

        Schema::create('survey_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained('surveys')->restrictOnDelete();
            $table->foreignId('person_id')->constrained('people')->restrictOnDelete();
            $table->foreignId('membership_id')->nullable()->constrained('group_memberships')->restrictOnDelete();
            $table->dateTime('submitted_at');
            $table->unique(['survey_id', 'person_id']);
            $table->timestamps();
        });

        Schema::create('survey_response_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('response_id')->constrained('survey_responses')->restrictOnDelete();
            $table->foreignId('option_id')->constrained('survey_options')->restrictOnDelete();
            $table->unique(['response_id', 'option_id']);
            $table->timestamps();
        });

        Schema::create('service_needs', function (Blueprint $table) {
            $table->id();
            $table->text('description');
            $table->dateTime('reported_at');
            $table->foreignId('responsible_person_id')->constrained('people')->restrictOnDelete();
            $table->string('status')->default('open');
            $table->foreignId('activity_id')->nullable()->constrained('ja_activities')->restrictOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });

        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->json('value');
            $table->foreignId('updated_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->string('action');
            $table->string('entity_type');
            $table->unsignedInteger('entity_id');
            $table->json('changes');
            $table->dateTime('occurred_at');
            $table->string('request_id')->nullable();
            $table->index(['entity_type', 'entity_id']);
            $table->timestamps();
        });

        Schema::table('ja_activities', function (Blueprint $table) {
            $table->unsignedBigInteger('primary_cycle_id')->nullable()->virtualAs("CASE WHEN is_primary = 1 AND status <> 'cancelled' THEN cycle_id ELSE NULL END")->unique();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('system_settings');
        Schema::dropIfExists('service_needs');
        Schema::dropIfExists('survey_response_options');
        Schema::dropIfExists('survey_responses');
        Schema::dropIfExists('survey_options');
        Schema::dropIfExists('surveys');
        Schema::dropIfExists('activity_photos');
        Schema::dropIfExists('activity_results');
        Schema::dropIfExists('participation_talents');
        Schema::dropIfExists('participations');
        Schema::dropIfExists('ja_attendances');
        Schema::dropIfExists('commitments');
        Schema::dropIfExists('opportunities');
        Schema::dropIfExists('activity_group_assignments');
        Schema::dropIfExists('ja_activities');
        Schema::dropIfExists('responsibilities');
        Schema::dropIfExists('activity_type_interests');
        Schema::dropIfExists('activity_types');
        Schema::dropIfExists('absence_follow_ups');
        Schema::dropIfExists('absence_alerts');
        Schema::dropIfExists('prayer_requests');
        Schema::dropIfExists('gp_attendances');
        Schema::dropIfExists('gp_meetings');
        Schema::dropIfExists('weekly_cycles');
        Schema::dropIfExists('group_memberships');
        Schema::dropIfExists('group_leaderships');
        Schema::dropIfExists('small_groups');
        Schema::dropIfExists('person_availability');
        Schema::dropIfExists('availability_slots');
        Schema::dropIfExists('person_interests');
        Schema::dropIfExists('person_talents');
        Schema::dropIfExists('interests');
        Schema::dropIfExists('talents');
        Schema::dropIfExists('user_roles');
        Schema::dropIfExists('roles');
    }
};
