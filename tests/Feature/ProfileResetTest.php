<?php

namespace Tests\Feature;

use App\Models\ProfilingQuestion;
use App\Models\User;
use App\Models\UserProfileAnswer;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProfileResetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        ProfilingQuestion::create([
            'code' => 'gender',
            'text' => 'What is your gender?',
            'type' => 'single',
            'options' => ['Male', 'Female'],
            'is_active' => true,
        ]);
    }

    public function test_can_reset_daily_profile_limitation_for_testing(): void
    {
        $user = User::factory()->create();
        Wallet::create(['user_id' => $user->id, 'balance_usd' => 0.00]);
        Sanctum::actingAs($user);

        $question = ProfilingQuestion::first();
        
        UserProfileAnswer::create([
            'user_id' => $user->id,
            'question_id' => $question->id,
            'answer' => 'Male',
            'updated_at' => Carbon::today(),
        ]);

        $response = $this->postJson('/api/profile', [
            'answers' => [$question->id => 'Female'],
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Profile can only be updated once per day',
            ]);

        UserProfileAnswer::where('user_id', $user->id)
            ->update(['updated_at' => Carbon::yesterday()]);

        $response = $this->postJson('/api/profile', [
            'answers' => [$question->id => 'Female'],
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Profile updated successfully',
            ]);
    }

    public function test_can_clear_all_profile_answers_for_testing(): void
    {
        $user = User::factory()->create();
        Wallet::create(['user_id' => $user->id, 'balance_usd' => 0.00]);
        Sanctum::actingAs($user);

        $question = ProfilingQuestion::first();
        
        UserProfileAnswer::create([
            'user_id' => $user->id,
            'question_id' => $question->id,
            'answer' => 'Male',
            'updated_at' => Carbon::today(),
        ]);

        $this->assertDatabaseHas('user_profile_answers', [
            'user_id' => $user->id,
            'question_id' => $question->id,
        ]);

        UserProfileAnswer::where('user_id', $user->id)->delete();

        $this->assertDatabaseMissing('user_profile_answers', [
            'user_id' => $user->id,
            'question_id' => $question->id,
        ]);

        $response = $this->postJson('/api/profile', [
            'answers' => [$question->id => 'Female'],
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Profile updated successfully',
            ]);
    }
}
