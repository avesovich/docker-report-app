<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->artisan('db:seed --class=RoleSeeder');
    $this->userAdmin = User::factory()->create()->assignRole('administrator');
    $this->userEditor = User::factory()->create()->assignRole('editor');
    $this->userExecutive = User::factory()->create()->assignRole('executive');
});

it('allows administrator to view review, update, revision, evaluated, and approved', function () {
    actingAs($this->userAdmin)->get(route('status.review'))->assertOk();
    actingAs($this->userAdmin)->get(route('status.updated'))->assertOk();
    actingAs($this->userAdmin)->get(route('status.revision'))->assertOk();
    actingAs($this->userAdmin)->get(route('status.evaluated'))->assertOk();
    actingAs($this->userAdmin)->get(route('status.approved'))->assertOk();
});

it('allows editor to view review, update, revision, evaluated, and approved', function () {
    actingAs($this->userEditor)->get(route('status.review'))->assertOk();
    actingAs($this->userEditor)->get(route('status.updated'))->assertOk();
    actingAs($this->userEditor)->get(route('status.revision'))->assertOk();
    actingAs($this->userEditor)->get(route('status.evaluated'))->assertOk();
    actingAs($this->userEditor)->get(route('status.approved'))->assertOk();
});


it('blocks executive role from accessing review, updated, and revision pages', function () {
    actingAs($this->userExecutive)->get(route('status.review'))->assertForbidden();
    actingAs($this->userExecutive)->get(route('status.updated'))->assertForbidden();
    actingAs($this->userExecutive)->get(route('status.revision'))->assertForbidden();
});

it('allows executive to view  evaluated, and approved', function () {
    actingAs($this->userExecutive)->get(route('status.evaluated'))->assertOk();
    actingAs($this->userExecutive)->get(route('status.approved'))->assertOk();
});
