<?php

use App\Models\GameSession;
use App\Models\User;
use App\Policies\GameSessionPolicy;

test('host passes all policy checks', function () {
    $host = User::factory()->create();
    $session = GameSession::factory()->create(['host_id' => $host->id]);
    $policy = new GameSessionPolicy;

    expect($policy->view($host, $session))->toBeTrue()
        ->and($policy->start($host, $session))->toBeTrue()
        ->and($policy->next($host, $session))->toBeTrue()
        ->and($policy->reveal($host, $session))->toBeTrue()
        ->and($policy->end($host, $session))->toBeTrue()
        ->and($policy->results($host, $session))->toBeTrue()
        ->and($policy->export($host, $session))->toBeTrue();
});

test('non-host fails all policy checks', function () {
    $host = User::factory()->create();
    $other = User::factory()->create();
    $session = GameSession::factory()->create(['host_id' => $host->id]);
    $policy = new GameSessionPolicy;

    expect($policy->view($other, $session))->toBeFalse()
        ->and($policy->start($other, $session))->toBeFalse()
        ->and($policy->next($other, $session))->toBeFalse()
        ->and($policy->reveal($other, $session))->toBeFalse()
        ->and($policy->end($other, $session))->toBeFalse()
        ->and($policy->results($other, $session))->toBeFalse()
        ->and($policy->export($other, $session))->toBeFalse();
});
