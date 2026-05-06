<?php

namespace App\Http\Controllers;

use App\Services\SemanticSearch\SemanticSearchService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function __construct(
        private readonly SemanticSearchService $semanticSearch,
    ) {}

    public function index(Request $request): View
    {
        $q = (string) $request->query('q', '');
        $page = max(1, (int) $request->query('page', 1));

        $data = $this->semanticSearch->search($q, $page);

        $showScores = $request->boolean('debug_scores') && (bool) config('app.debug');
        if ($showScores) {
            $data['paginator']->appends(['debug_scores' => 1]);
        }

        return view('search.index', [
            'query' => $data['query'],
            'mode' => $data['mode'],
            'fallbackReason' => $data['fallback_reason'],
            'paginator' => $data['paginator'],
            'showScores' => $showScores,
        ]);
    }
}
