<?php

use App\Models\User;

function adminUser(): User
{
    return User::factory()->create(['is_admin' => true]);
}

test('admin can list users', function () {
    $admin = adminUser();

    $this->actingAs($admin)
        ->get(route('admin.users.index'))
        ->assertSuccessful();
});

test('non-admin cannot list users', function () {
    $user = User::factory()->create(['is_admin' => false]);

    $this->actingAs($user)
        ->get(route('admin.users.index'))
        ->assertForbidden();
});

test('guest is redirected from user list', function () {
    $this->get(route('admin.users.index'))
        ->assertRedirect(route('login'));
});

test('admin can view user detail', function () {
    $admin = adminUser();
    $target = User::factory()->create();

    $this->actingAs($admin)
        ->get(route('admin.users.show', $target))
        ->assertSuccessful();
});

test('admin can grant admin role', function () {
    $admin = adminUser();
    $target = User::factory()->create(['is_admin' => false]);

    $this->actingAs($admin)
        ->put(route('admin.users.update', $target), ['is_admin' => true]);

    expect($target->fresh()->is_admin)->toBeTrue();
});

test('admin can revoke admin role from other user', function () {
    $admin = adminUser();
    $target = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)
        ->put(route('admin.users.update', $target), ['is_admin' => false]);

    expect($target->fresh()->is_admin)->toBeFalse();
});

test('admin cannot revoke their own admin status', function () {
    $admin = adminUser();

    $this->actingAs($admin)
        ->put(route('admin.users.update', $admin), ['is_admin' => false])
        ->assertSessionHasErrors('is_admin');

    expect($admin->fresh()->is_admin)->toBeTrue();
});

test('admin can delete another user', function () {
    $admin = adminUser();
    $target = User::factory()->create();

    $this->actingAs($admin)
        ->delete(route('admin.users.destroy', $target))
        ->assertRedirect(route('admin.users.index'));

    $this->assertModelMissing($target);
});

test('admin cannot delete themselves', function () {
    $admin = adminUser();

    $this->actingAs($admin)
        ->delete(route('admin.users.destroy', $admin))
        ->assertSessionHasErrors('user');

    $this->assertModelExists($admin);
});

test('admin user search returns matching results', function () {
    $admin = adminUser();
    User::factory()->create(['name' => 'Budi Santoso']);
    User::factory()->create(['name' => 'Siti Rahayu']);

    $response = $this->actingAs($admin)
        ->get(route('admin.users.index', ['search' => 'Budi']))
        ->assertSuccessful();

    $users = $response->original->getData()['page']['props']['users']['data'];
    expect(count($users))->toBe(1)
        ->and($users[0]['name'])->toBe('Budi Santoso');
});
