<?php

use function Xwero\ComposableQueries\PDO\replaceParameters;

test('replace array placeholders', function () {
   $query = ':Array:Test';
   $placeholderReplacements = [':Array:Test_0' => 1, ':Array:Test_1' => 2, ':Array:Test_2' => 3];

   expect(replaceParameters($query, $placeholderReplacements))->toBe(':Array:Test_0,:Array:Test_1,:Array:Test_2');
});

test('replace placeholders with backslash and colon in name', function () {
   $placeholderReplacements = [':Test\Unit:Test' => 1];

   expect(replaceParameters(':Test\Unit:Test', $placeholderReplacements))->toBe(':Test_Unit_Test');
});