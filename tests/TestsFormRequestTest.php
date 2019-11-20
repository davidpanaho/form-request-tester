<?php

namespace Talkative\FormRequestTester\Tests;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Talkative\FormRequestTester\TestsFormRequests;
use Talkative\FormRequestTester\Tests\Stubs\Models\Post;
use Talkative\FormRequestTester\Tests\Stubs\Models\User;
use Talkative\FormRequestTester\Tests\Stubs\FormRequests\UpdatePost;

class TestsFormRequestTest extends TestCase
{
    use
        DatabaseMigrations,
        TestsFormRequests;

    public function setUp()
    {
        parent::setUp();

        $this->user = factory(User::class)->create();
        $this->post = factory(Post::class)->create([
            'user_id' => $this->user->id
        ]);

        $this->actingAs($this->user);
    }

    /** @test */
    public function validation_will_pass()
    {
        $this->formRequest(
            UpdatePost::class,
            [
                'content' => 'This is content',
                'user_id' => $this->user->id
            ],
            ['method' => 'put', 'route' => "posts/{$this->post->id}"]
        )->assertValidationPassed();
    }

    /** @test */
    public function validation_will_fail()
    {
        $this->formRequest(
            UpdatePost::class,
            [],
            ['method' => 'put', 'route' => "posts/{$this->post->id}"]
        )
            ->assertValidationFailed()
            ->assertValidationErrors(['user_id', 'content'])
            ->assertValidationMessages(['Content Field is required', 'User Field is required']);
    }

    /** @test */
    public function validation_will_fail_because_user_id_is_not_valid()
    {
        $this->formRequest(
            UpdatePost::class,
            [
                'content' => 'This is content',
                'user_id' => 2000
            ],
            ['method' => 'put', 'route' => "posts/{$this->post->id}"]
        )
            ->assertValidationFailed()
            ->assertValidationErrors(['user_id'])
            ->assertValidationErrorsMissing(['content'])
            ->assertValidationMessages(['User is not valid']);
    }

    /** @test */
    public function form_request_will_authorize_request()
    {
        $this->formRequest(
            UpdatePost::class,
            [],
            ['method' => 'put', 'route' => "posts/{$this->post->id}"]
        )->assertAuthorized();
    }

    /** @test */
    public function form_request_will_not_authorize_request()
    {
        $user = factory(User::class)->create();
        $this->actingAs($user);
        $this->formRequest(
            UpdatePost::class,
            [],
            ['method' => 'put', 'route' => "posts/{$this->post->id}"]
        )->assertNotAuthorized();
    }
}