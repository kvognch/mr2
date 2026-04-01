<?php

namespace App\Http\Controllers;

use App\Enums\ReviewStatus;
use App\Models\Contractor;
use App\Models\ContractorReview;
use App\Models\ServiceReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PublicReviewController extends Controller
{
    public function storeService(Request $request): RedirectResponse
    {
        $data = $request->validateWithBag('serviceReview', [
            'author_name' => ['required', 'string', 'max:255'],
            'author_role' => ['required', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:3000'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'is_recommended' => ['nullable', 'boolean'],
        ]);

        ServiceReview::query()->create([
            'user_id' => $request->user()->id,
            'author_name' => $data['author_name'],
            'author_role' => $data['author_role'],
            'title' => $data['title'],
            'body' => $data['body'],
            'rating' => (int) $data['rating'],
            'is_recommended' => (bool) ($data['is_recommended'] ?? false),
            'status' => ReviewStatus::Pending->value,
        ]);

        return back()
            ->with('open_service_review_modal', true)
            ->with('service_review_success', 'Отзыв отправлен на модерацию.');
    }

    public function storeContractor(Request $request, string $contractor): RedirectResponse
    {
        $contractorModel = Contractor::query()
            ->where('slug', $contractor)
            ->firstOrFail();

        $data = $request->validateWithBag('contractorReview', [
            'author_name' => ['required', 'string', 'max:255'],
            'author_role' => ['required', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:3000'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'is_recommended' => ['nullable', 'boolean'],
        ]);

        ContractorReview::query()->create([
            'contractor_id' => $contractorModel->id,
            'user_id' => $request->user()->id,
            'author_name' => $data['author_name'],
            'author_role' => $data['author_role'],
            'title' => $data['title'],
            'body' => $data['body'],
            'rating' => (int) $data['rating'],
            'is_recommended' => (bool) ($data['is_recommended'] ?? false),
            'status' => ReviewStatus::Pending->value,
        ]);

        return back()
            ->with('open_contractor_review_modal', true)
            ->with('contractor_review_success', 'Отзыв отправлен на модерацию.');
    }
}
