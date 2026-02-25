<?php

namespace Kennofizet\PackagesCore\Middleware;

use Closure;
use Illuminate\Http\Request;

class ValidatorRequestMiddleware
{
    private $unsafeCssProperties = [
        'contenteditable',
        'style',
        'href',
        'src'
    ];

    public function handle(Request $request, Closure $next)
    {
        $per_page = $request->input('perPage');
        $perPage = $request->input('per_page');

        if ($per_page and $per_page > 50) {
            return response()->json(['error' => 'per page cant > 50'], 400);
        }

        if ($perPage and $perPage > 50) {
            return response()->json(['error' => 'per page cant > 50'], 400);
        }

        $input = $request->except(array_keys($request->allFiles()));
        $input = $this->processBooleanValues($input);
        array_walk_recursive($input, function (&$value) {
            $value = $this->sanitizeValue($value);
        });

        if ($request->isMethod('GET')) {
            $queryParams = $request->query();
            foreach ($queryParams as $key => $value) {
                if ($value === 'undefined' || $value === 'null' || $value === 'NULL' || $value === null) {
                    $queryParams[$key] = '';
                }
                if ($value === 'true') {
                    $queryParams[$key] = true;
                }
                if ($value === 'false') {
                    $queryParams[$key] = false;
                }
            }
            $request->query->replace($queryParams);
            $input = $request->all();
        }

        $request->replace($input);
        return $next($request);
    }

    private function processBooleanValues($input)
    {
        if (is_array($input)) {
            foreach ($input as $key => $value) {
                if (is_array($value)) {
                    $input[$key] = $this->processBooleanValues($value);
                } else {
                    if ($value === 'true') {
                        $input[$key] = true;
                    } elseif ($value === 'false') {
                        $input[$key] = false;
                    } elseif ($value === 'undefined' || $value === 'null' || $value === 'NULL' || $value === null) {
                        $input[$key] = null;
                    }
                }
            }
        }
        return $input;
    }

    private function sanitizeValue($value)
    {
        if (is_string($value)) {
            $allowable_tags = '<span><div><i><b><br><div><h1><h2><h3><h4><h5><h6><hr><li><p><pre><span><strong><table><tbody><td><tfoot><th><thead><tr><ul>';
            $value = strip_tags($value, $allowable_tags);
            $value = str_replace($this->unsafeCssProperties, '*****', $value);
        }
        return $value;
    }
}
