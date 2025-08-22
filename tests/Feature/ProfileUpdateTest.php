<?php

namespace Tests\Feature;

use App\Models\PointsTransaction;
use App\Models\ProfilingQuestion;
use App\Models\User;
use App\Models\UserProfileAnswer;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProfileUpdateTest extends TestCase
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

        ProfilingQuestion::create([
            'code' => 'dob',
            'text' => 'What is your date of birth?',
            'type' => 'date',
            'options' => null,
            'is_active' => true,
        ]);

        ProfilingQuestion::create([
            'code' => 'interests',
            'text' => 'What are your interests? (Select all that apply)',
            'type' => 'multiple',
            'options' => ['Sports', 'Music', 'Technology', 'Travel', 'Reading'],
            'is_active' => true,
        ]);

        ProfilingQuestion::create([
            'code' => 'skills',
            'text' => 'What programming languages do you know?',
            'type' => 'multiple',
            'options' => ['JavaScript', 'Python', 'PHP', 'Java', 'Go'],
            'is_active' => true,
        ]);

        ProfilingQuestion::create([
            'code' => 'education',
            'text' => 'What is your highest level of education?',
            'type' => 'single',
            'options' => ['High School', 'Bachelor\'s Degree', 'Master\'s Degree', 'PhD'],
            'is_active' => true,
        ]);
    }

    public function test_user_can_update_profile_successfully(): void
    {
        $user = User::factory()->create();
        Wallet::create(['user_id' => $user->id, 'balance_usd' => 0.00]);
        Sanctum::actingAs($user);

        $questions = ProfilingQuestion::all();
        $answers = [
            $questions[0]->id => 'Male',
            $questions[1]->id => '1990-01-01',
        ];

        $response = $this->postJson('/api/profile', [
            'answers' => $answers,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Profile updated successfully',
                'points_earned' => 5,
            ]);

        $this->assertDatabaseHas('user_profile_answers', [
            'user_id' => $user->id,
            'question_id' => $questions[0]->id,
            'answer' => 'Male',
        ]);

        $this->assertDatabaseHas('user_profile_answers', [
            'user_id' => $user->id,
            'question_id' => $questions[1]->id,
            'answer' => '1990-01-01',
        ]);

        $this->assertDatabaseHas('points_transactions', [
            'user_id' => $user->id,
            'points' => 5,
            'reason' => 'Profile update',
            'is_claimed' => false,
        ]);
    }

    public function test_user_cannot_update_profile_twice_in_same_day(): void
    {
        $user = User::factory()->create();
        Wallet::create(['user_id' => $user->id, 'balance_usd' => 0.00]);
        Sanctum::actingAs($user);

        $question = ProfilingQuestion::first();
        
        $answer = UserProfileAnswer::create([
            'user_id' => $user->id,
            'question_id' => $question->id,
            'answer' => 'Male',
        ]);
        
        $answer->timestamps = false;
        $answer->updated_at = Carbon::today();
        $answer->save();

        $response = $this->postJson('/api/profile', [
            'answers' => [$question->id => 'Female'],
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Profile can only be updated once per day',
            ]);
    }

    public function test_profile_update_requires_authentication(): void
    {
        $question = ProfilingQuestion::first();

        $response = $this->postJson('/api/profile', [
            'answers' => [$question->id => 'Male'],
        ]);

        $response->assertStatus(401);
    }

    public function test_profile_update_requires_valid_answers(): void
    {
        $user = User::factory()->create();
        Wallet::create(['user_id' => $user->id, 'balance_usd' => 0.00]);
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/profile', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['answers']);
    }

    public function test_user_can_update_existing_answers(): void
    {
        $user = User::factory()->create();
        Wallet::create(['user_id' => $user->id, 'balance_usd' => 0.00]);
        Sanctum::actingAs($user);

        $question = ProfilingQuestion::first();
        
        $answer = UserProfileAnswer::create([
            'user_id' => $user->id,
            'question_id' => $question->id,
            'answer' => 'Male',
        ]);
        
        $answer->timestamps = false;
        $answer->updated_at = Carbon::yesterday();
        $answer->created_at = Carbon::yesterday();
        $answer->save();

        $response = $this->postJson('/api/profile', [
            'answers' => [$question->id => 'Female'],
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('user_profile_answers', [
            'user_id' => $user->id,
            'question_id' => $question->id,
            'answer' => 'Female',
        ]);

        $this->assertEquals(1, UserProfileAnswer::where('user_id', $user->id)
            ->where('question_id', $question->id)
            ->count());
    }

    public function test_user_can_update_profile_with_multiselect_answers(): void
    {
        $user = User::factory()->create();
        Wallet::create(['user_id' => $user->id, 'balance_usd' => 0.00]);
        Sanctum::actingAs($user);

        $questions = ProfilingQuestion::all();
        $genderQuestion = $questions->where('code', 'gender')->first();
        $interestsQuestion = $questions->where('code', 'interests')->first();
        $skillsQuestion = $questions->where('code', 'skills')->first();

        $answers = [
            $genderQuestion->id => 'Female',
            $interestsQuestion->id => ['Technology', 'Reading', 'Music'],
            $skillsQuestion->id => ['JavaScript', 'Python'],
        ];

        $response = $this->postJson('/api/profile', [
            'answers' => $answers,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Profile updated successfully',
                'points_earned' => 5,
            ]);

        $this->assertDatabaseHas('user_profile_answers', [
            'user_id' => $user->id,
            'question_id' => $genderQuestion->id,
            'answer' => 'Female',
        ]);

        // Check multiselect is stored as JSON
        $this->assertDatabaseHas('user_profile_answers', [
            'user_id' => $user->id,
            'question_id' => $interestsQuestion->id,
            'answer' => json_encode(['Technology', 'Reading', 'Music']),
        ]);

        $this->assertDatabaseHas('user_profile_answers', [
            'user_id' => $user->id,
            'question_id' => $skillsQuestion->id,
            'answer' => json_encode(['JavaScript', 'Python']),
        ]);
    }

    public function test_multiselect_answer_must_be_array(): void
    {
        $user = User::factory()->create();
        Wallet::create(['user_id' => $user->id, 'balance_usd' => 0.00]);
        Sanctum::actingAs($user);

        $interestsQuestion = ProfilingQuestion::where('code', 'interests')->first();

        $response = $this->postJson('/api/profile', [
            'answers' => [
                $interestsQuestion->id => 'Technology', // Should be array, not string
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(["answers.{$interestsQuestion->id}"]);
    }

    public function test_multiselect_answer_cannot_be_empty(): void
    {
        $user = User::factory()->create();
        Wallet::create(['user_id' => $user->id, 'balance_usd' => 0.00]);
        Sanctum::actingAs($user);

        $interestsQuestion = ProfilingQuestion::where('code', 'interests')->first();

        $response = $this->postJson('/api/profile', [
            'answers' => [
                $interestsQuestion->id => [], // Empty array not allowed
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(["answers.{$interestsQuestion->id}"]);
    }

    public function test_multiselect_validates_option_values(): void
    {
        $user = User::factory()->create();
        Wallet::create(['user_id' => $user->id, 'balance_usd' => 0.00]);
        Sanctum::actingAs($user);

        $interestsQuestion = ProfilingQuestion::where('code', 'interests')->first();

        $response = $this->postJson('/api/profile', [
            'answers' => [
                $interestsQuestion->id => ['Technology', 'InvalidOption'], // InvalidOption not in options
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(["answers.{$interestsQuestion->id}"]);
    }

    public function test_single_choice_validates_option_values(): void
    {
        $user = User::factory()->create();
        Wallet::create(['user_id' => $user->id, 'balance_usd' => 0.00]);
        Sanctum::actingAs($user);

        $genderQuestion = ProfilingQuestion::where('code', 'gender')->first();

        $response = $this->postJson('/api/profile', [
            'answers' => [
                $genderQuestion->id => 'InvalidGender', // Not in options
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(["answers.{$genderQuestion->id}"]);
    }

    public function test_date_answer_validates_format(): void
    {
        $user = User::factory()->create();
        Wallet::create(['user_id' => $user->id, 'balance_usd' => 0.00]);
        Sanctum::actingAs($user);

        $dobQuestion = ProfilingQuestion::where('code', 'dob')->first();

        $response = $this->postJson('/api/profile', [
            'answers' => [
                $dobQuestion->id => 'invalid-date',
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(["answers.{$dobQuestion->id}"]);
    }

    public function test_get_profile_returns_multiselect_as_array(): void
    {
        $user = User::factory()->create();
        Wallet::create(['user_id' => $user->id, 'balance_usd' => 0.00]);
        Sanctum::actingAs($user);

        $interestsQuestion = ProfilingQuestion::where('code', 'interests')->first();

        // Create answer with multiselect data
        UserProfileAnswer::create([
            'user_id' => $user->id,
            'question_id' => $interestsQuestion->id,
            'answer' => json_encode(['Technology', 'Music']),
        ]);

        $response = $this->getJson('/api/profile');

        $response->assertStatus(200);
        
        $profileAnswers = $response->json('profile_answers');
        $this->assertArrayHasKey('interests', $profileAnswers);
        $this->assertEquals(['Technology', 'Music'], $profileAnswers['interests']['answer']);
        $this->assertEquals('multiple', $profileAnswers['interests']['question_type']);
    }

    public function test_profile_update_with_mixed_question_types(): void
    {
        $user = User::factory()->create();
        Wallet::create(['user_id' => $user->id, 'balance_usd' => 0.00]);
        Sanctum::actingAs($user);

        $questions = ProfilingQuestion::all();
        $genderQuestion = $questions->where('code', 'gender')->first();
        $dobQuestion = $questions->where('code', 'dob')->first();
        $interestsQuestion = $questions->where('code', 'interests')->first();
        $skillsQuestion = $questions->where('code', 'skills')->first();
        $educationQuestion = $questions->where('code', 'education')->first();

        $answers = [
            $genderQuestion->id => 'Male',                                      // single
            $dobQuestion->id => '1995-03-20',                                  // date
            $interestsQuestion->id => ['Technology', 'Sports', 'Reading'],     // multiple
            $skillsQuestion->id => ['JavaScript', 'Python', 'PHP'],           // multiple
            $educationQuestion->id => 'Master\'s Degree',                     // single
        ];

        $response = $this->postJson('/api/profile', [
            'answers' => $answers,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Profile updated successfully',
                'points_earned' => 5,
            ]);

        $this->assertDatabaseHas('user_profile_answers', [
            'user_id' => $user->id,
            'question_id' => $genderQuestion->id,
            'answer' => 'Male',
        ]);

        $this->assertDatabaseHas('user_profile_answers', [
            'user_id' => $user->id,
            'question_id' => $dobQuestion->id,
            'answer' => '1995-03-20',
        ]);

        $this->assertDatabaseHas('user_profile_answers', [
            'user_id' => $user->id,
            'question_id' => $interestsQuestion->id,
            'answer' => json_encode(['Technology', 'Sports', 'Reading']),
        ]);

        $this->assertDatabaseHas('user_profile_answers', [
            'user_id' => $user->id,
            'question_id' => $skillsQuestion->id,
            'answer' => json_encode(['JavaScript', 'Python', 'PHP']),
        ]);

        $this->assertDatabaseHas('user_profile_answers', [
            'user_id' => $user->id,
            'question_id' => $educationQuestion->id,
            'answer' => 'Master\'s Degree',
        ]);

        $this->assertDatabaseHas('points_transactions', [
            'user_id' => $user->id,
            'points' => 5,
            'reason' => 'Profile update',
            'is_claimed' => false,
        ]);
    }

    public function test_inactive_questions_are_rejected(): void
    {
        $user = User::factory()->create();
        Wallet::create(['user_id' => $user->id, 'balance_usd' => 0.00]);
        Sanctum::actingAs($user);

        $inactiveQuestion = ProfilingQuestion::create([
            'code' => 'inactive',
            'text' => 'Inactive question',
            'type' => 'single',
            'options' => ['Option1', 'Option2'],
            'is_active' => false,
        ]);

        $response = $this->postJson('/api/profile', [
            'answers' => [
                $inactiveQuestion->id => 'Option1',
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['answers']);
    }
}
