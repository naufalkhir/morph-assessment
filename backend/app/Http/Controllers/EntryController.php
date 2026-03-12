<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEntryRequest;
use App\Http\Requests\UpdateEntryRequest;
use App\Models\Entry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EntryController extends Controller
{
    /**
     * GET /api/entries
     * List all entries — paginated, filterable, sortable.
     */
    public function index(Request $request): JsonResponse
    {
        $allowed = ['description', 'amount', 'currency', 'transaction_date', 'created_at'];
        $sort    = in_array($request->sort, $allowed) ? $request->sort : 'created_at';
        $order   = strtolower($request->order ?? 'desc') === 'asc' ? 'asc' : 'desc';
        $perPage = (int) ($request->per_page ?? 10);
        $perPage = min(max($perPage, 1), 100); // clamp 1–100

        $entries = Entry::query()
            ->when($request->search, fn ($q, $s) => $q->where('description', 'like', "%{$s}%"))
            ->when($request->date_from, fn ($q, $d) => $q->whereDate('transaction_date', '>=', $d))
            ->when($request->date_to,   fn ($q, $d) => $q->whereDate('transaction_date', '<=', $d))
            ->when($request->currency,  fn ($q, $c) => $q->where('currency', strtoupper($c)))
            ->orderBy($sort, $order)
            ->paginate($perPage);

        return response()->json($entries);
    }

    /**
     * POST /api/entries
     * Create a new entry.
     */
    public function store(StoreEntryRequest $request): JsonResponse
    {
        $entry = Entry::create($request->validated());

        return response()->json([
            'message' => 'Entry created successfully.',
            'data'    => $entry,
        ], 201);
    }

    /**
     * GET /api/entries/{id}
     * Return a single entry.
     */
    public function show(Entry $entry): JsonResponse
    {
        return response()->json(['data' => $entry]);
    }

    /**
     * PUT /api/entries/{id}
     * Update an existing entry.
     */
    public function update(UpdateEntryRequest $request, Entry $entry): JsonResponse
    {
        $entry->update($request->validated());

        return response()->json([
            'message' => 'Entry updated successfully.',
            'data'    => $entry->fresh(),
        ]);
    }

    /**
     * DELETE /api/entries/{id}
     * Delete an entry.
     */
    public function destroy(Entry $entry): JsonResponse
    {
        $entry->delete();

        return response()->json(null, 204);
    }
}
