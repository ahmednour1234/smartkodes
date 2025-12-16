#!/usr/bin/env php
<?php

/**
 * Test Script for Symfony Expression Language Integration
 *
 * This sc// Test 4: Minimum Value
echo "Test 4: Minimum Value\n";
echo "Formula: min(value1, 100)\n";
try {
    $result = $expressionLanguage->evaluate('min(value1, 100)', [
        'value1' => 150
    ]);
    echo "Result: $result\n";
    echo "Expected: 100\n";
    echo $result == 100 ? "✅ PASS\n\n" : "❌ FAIL\n\n";
} catch (Exception $e) {
    echo "❌ FAIL: " . $e->getMessage() . "\n\n";
}ulated field formula evaluation
 * // Test 7: Power/Exponent
echo "Test 7: Power/Exponent\n";
echo "Formula: pow(base, 2)\n";
try {
    $result = $expressionLanguage->evaluate('pow(base, 2)', [
        'base' => 5
    ]);
    echo "Result: $result\n";
    echo "Expected: 25\n";
    echo $result == 25 ? "✅ PASS\n\n" : "❌ FAIL\n\n";
} catch (Exception $e) {
    echo "❌ FAIL: " . $e->getMessage() . "\n\n";
}nctionality after upgrading from eval().
 */

require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

echo "=== Testing Symfony Expression Language for Calculated Fields ===\n\n";

$expressionLanguage = new ExpressionLanguage();

// Register all math functions FIRST before any evaluation
$expressionLanguage->register('round', function ($value, $precision = 0) {
    return sprintf('round(%s, %s)', $value, $precision);
}, function ($arguments, $value, $precision = 0) {
    return round($value, $precision);
});

$expressionLanguage->register('max', function (...$args) {
    return sprintf('max(%s)', implode(', ', $args));
}, function ($arguments, ...$values) {
    return max(...$values);
});

$expressionLanguage->register('min', function (...$args) {
    return sprintf('min(%s)', implode(', ', $args));
}, function ($arguments, ...$values) {
    return min(...$values);
});

$expressionLanguage->register('sqrt', function ($value) {
    return sprintf('sqrt(%s)', $value);
}, function ($arguments, $value) {
    return sqrt($value);
});

$expressionLanguage->register('pow', function ($base, $exp) {
    return sprintf('pow(%s, %s)', $base, $exp);
}, function ($arguments, $base, $exp) {
    return pow($base, $exp);
});

$expressionLanguage->register('abs', function ($value) {
    return sprintf('abs(%s)', $value);
}, function ($arguments, $value) {
    return abs($value);
});

// Test 1: Simple Arithmetic
echo "Test 1: Simple Arithmetic\n";
echo "Formula: quantity * price\n";
$result = $expressionLanguage->evaluate('quantity * price', [
    'quantity' => 5,
    'price' => 10
]);
echo "Result: $result\n";
echo "Expected: 50\n";
echo $result == 50 ? "✅ PASS\n\n" : "❌ FAIL\n\n";

// Test 2: Division with Rounding
echo "Test 2: Division with Rounding\n";
echo "Formula: round(total / count, 2)\n";
try {
    $result = $expressionLanguage->evaluate('round(total / count, 2)', [
        'total' => 100,
        'count' => 3
    ]);
    echo "Result: $result\n";
    echo "Expected: 33.33\n";
    echo $result == 33.33 ? "✅ PASS\n\n" : "❌ FAIL\n\n";
} catch (Exception $e) {
    echo "❌ FAIL: " . $e->getMessage() . "\n\n";
}

// Test 3: Max Function
echo "Test 3: Maximum Value\n";
echo "Formula: max(value1, value2, value3)\n";
try {
    $expressionLanguage->register('max', function (...$args) {
        return sprintf('max(%s)', implode(', ', $args));
    }, function ($arguments, ...$values) {
        return max(...$values);
    });

    $result = $expressionLanguage->evaluate('max(value1, value2, value3)', [
        'value1' => 10,
        'value2' => 25,
        'value3' => 15
    ]);
    echo "Result: $result\n";
    echo "Expected: 25\n";
    echo $result == 25 ? "✅ PASS\n\n" : "❌ FAIL\n\n";
} catch (Exception $e) {
    echo "❌ FAIL: " . $e->getMessage() . "\n\n";
}

