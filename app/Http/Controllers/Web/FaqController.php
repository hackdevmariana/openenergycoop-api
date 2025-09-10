<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    /**
     * Mostrar una FAQ individual por slug
     */
    public function show(Request $request, string $slug)
    {
        $faq = Faq::query()
            ->with(['topic', 'organization'])
            ->where('slug', $slug)
            ->where('is_draft', false)
            ->firstOrFail();

        // Incrementar contador de vistas
        $faq->increment('view_count');

        return view('faqs.show', compact('faq'));
    }

    /**
     * Mostrar todas las FAQs
     */
    public function index(Request $request)
    {
        $query = Faq::query()
            ->with(['topic', 'organization'])
            ->where('is_draft', false)
            ->orderBy('position');

        if ($request->filled('topic')) {
            $query->whereHas('topic', function ($q) use ($request) {
                $q->where('slug', $request->topic);
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('question', 'like', "%{$search}%")
                  ->orWhere('answer', 'like', "%{$search}%");
            });
        }

        $faqs = $query->paginate(20);

        return view('faqs.index', compact('faqs'));
    }
}
