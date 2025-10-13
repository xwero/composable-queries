<?php

use Test\Unit\Users;
use Test\Unit\UsersBacked;
use Xwero\ComposableQueries\BaseNamespaceCollection;
use Xwero\ComposableQueries\OverrideCollection;
use function Xwero\ComposableQueries\replacePlaceholders;

test(('no replacement'), function ($query) {
    expect(replacePlaceholders($query))->toBe($query);
})->with(['test', 'User:Users', '~Users:Users']);

test('full namespace', function (string $placeholder, string $replacement) {
   expect(replacePlaceholders($placeholder))->toBe($replacement);
})->with([
    ['~Test\Unit\Users:Users', "users"],
    ['~Test\Unit\UsersBacked:Email', "e-mail"],
]);

test('full namespace with overrides', function (string $placeholder, string $replacement) {
    $overrides = new OverrideCollection(Users::Users, 'users as u', UsersBacked::Email, 'u.email');

    expect(replacePlaceholders($placeholder, $overrides))->toBe($replacement);
})->with([
    ['~Test\Unit\Users:Users', "users as u"],
    ['~Test\Unit\UsersBacked:Email', "u.email"],
]);

test('shortened namespace', function (string $placeholder, string $replacement) {
    $baseNamespaces = new BaseNamespaceCollection('Test\Unit');

    expect(replacePlaceholders($placeholder, baseNamespaces: $baseNamespaces))->toBe($replacement);
})->with([
    ['~Users:Users', "users"],
    ['~UsersBacked:Email', "e-mail"],
]);

test('shortened namespace with overrides', function (string $placeholder, string $replacement) {
    $baseNamespaces = new BaseNamespaceCollection('Test\Unit');
    $overrides = new OverrideCollection(Users::Users, 'users as u', UsersBacked::Email, 'u.email');

    expect(replacePlaceholders($placeholder, $overrides, $baseNamespaces))->toBe($replacement);
})->with([
    ['~Users:Users', "users as u"],
    ['~UsersBacked:Email', "u.email"],
]);
