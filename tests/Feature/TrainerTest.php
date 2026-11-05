<?php

namespace Tests\Feature;

use App\Models\Trainer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrainerTest extends TestCase
{
    use RefreshDatabase;

    public function test_trainer_resource_pages_render(): void
    {
        $user = User::factory()->create();
        $trainer = Trainer::create([
            'name' => 'Coach One',
            'email' => 'coach@example.com',
            'specialty' => 'Strength',
        ]);

        $this->actingAs($user)
            ->get(route('trainers.index'))
            ->assertOk();
        $this->get(route('trainers.create'))->assertOk();
        $this->get(route('trainers.show', $trainer))->assertOk();
        $this->get(route('trainers.edit', $trainer))->assertOk();
    }

    public function test_trainer_can_be_created_updated_and_soft_deleted(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('trainers.store'), [
            'name' => 'Coach One',
            'email' => 'coach@example.com',
            'specialty' => 'Strength',
            'lead_time_days' => 3,
            'is_active' => 1,
        ])->assertRedirect(route('trainers.index'));

        $trainer = Trainer::query()->where('email', 'coach@example.com')->firstOrFail();
        $this->assertTrue($trainer->is_active);

        $this->put(route('trainers.update', $trainer), [
            'name' => 'Coach Two',
            'email' => 'coach@example.com',
            'specialty' => 'Cardio',
            'lead_time_days' => 4,
            'is_active' => 0,
        ])->assertRedirect(route('trainers.index'));

        $this->assertDatabaseHas('trainers', [
            'id' => $trainer->id,
            'name' => 'Coach Two',
            'is_active' => 0,
        ]);

        $this->delete(route('trainers.destroy', $trainer))
            ->assertRedirect(route('trainers.index'));
        $this->assertSoftDeleted('trainers', ['id' => $trainer->id]);
    }
}