// Test 4: Min Function
echo "Test 4: Minimum Value\n";
echo "Formula: min(value1, 100)\n";
try {
    $expressionLanguage->register('min', function (...$args) {
        return sprintf('min(%s)', implode(', ', $args));
    }, function ($arguments, ...$values) {
        return min(...$values);
    });

    $result = $expressionLanguage->evaluate('min(value1, 100)', [
        'value1' => 150
    ]);
    echo "Result: $result\n";
    echo "Expected: 100\n";
    echo $result == 100 ? "✅ PASS\n\n" : "❌ FAIL\n\n";
} catch (Exception $e) {
    echo "❌ FAIL: " . $e->getMessage() . "\n\n";
}

// Test 5: Tax Calculation
echo "Test 5: Tax Calculation\n";
echo "Formula: round(subtotal * 1.15, 2)\n";
try {
    $result = $expressionLanguage->evaluate('round(subtotal * 1.15, 2)', [
        'subtotal' => 100
    ]);
    echo "Result: $result\n";
    echo "Expected: 115\n";
    echo $result == 115 ? "✅ PASS\n\n" : "❌ FAIL\n\n";
} catch (Exception $e) {
    echo "❌ FAIL: " . $e->getMessage() . "\n\n";
}

// Test 6: Square Root
echo "Test 6: Square Root\n";
echo "Formula: sqrt(value)\n";
try {
    $result = $expressionLanguage->evaluate('sqrt(value)', [
        'value' => 16
    ]);
    echo "Result: $result\n";
    echo "Expected: 4\n";
    echo $result == 4 ? "✅ PASS\n\n" : "❌ FAIL\n\n";
} catch (Exception $e) {
    echo "❌ FAIL: " . $e->getMessage() . "\n\n";
}

// Test 7: Power
echo "Test 7: Power/Exponent\n";
echo "Formula: pow(base, 2)\n";
try {
    $expressionLanguage->register('pow', function ($base, $exp) {
        return sprintf('pow(%s, %s)', $base, $exp);
    }, function ($arguments, $base, $exp) {
        return pow($base, $exp);
    });

    $result = $expressionLanguage->evaluate('pow(base, 2)', [
        'base' => 5
    ]);
    echo "Result: $result\n";
    echo "Expected: 25\n";
    echo $result == 25 ? "✅ PASS\n\n" : "❌ FAIL\n\n";
} catch (Exception $e) {
    echo "❌ FAIL: " . $e->getMessage() . "\n\n";
}

// Test 8: Complex Formula
echo "Test 8: Complex Formula (Discount Calculation)\n";
echo "Formula: round((price * quantity) * (1 - (discount / 100)), 2)\n";
try {
    $result = $expressionLanguage->evaluate('round((price * quantity) * (1 - (discount / 100)), 2)', [
        'price' => 50,
        'quantity' => 3,
        'discount' => 10
    ]);
    echo "Result: $result\n";
    echo "Expected: 135\n";
    echo $result == 135 ? "✅ PASS\n\n" : "❌ FAIL\n\n";
} catch (Exception $e) {
    echo "❌ FAIL: " . $e->getMessage() . "\n\n";
}

// Test 9: Security - Prevent Code Execution
echo "Test 9: Security Test (Should Fail Safely)\n";
echo "Formula: eval('return 1 + 1;')\n";
try {
    $result = $expressionLanguage->evaluate("eval('return 1 + 1;')", []);
    echo "❌ FAIL: Code execution was allowed!\n\n";
} catch (Exception $e) {
    echo "✅ PASS: Code execution prevented\n";
    echo "Error: " . $e->getMessage() . "\n\n";
}

// Test 10: Absolute Value
echo "Test 10: Absolute Value\n";
echo "Formula: abs(value1 - value2)\n";
try {
    $result = $expressionLanguage->evaluate('abs(value1 - value2)', [
        'value1' => 10,
        'value2' => 25
    ]);
    echo "Result: $result\n";
    echo "Expected: 15\n";
    echo $result == 15 ? "✅ PASS\n\n" : "❌ FAIL\n\n";
} catch (Exception $e) {
    echo "❌ FAIL: " . $e->getMessage() . "\n\n";
}

echo "=== All Tests Complete ===\n";
echo "\nNote: The security test (Test 9) should FAIL with an error.\n";
echo "This is the expected behavior - it proves eval() is NOT being used.\n";
