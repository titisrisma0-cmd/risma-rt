<?php

namespace App\Http\Controllers;

use App\Models\AssistantFaq;
use App\Models\AssistantUnansweredQuestion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssistantFaqController extends Controller
{
    /**
     * Pastikan hanya Super Admin atau Pengurus RW yang dapat mengelola FAQ Asisten
     */
    protected function authorizeManager(): void
    {
        $user = auth()->user();
        if (! $user || (! $user->isSuperAdmin() && ! $user->isAdminRw())) {
            abort(403, 'Akses terbatas untuk Super Admin dan Pengurus RW.');
        }
    }

    /**
     * Tampilkan halaman kelola FAQ Asisten
     * GET /assistant/faqs
     */
    public function index(): View
    {
        $this->authorizeManager();

        $faqs = AssistantFaq::orderBy('urutan', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $unansweredQuestions = AssistantUnansweredQuestion::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('assistant.faqs.index', compact('faqs', 'unansweredQuestions'));
    }

    /**
     * Simpan FAQ baru
     * POST /assistant/faqs
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorizeManager();

        $validated = $request->validate([
            'pertanyaan' => 'required|string|max:255',
            'kata_kunci' => 'nullable|string|max:1000',
            'jawaban' => 'required|string',
            'kategori' => 'nullable|string|max:100',
            'urutan' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'perlu_tinjauan' => 'nullable|boolean',
            'catatan_tinjauan' => 'nullable|string|max:255',
            'field_pengurus' => 'nullable',
        ], [
            'pertanyaan.required' => 'Pertanyaan wajib diisi.',
            'jawaban.required' => 'Jawaban wajib diisi.',
        ]);

        $validated['created_by'] = auth()->id();
        $validated['kategori'] = $validated['kategori'] ?: 'Umum';
        $validated['urutan'] = $validated['urutan'] ?? 0;
        $validated['is_active'] = $request->boolean('is_active');
        $validated['perlu_tinjauan'] = $request->boolean('perlu_tinjauan');
        $validated['catatan_tinjauan'] = $validated['catatan_tinjauan'] ?? null;
        if ($request->filled('field_pengurus')) {
            $rawField = $request->input('field_pengurus');
            $validated['field_pengurus'] = is_array($rawField) ? $rawField : (json_decode((string)$rawField, true) ?? $rawField);
        }
        if ($request->filled('field_pengurus')) {
            $rawField = $request->input('field_pengurus');
            $validated['field_pengurus'] = is_array($rawField) ? $rawField : (json_decode((string)$rawField, true) ?? $rawField);
        }

        AssistantFaq::create($validated);

        // Jika FAQ dibuat dari pertanyaan belum terjawab
        if ($request->filled('unanswered_id')) {
            AssistantUnansweredQuestion::where('id', $request->input('unanswered_id'))
                ->update(['status' => 'dijawab']);
        }

        return redirect()->route('assistant.faqs.index')
            ->with('success', 'FAQ Asisten berhasil ditambahkan.');
    }

    /**
     * Perbarui FAQ yang sudah ada
     * PUT /assistant/faqs/{faq}
     */
    public function update(Request $request, AssistantFaq $faq): RedirectResponse
    {
        $this->authorizeManager();

        $validated = $request->validate([
            'pertanyaan' => 'required|string|max:255',
            'kata_kunci' => 'nullable|string|max:1000',
            'jawaban' => 'required|string',
            'kategori' => 'nullable|string|max:100',
            'urutan' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'perlu_tinjauan' => 'nullable|boolean',
            'catatan_tinjauan' => 'nullable|string|max:255',
            'field_pengurus' => 'nullable',
        ], [
            'pertanyaan.required' => 'Pertanyaan wajib diisi.',
            'jawaban.required' => 'Jawaban wajib diisi.',
        ]);

        $validated['kategori'] = $validated['kategori'] ?: 'Umum';
        $validated['urutan'] = $validated['urutan'] ?? 0;
        $validated['is_active'] = $request->boolean('is_active');
        $validated['perlu_tinjauan'] = $request->boolean('perlu_tinjauan');
        $validated['catatan_tinjauan'] = $validated['catatan_tinjauan'] ?? null;
        if ($request->filled('field_pengurus')) {
            $rawField = $request->input('field_pengurus');
            $validated['field_pengurus'] = is_array($rawField) ? $rawField : (json_decode((string)$rawField, true) ?? $rawField);
        }

        $faq->update($validated);

        return redirect()->route('assistant.faqs.index')
            ->with('success', 'FAQ Asisten berhasil diperbarui.');
    }

    /**
     * Aktifkan / Nonaktifkan status FAQ
     * PATCH /assistant/faqs/{faq}/toggle
     */
    public function toggle(AssistantFaq $faq): RedirectResponse
    {
        $this->authorizeManager();

        $faq->update(['is_active' => ! $faq->is_active]);

        $status = $faq->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->route('assistant.faqs.index')
            ->with('success', "FAQ berhasil {$status}.");
    }

    /**
     * Hapus FAQ
     * DELETE /assistant/faqs/{faq}
     */
    public function destroy(AssistantFaq $faq): RedirectResponse
    {
        $this->authorizeManager();

        $faq->delete();

        return redirect()->route('assistant.faqs.index')
            ->with('success', 'FAQ Asisten berhasil dihapus.');
    }

    /**
     * Hapus pertanyaan tidak terjawab
     * DELETE /assistant/unanswered/{unanswered}
     */
    public function destroyUnanswered(AssistantUnansweredQuestion $unanswered): RedirectResponse
    {
        $this->authorizeManager();

        $unanswered->delete();

        return redirect()->route('assistant.faqs.index')
            ->with('success', 'Catatan pertanyaan tidak terjawab berhasil dihapus.');
    }
}
